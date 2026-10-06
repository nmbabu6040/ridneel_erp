<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A single "partners" table covers Dealer, Retailer and Mechanic —
     * they share the same fields (name, contact, address, territory,
     * opening balance). The "type" column is what the rest of the
     * system (Sales, Mobile App login, KPI in later phases) filters on.
     * This avoids three near-identical tables and three near-identical
     * CRUD screens.
     */
    public function up(): void
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['dealer', 'retailer', 'mechanic']);
            $table->string('name');                 // contact person name
            $table->string('business_name')->nullable();
            $table->string('phone', 20);
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('territory')->nullable(); // area / route / beat
            $table->foreignId('linked_dealer_id')->nullable()
                ->constrained('partners')->nullOnDelete(); // retailer -> dealer they buy from
            $table->decimal('opening_balance', 14, 2)->default(0); // + means partner owes us
            $table->decimal('credit_limit', 14, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->unique(['phone', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('partners');
    }
};
