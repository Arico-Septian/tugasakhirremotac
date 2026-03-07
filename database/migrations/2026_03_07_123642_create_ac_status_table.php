<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ac_status', function (Blueprint $table) {
            $table->id();
            $table->string('room');
            $table->integer('ac_id');
            $table->string('brand');
            $table->string('power');
            $table->integer('temperature');
            $table->string('mode');
            $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ac_status');
    }
};
