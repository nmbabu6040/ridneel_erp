<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->string('name');        // e.g. "Piece", "Kilogram", "Liter"
            $table->string('name_bn')->nullable(); // e.g. "পিস", "কেজি", "লিটার"
            $table->string('short_code', 10)->unique(); // e.g. "pcs", "kg", "ltr"
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
    }
};
