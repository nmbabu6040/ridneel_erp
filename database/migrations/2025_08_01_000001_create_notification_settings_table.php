<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per notification event (e.g. "low_stock", "new_invoice").
     * email_enabled is the master on/off switch an admin can flip from
     * the Notification Settings page. extra_recipients is a comma-
     * separated list of additional email addresses that should get
     * this event's emails on top of whichever users already qualify
     * (e.g. everyone with accounting.view) — kept as plain text rather
     * than a separate table since a handful of extra addresses per
     * event is all this ever needs.
     */
    public function up(): void
    {
        Schema::create('notification_settings', function (Blueprint $table) {
            $table->id();
            $table->string('event_key')->unique();
            $table->string('label');
            $table->string('label_bn')->nullable();
            $table->boolean('email_enabled')->default(true);
            $table->text('extra_recipients')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_settings');
    }
};
