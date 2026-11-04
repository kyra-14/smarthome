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
        Schema::create('devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('room_id')->constrained('rooms')->cascadeOnDelete();
            $table->string('name');
            $table->string('type'); // light, ac, lock, camera, fan, tv, socket, curtain, purifier
            $table->boolean('is_on')->default(false);
            $table->integer('value')->default(0); // brightness / temp / percentage
            $table->json('settings')->nullable();
            $table->decimal('power_watts', 8, 2)->default(0);
            $table->boolean('is_online')->default(true);
            $table->integer('battery_percent')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
