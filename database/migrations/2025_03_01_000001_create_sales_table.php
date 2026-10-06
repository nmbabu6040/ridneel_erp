<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One table for both Primary Sales (Company → Dealer) and Secondary
     * Sales (Dealer → Retailer) — distinguished by "type". They share
     * an identical structure (buyer, items, totals, payment), so a
     * single table + type column keeps reporting and code simple
     * instead of duplicating everything twice.
     */
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->enum('type', ['primary', 'secondary']);
            $table->foreignId('partner_id')->constrained('partners')->restrictOnDelete(); // buyer: dealer or retailer
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->date('sale_date');
            $table->decimal('subtotal', 14, 2)->default(0);
            $table->decimal('discount', 14, 2)->default(0);
            $table->decimal('total', 14, 2)->default(0);
            $table->decimal('paid_amount', 14, 2)->default(0);
            $table->decimal('due_amount', 14, 2)->default(0);
            $table->enum('status', ['confirmed', 'cancelled'])->default('confirmed');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'sale_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
