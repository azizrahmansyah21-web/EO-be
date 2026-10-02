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
        Schema::create('events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subtitle')->nullable();
            $table->date('event_date');
            $table->string('event_time')->default('09:00 - 15:00 WIB');
            $table->string('venue');
            $table->text('address');
            $table->text('maps_url')->nullable();
            $table->string('image_url')->nullable();
            $table->string('status', 20)->default('active')->index(); // 'draft' | 'active' | 'completed'
            $table->integer('quota_target')->default(300);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('events');
    }
};
