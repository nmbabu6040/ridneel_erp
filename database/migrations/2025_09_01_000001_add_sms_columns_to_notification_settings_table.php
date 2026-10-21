<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notification_settings', function (Blueprint $table) {
            $table->boolean('sms_enabled')->default(false)->after('email_enabled');
            $table->text('sms_extra_recipients')->nullable()->after('extra_recipients');
        });
    }

    public function down(): void
    {
        Schema::table('notification_settings', function (Blueprint $table) {
            $table->dropColumn(['sms_enabled', 'sms_extra_recipients']);
        });
    }
};
