<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A monthly sales target for either an SR (target_type='sr',
     * target_ref_id = users.id, measured against their Secondary Sales)
     * or a Dealer (target_type='dealer', target_ref_id = partners.id,
     * measured against Primary Sales to that dealer). No FK constraint
     * on target_ref_id since it points to two different tables
     * depending on target_type (a lightweight polymorphic reference);
     * the application layer validates which table to check against.
     */
    public function up(): void
    {
        Schema::create('sales_targets', function (Blueprint $table) {
            $table->id();
            $table->enum('target_type', ['sr', 'dealer']);
            $table->unsignedBigInteger('target_ref_id');
            $table->date('period_month'); // always stored as the 1st of the month
            $table->decimal('target_amount', 14, 2);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['target_type', 'target_ref_id', 'period_month'], 'sales_targets_unique_period');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_targets');
    }
};
