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
        Schema::create('guests', function (Blueprint $table) {
            $table->id();
            $table->uuid('token')->unique()->index();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('sales_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('company')->nullable();
            $table->string('title')->nullable();
            $table->string('phone', 30)->index();
            $table->boolean('vip')->default(false);
            $table->string('vip_tier', 20)->default('REGULAR'); // REGULAR | VIP | VVIP
            $table->integer('pax')->default(1);
            $table->string('status', 20)->default('pending')->index(); // pending | invited | confirmed | declined | attended
            
            // Pos 1: Gate Check-in
            $table->timestamp('claimed_at')->nullable();
            
            // Pos 2: Souvenir Handover
            $table->boolean('souvenir_claimed')->default(false);
            $table->timestamp('souvenir_claimed_at')->nullable();

            // Pos 3: Snack Box Handover
            $table->boolean('snack_claimed')->default(false);
            $table->timestamp('snack_claimed_at')->nullable();

            $table->string('car_model')->nullable();
            $table->timestamps();

            // Strict Anti-Duplicate Constraint: 1 phone per event
            $table->unique(['event_id', 'phone'], 'guests_event_phone_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('guests');
    }
};
