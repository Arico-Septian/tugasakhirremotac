<?php

namespace App\Http\Controllers;

use App\Models\AcUnit;
use App\Models\Room;
use App\Models\UserLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class EnergyController extends Controller
{
    /**
     * GET /energy — Energy analytics page
     */
    public function index(Request $request)
    {
        $period = $request->input('period', 'month'); // day, week, month
        [$startDate, $endDate, $groupFmt, $periodLabel] = $this->resolvePeriod($period);

        $powerKw = (float) config('smartac.energy.power_kw');
        $tariff = (float) config('smartac.energy.tariff_per_kwh');
        $defaultHours = (float) config('smartac.energy.default_session_hours');
        $currency = config('smartac.energy.currency_symbol');

        // Fetch all power events in window
        $logs = UserLog::whereIn('activity', ['on', 'off', 'bulk_on', 'bulk_off'])
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->orderBy('created_at')
            ->get();

        // Estimate runtime hours per AC by pairing ON/OFF events
        // Fallback: if ON has no matching OFF in window, assume default session length
        $runtimePerAc = $this->estimateRuntimeByAc($logs, $startDate, $endDate, $defaultHours);

        // Aggregate per AC unit
        $acUnits = AcUnit::with('room')->get();

        $perAcStats = $acUnits->map(function ($ac) use ($runtimePerAc, $powerKw, $tariff) {
            $key = strtolower(trim($ac->room->name ?? '')) . '|' . $ac->ac_number;
            $hours = $runtimePerAc[$key] ?? 0;
            $kwh = $hours * $powerKw;
            $cost = $kwh * $tariff;

            return [
                'ac_id' => $ac->id,
                'room' => $ac->room->name ?? '-',
                'ac_number' => $ac->ac_number,
                'name' => $ac->name,
                'hours' => round($hours, 1),
                'kwh' => round($kwh, 2),
                'cost' => round($cost),
            ];
        })->sortByDesc('kwh')->values();

        // Aggregate per room
        $perRoomStats = $perAcStats->groupBy('room')->map(function ($group, $room) {
            return [
                'room' => $room,
                'ac_count' => $group->count(),
                'hours' => round($group->sum('hours'), 1),
                'kwh' => round($group->sum('kwh'), 2),
                'cost' => round($group->sum('cost')),
            ];
        })->sortByDesc('kwh')->values();

        // Totals
        $totals = [
            'hours' => round($perAcStats->sum('hours'), 1),
            'kwh' => round($perAcStats->sum('kwh'), 2),
            'cost' => round($perAcStats->sum('cost')),
            'events' => $logs->whereIn('activity', ['on', 'bulk_on'])->count(),
        ];

        // Time series for chart (group by day for week/month, hour for day)
        $timeSeries = $this->buildTimeSeries($logs, $startDate, $endDate, $period, $powerKw, $defaultHours);

        return view('energy.index', [
            'period' => $period,
            'periodLabel' => $periodLabel,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'totals' => $totals,
            'perAcStats' => $perAcStats,
            'perRoomStats' => $perRoomStats,
            'timeSeries' => $timeSeries,
            'powerKw' => $powerKw,
            'tariff' => $tariff,
            'currency' => $currency,
        ]);
    }

    private function resolvePeriod(string $period): array
    {
        $now = Carbon::now();

        return match ($period) {
            'day' => [$now->copy()->startOfDay(), $now->copy()->endOfDay(), 'H:00', 'Hari ini'],
            'week' => [$now->copy()->startOfWeek(), $now->copy()->endOfWeek(), 'D M', '7 hari terakhir'],
            default => [$now->copy()->startOfMonth(), $now->copy()->endOfMonth(), 'D M', $now->format('F Y')],
        };
    }

    /**
     * Pair ON/OFF events per AC to compute runtime hours.
     * For unpaired ON (no OFF before window end), assume default session length.
     */
    private function estimateRuntimeByAc(Collection $logs, Carbon $start, Carbon $end, float $defaultHours): array
    {
        $byAc = [];

        foreach ($logs as $log) {
            $room = strtolower(trim((string) $log->room));
            $ac = (string) $log->ac;

            if ($room === '' || $ac === '') {
                continue;
            }

            // Extract numeric AC id from "AC 1 - Unit A" / "1" / "AC1"
            preg_match('/(\d+)/', $ac, $m);
            $acNum = $m[1] ?? $ac;

            $key = $room . '|' . $acNum;
            $byAc[$key][] = $log;
        }

        $result = [];

        foreach ($byAc as $key => $events) {
            $hours = 0.0;
            $openOn = null;

            foreach ($events as $ev) {
                $isOn = in_array($ev->activity, ['on', 'bulk_on'], true);
                $isOff = in_array($ev->activity, ['off', 'bulk_off'], true);

                if ($isOn) {
                    if ($openOn === null) {
                        $openOn = Carbon::parse($ev->created_at);
                    }
                } elseif ($isOff && $openOn !== null) {
                    $offAt = Carbon::parse($ev->created_at);
                    $hours += max(0, $openOn->diffInMinutes($offAt) / 60);
                    $openOn = null;
                }
            }

            // Unclosed ON at end of window → assume default session
            if ($openOn !== null) {
                $hours += min($defaultHours, max(0, $openOn->diffInMinutes($end) / 60));
            }

            // If no events at all but AC was already running before window, no estimate
            $result[$key] = $hours;
        }

        return $result;
    }

    /**
     * Build time series for chart, grouped per day (or per hour for "day" period)
     */
    private function buildTimeSeries(Collection $logs, Carbon $start, Carbon $end, string $period, float $powerKw, float $defaultHours): array
    {
        $buckets = [];
        $cursor = $start->copy();
        $isHourly = $period === 'day';

        while ($cursor->lte($end)) {
            $label = $isHourly ? $cursor->format('H:00') : $cursor->format('d M');
            $buckets[$label] = 0.0;
            $cursor->add($isHourly ? '1 hour' : '1 day');
        }

        // Distribute estimated runtime per ON event into buckets
        $openOnByAc = [];

        foreach ($logs as $log) {
            $isOn = in_array($log->activity, ['on', 'bulk_on'], true);
            $isOff = in_array($log->activity, ['off', 'bulk_off'], true);
            $key = strtolower((string) $log->room) . '|' . (string) $log->ac;

            if ($isOn && !isset($openOnByAc[$key])) {
                $openOnByAc[$key] = Carbon::parse($log->created_at);
            } elseif ($isOff && isset($openOnByAc[$key])) {
                $onAt = $openOnByAc[$key];
                $offAt = Carbon::parse($log->created_at);
                $this->distributeAcrossBuckets($buckets, $onAt, $offAt, $isHourly, $powerKw);
                unset($openOnByAc[$key]);
            }
        }

        // Unclosed → distribute up to default session hours from ON time
        foreach ($openOnByAc as $onAt) {
            $offAt = $onAt->copy()->addHours($defaultHours)->min($end);
            $this->distributeAcrossBuckets($buckets, $onAt, $offAt, $isHourly, $powerKw);
        }

        return [
            'labels' => array_keys($buckets),
            'kwh' => array_map(fn($v) => round($v, 2), array_values($buckets)),
        ];
    }

    private function distributeAcrossBuckets(array &$buckets, Carbon $start, Carbon $end, bool $isHourly, float $powerKw): void
    {
        $cur = $start->copy();

        while ($cur->lt($end)) {
            $bucketEnd = $isHourly
                ? $cur->copy()->startOfHour()->addHour()
                : $cur->copy()->startOfDay()->addDay();

            $segmentEnd = $bucketEnd->lt($end) ? $bucketEnd : $end;
            $hours = max(0, $cur->diffInMinutes($segmentEnd) / 60);
            $label = $isHourly ? $cur->format('H:00') : $cur->format('d M');

            if (isset($buckets[$label])) {
                $buckets[$label] += $hours * $powerKw;
            }

            $cur = $segmentEnd;
        }
    }
}
