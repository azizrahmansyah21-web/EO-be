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
        Schema::create('scan_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guest_id')->constrained('guests')->cascadeOnDelete();
            $table->foreignId('operator_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedTinyInteger('pos')->index(); // 1 = Gate, 2 = Souvenir, 3 = Snack
            $table->string('action', 50); // checkin | souvenir_handover | snack_handover
            $table->text('device_info')->nullable();
            $table->timestamp('scanned_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scan_logs');
    }
};
