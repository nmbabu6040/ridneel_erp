<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Append-only stock ledger. Current stock for a product/warehouse is
     * always SUM(quantity) where quantity is signed (+in / -out) — never
     * a separately-maintained "current_stock" counter, so it can never
     * drift out of sync with the actual entries. Purchase, Sales, Return
     * and Transfer modules in later phases all write rows here instead
     * of introducing their own stock-adjustment logic.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->enum('type', [
                'opening',        // opening stock entry
                'purchase_in',    // Phase 5: purchase received
                'sales_out',      // Phase 3: sold to dealer/retailer
                'return_in',      // Phase 6: customer return
                'return_out',     // Phase 6: return to supplier
                'adjustment_in',  // manual correction (found stock)
                'adjustment_out', // manual correction (damage/loss)
                'transfer_in',    // warehouse-to-warehouse transfer in
                'transfer_out',   // warehouse-to-warehouse transfer out
            ]);
            $table->integer('quantity'); // ALWAYS signed: +ve for *_in / opening, -ve for *_out
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->string('reference_no')->nullable(); // PO number, invoice number, etc.
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['product_id', 'warehouse_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
