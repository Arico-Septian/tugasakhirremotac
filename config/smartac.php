<?php

/*
|--------------------------------------------------------------------------
| SmartAC Application Configuration
|--------------------------------------------------------------------------
*/

return [
    /*
     * Energy estimation parameters
     * - power_kw: Average kW draw per AC unit when ON (default ~0.9 kW for 1 PK AC)
     * - tariff_per_kwh: Electricity tariff in IDR per kWh (PLN R-1/TR ~ Rp 1.444,7)
     * - default_session_hours: Estimated avg ON duration per power-on event
     */
    'energy' => [
        'power_kw' => env('SMARTAC_POWER_KW', 0.9),
        'tariff_per_kwh' => env('SMARTAC_TARIFF_PER_KWH', 1444.7),
        'default_session_hours' => env('SMARTAC_DEFAULT_SESSION_HOURS', 4),
        'currency_symbol' => 'Rp',
    ],

    /*
     * Temperature thresholds for auto-alerts
     */
    'temp_alerts' => [
        'too_hot' => env('SMARTAC_THRESHOLD_HOT', 30),
        'too_cold' => env('SMARTAC_THRESHOLD_COLD', 18),
    ],
];
