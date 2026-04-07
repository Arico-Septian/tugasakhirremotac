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
        Schema::table('ac_units', function (Blueprint $table) {

            $table->time('timer_on')->nullable();
            $table->time('timer_off')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ac_units', function (Blueprint $table) {

            $table->dropColumn(['timer_on', 'timer_off']);
        });
    }
};
