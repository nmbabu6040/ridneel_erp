<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One table for both directions — "sales_return" (a dealer/retailer
     * sends goods back to us) and "purchase_return" (we send goods back
     * to a supplier) — same shape as the Sale/Purchase split in
     * Phase 3/5. "reference_type"/"reference_id" optionally link back
     * to the original Sale or Purchase for traceability, but are
     * nullable: a return can also be recorded standalone (goods
     * returned without the original invoice on hand). Quantities here
     * are NOT validated against the original invoice's quantity — see
     * README for why that's an intentional Phase-6 simplification.
     */
    public function up(): void
    {
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->string('return_no')->unique();
            $table->enum('type', ['sales_return', 'purchase_return']);
            $table->foreignId('partner_id')->constrained('partners')->restrictOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->restrictOnDelete();
            $table->string('reference_type')->nullable(); // "Sale" or "Purchase"
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->date('return_date');
            $table->string('reason')->nullable();
            $table->decimal('total', 14, 2)->default(0);
            $table->enum('status', ['confirmed', 'cancelled'])->default('confirmed');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'return_date']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
