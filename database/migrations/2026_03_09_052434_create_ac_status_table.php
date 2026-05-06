<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::create('ac_statuses', function (Blueprint $table) {

            $table->id();

            $table->foreignId('ac_unit_id')
                ->constrained('ac_units')
                ->onDelete('cascade');

            $table->enum('power', ['ON', 'OFF'])->default('OFF');

            $table->integer('set_temperature')->default(24);

            $table->enum('mode', ['COOL', 'HEAT', 'DRY', 'FAN', 'AUTO'])->default('AUTO');

            $table->enum('fan_speed', ['AUTO', 'LOW', 'MEDIUM', 'HIGH'])->default('AUTO');

            $table->enum('swing', ['OFF', 'FULL', 'HALF', 'DOWN'])->default('OFF');

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('ac_statuses');
    }
};
