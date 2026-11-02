<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Batch/expiry tracking lives on stock_movements itself (not a
     * separate "batches" table) — the ledger already carries one row
     * per quantity change, so a batch's remaining quantity is just
     * SUM(quantity) WHERE batch_no = X, exactly the same non-drift
     * principle as plain stock tracking. Both columns are nullable:
     * a business selling non-perishables (hardware, electronics) can
     * simply never fill them in, and everything keeps working exactly
     * as before.
     */
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('batch_no')->nullable()->after('quantity');
            $table->date('expiry_date')->nullable()->after('batch_no');
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn(['batch_no', 'expiry_date']);
        });
    }
};
