<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Audit trail: every email this system attempts to send gets a row
     * here, success or failure. Without this, "did the low-stock email
     * actually go out?" is unanswerable from the UI — this makes it
     * answerable from a simple list page instead of digging through
     * server mail logs.
     */
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('event_key');
            $table->string('subject');
            $table->text('recipients'); // comma-separated, as actually sent to
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['event_key', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
