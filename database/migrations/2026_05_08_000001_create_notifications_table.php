<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('type', 32)->index();              // device_offline, temp_alert, schedule_run, system, info
            $table->string('severity', 16)->default('info');  // info, success, warning, error
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('link')->nullable();               // optional URL to click through
            $table->json('meta')->nullable();                 // optional structured data
            $table->timestamp('read_at')->nullable()->index();
            $table->timestamps();

            $table->index(['user_id', 'read_at']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
