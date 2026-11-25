<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * A product's base unit (products.unit_id, since Phase 2) always
     * stays what stock is counted in internally — Stock Ledger /
     * currentStock() / low-stock alerts need zero changes. These two
     * columns are purely optional: when set, Sales/Purchase forms let
     * staff enter a quantity in the secondary unit (e.g. "2 Carton")
     * and the system converts it to base units (200 Pieces) before
     * touching stock.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('secondary_unit_id')->nullable()->after('unit_id')
                ->constrained('units')->nullOnDelete();
            $table->decimal('conversion_factor', 12, 4)->nullable()->after('secondary_unit_id');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('secondary_unit_id');
            $table->dropColumn('conversion_factor');
        });
    }
};
