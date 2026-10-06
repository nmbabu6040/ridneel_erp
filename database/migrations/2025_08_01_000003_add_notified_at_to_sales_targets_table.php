<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sales_targets', function (Blueprint $table) {
            // Set the first time achievement crosses 100% and the
            // "target achieved" email fires — checked before sending
            // again so a target that stays above 100% for the rest of
            // the month doesn't re-email on every subsequent sale.
            $table->timestamp('notified_at')->nullable()->after('target_amount');
        });
    }

    public function down(): void
    {
        Schema::table('sales_targets', function (Blueprint $table) {
            $table->dropColumn('notified_at');
        });
    }
};
