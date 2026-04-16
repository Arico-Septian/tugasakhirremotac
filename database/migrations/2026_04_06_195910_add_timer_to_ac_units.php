<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $hasTimerOn = Schema::hasColumn('ac_units', 'timer_on');
        $hasTimerOff = Schema::hasColumn('ac_units', 'timer_off');

        if (!$hasTimerOn || !$hasTimerOff) {

            Schema::table('ac_units', function (Blueprint $table) use ($hasTimerOn, $hasTimerOff) {

                if (!$hasTimerOn) {
                    $table->time('timer_on')->nullable()->comment('Waktu nyala AC');
                }

                if (!$hasTimerOff) {
                    $table->time('timer_off')->nullable()->comment('Waktu mati AC');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('ac_units', function (Blueprint $table) {

            if (Schema::hasColumn('ac_units', 'timer_on')) {
                $table->dropColumn('timer_on');
            }

            if (Schema::hasColumn('ac_units', 'timer_off')) {
                $table->dropColumn('timer_off');
            }
        });
    }
};
