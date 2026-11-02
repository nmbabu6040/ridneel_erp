<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Per-product opt-in rather than a global setting — a distribution
     * business often sells BOTH perishables (medicine, food) that need
     * batch/expiry entry on every purchase, AND non-perishables
     * (hardware, stationery) where typing a batch number every time
     * would just be friction with zero benefit. This flag lets the
     * Purchase form show the batch/expiry fields only for products
     * that actually need them.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('tracks_batches')->default(false)->after('reorder_level');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('tracks_batches');
        });
    }
};
