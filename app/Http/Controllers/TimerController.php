<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AcUnit;

class TimerController extends Controller
{
    public function schedule(Request $request, $id)
    {
        $request->validate([
            'timer_on' => 'nullable|date_format:H:i',
            'timer_off' => 'nullable|date_format:H:i',
        ]);

        $ac = AcUnit::findOrFail($id);

        $ac->update([
            'timer_on' => $request->timer_on ?: null,
            'timer_off' => $request->timer_off ?: null
        ]);

        return back()->with('success', 'Timer berhasil disimpan');
    }
}
