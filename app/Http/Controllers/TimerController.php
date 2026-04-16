<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcUnit;
use Illuminate\Support\Facades\Cache;

class TimerController extends Controller
{
    public function schedule(Request $request, $id)
    {
        $request->validate([
            'timer_on'  => 'nullable|date_format:H:i',
            'timer_off' => 'nullable|date_format:H:i',
        ]);

        $ac = AcUnit::findOrFail($id);

        // VALIDASI LOGIKA
        if ($request->timer_on && $request->timer_off) {
            if ($request->timer_on === $request->timer_off) {
                return back()->with('error', 'Timer ON dan OFF tidak boleh sama');
            }
        }

        $newTimerOn  = $request->timer_on ?: null;
        $newTimerOff = $request->timer_off ?: null;

        $key = "timer_version_{$ac->id}";

        // UPDATE HANYA JIKA BERUBAH
        if (
            $ac->timer_on !== $newTimerOn ||
            $ac->timer_off !== $newTimerOff
        ) {

            if (!Cache::has($key)) {
                Cache::put($key, 1);
            } else {
                Cache::increment($key);
            }

            $ac->update([
                'timer_on'  => $newTimerOn,
                'timer_off' => $newTimerOff,
            ]);
        }

        return back()->with('success', 'Timer berhasil disimpan');
    }
}
