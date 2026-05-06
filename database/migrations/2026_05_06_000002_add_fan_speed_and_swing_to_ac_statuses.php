<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasFanSpeed = Schema::hasColumn('ac_statuses', 'fan_speed');
        $hasSwing = Schema::hasColumn('ac_statuses', 'swing');

        if ($hasFanSpeed && $hasSwing) {
            return;
        }

        Schema::table('ac_statuses', function (Blueprint $table) use ($hasFanSpeed, $hasSwing) {
            if (!$hasFanSpeed) {
                $table->enum('fan_speed', ['AUTO', 'LOW', 'MEDIUM', 'HIGH'])->default('AUTO');
            }

            if (!$hasSwing) {
                $table->enum('swing', ['OFF', 'FULL', 'HALF', 'DOWN'])->default('OFF');
            }
        });
    }

    public function down(): void
    {
        $columns = [];

        if (Schema::hasColumn('ac_statuses', 'fan_speed')) {
            $columns[] = 'fan_speed';
        }

        if (Schema::hasColumn('ac_statuses', 'swing')) {
            $columns[] = 'swing';
        }

        if (!$columns) {
            return;
        }

        Schema::table('ac_statuses', function (Blueprint $table) use ($columns) {
            $table->dropColumn($columns);
        });
    }
};
