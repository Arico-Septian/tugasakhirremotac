<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up()
    {
        Schema::table('ac_statuses', function (Blueprint $table) {

            $table->time('timer_on')->nullable();
            $table->time('timer_off')->nullable();

        });
    }

    public function down()
    {
        Schema::table('ac_statuses', function (Blueprint $table) {

            $table->dropColumn(['timer_on','timer_off']);

        });
    }

};
