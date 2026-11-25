<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('product_id')
                ->constrained('units')->nullOnDelete();
            $table->integer('base_quantity')->nullable()->after('quantity');
        });

        DB::table('sale_items')->whereNull('base_quantity')->update(['base_quantity' => DB::raw('quantity')]);
    }

    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
            $table->dropColumn('base_quantity');
        });
    }
};
