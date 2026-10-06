<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->after('id')
                ->constrained('roles')->nullOnDelete();
            $table->string('phone', 20)->nullable()->after('email');
            $table->enum('status', ['active', 'inactive', 'suspended'])
                ->default('active')->after('phone');
            $table->string('preferred_locale', 5)->default('bn')->after('status');
            $table->string('avatar')->nullable()->after('preferred_locale');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
            $table->dropColumn(['phone', 'status', 'preferred_locale', 'avatar']);
        });
    }
};
