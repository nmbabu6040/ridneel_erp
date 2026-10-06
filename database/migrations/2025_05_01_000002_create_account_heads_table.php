<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A deliberately lightweight Chart of Accounts — enough to
     * categorize expenses and give the business a named list of
     * accounts (Asset/Liability/Income/Expense/Equity), NOT a full
     * double-entry general ledger with journal postings. Sales,
     * Purchases and Payments already post to stock_movements /
     * partner_transactions (Phase 2/3/5); wiring every one of those
     * into formal journal entries against these heads would be a
     * much larger accounting-engine project than a single phase can
     * responsibly ship bug-free. This gives a real, useful structure
     * now and can grow into full GL posting later without breaking
     * this table.
     */
    public function up(): void
    {
        Schema::create('account_heads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('name_bn')->nullable();
            $table->string('code')->unique();
            $table->enum('type', ['asset', 'liability', 'income', 'expense', 'equity']);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_heads');
    }
};
