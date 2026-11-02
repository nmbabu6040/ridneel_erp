<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Deliberately a singleton table (always exactly one row, id=1) —
     * not a key-value settings table — because every field here has a
     * known shape and is read on nearly every page load (sidebar brand,
     * invoice header, email header). A single typed row is simpler and
     * faster than SELECTing several key-value rows per request.
     */
    public function up(): void
    {
        Schema::create('company_settings', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('My Company');
            $table->string('name_bn')->nullable();
            $table->string('tagline')->nullable();
            $table->string('logo_path')->nullable();
            $table->string('favicon_emoji', 8)->default('🏢');
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('currency_symbol', 10)->default('৳');
            $table->string('currency_code', 10)->default('BDT');
            $table->text('invoice_footer_note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_settings');
    }
};
