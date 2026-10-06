<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Phase 1/2 defined partners.type as an ENUM('dealer','retailer',
     * 'mechanic'). Phase 5 needs a 4th value ("supplier") so Purchases
     * can reuse the same Partner model + partner_transactions ledger
     * instead of duplicating a whole Supplier module. Widening an ENUM
     * column normally needs doctrine/dbal (not installed here), so this
     * uses raw SQL instead — MySQL-specific, matching the DB_CONNECTION
     * this project's README already assumes. If you're on a different
     * database, adjust the two statements below to your driver's
     * equivalent "alter column type" syntax.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE partners MODIFY COLUMN type VARCHAR(20) NOT NULL");
        }
        // sqlite/pgsql already store enums permissively enough for this
        // app's own Rule::in() validation to be the real gatekeeper —
        // no schema change needed there.
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE partners MODIFY COLUMN type ENUM('dealer','retailer','mechanic') NOT NULL");
        }
    }
};
