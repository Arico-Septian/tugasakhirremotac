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
        Schema::create('ac_units', function (Blueprint $table) {

            $table->id();

            $table->foreignId('room_id')->constrained()->onDelete('cascade');

            $table->string('name'); // AC 1
            $table->string('brand'); // GREE / LG
            $table->integer('ac_number');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ac_units');
    }
};
