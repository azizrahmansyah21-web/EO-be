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
        Schema::create('wa_blast_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('guest_id')->constrained('guests')->cascadeOnDelete();
            $table->string('phone_target', 30);
            $table->text('message_content');
            $table->string('status', 20)->default('queued')->index(); // queued | processing | sent | failed
            $table->unsignedInteger('retry_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('wa_blast_jobs');
    }
};
