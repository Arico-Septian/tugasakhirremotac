<?php

/*
|--------------------------------------------------------------------------
| SmartAC Application Configuration
|--------------------------------------------------------------------------
*/

return [
    /*
     * Temperature thresholds for auto-alerts
     */
    'temp_alerts' => [
        'too_hot' => env('SMARTAC_THRESHOLD_HOT', 30),
        'too_cold' => env('SMARTAC_THRESHOLD_COLD', 18),
    ],
];
