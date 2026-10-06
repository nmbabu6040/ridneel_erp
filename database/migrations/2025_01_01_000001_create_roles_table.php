<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Roles table stores each role's permission list as a JSON array of
     * permission keys (e.g. ["users.view","users.create","sales.manage"]).
     * This keeps Phase 1 simple while remaining fully extensible — new
     * permission keys can be introduced by modules later without any
     * schema change.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');            // English name, e.g. "Sales Representative"
            $table->string('name_bn')->nullable(); // Bengali name, e.g. "বিক্রয় প্রতিনিধি"
            $table->string('slug')->unique();  // e.g. "sr", "dealer", "admin"
            $table->json('permissions')->nullable(); // array of permission keys
            $table->boolean('is_system')->default(false); // system roles cannot be deleted
            $table->timestamps();
        });

        // Seed nothing here — handled by RolePermissionSeeder so this
        // migration stays reversible and side-effect free.
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
