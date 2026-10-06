<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Append-only ledger for a partner's running balance — exactly the
     * same design principle as stock_movements. A partner's live
     * balance = partners.opening_balance + SUM(partner_transactions.amount).
     * "amount" is signed: +ve increases what the partner owes us
     * (a sale), -ve decreases it (a payment received). This table is
     * what Phase 5 (full Accounting) will build its ledger reports on.
     */
    public function up(): void
    {
        Schema::create('partner_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('partner_id')->constrained('partners')->cascadeOnDelete();
            $table->enum('type', ['sale', 'payment', 'return', 'adjustment']);
            $table->decimal('amount', 14, 2); // signed
            $table->string('reference_type')->nullable(); // e.g. "Sale"
            $table->unsignedBigInteger('reference_id')->nullable(); // e.g. sales.id
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['partner_id']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partner_transactions');
    }
};
