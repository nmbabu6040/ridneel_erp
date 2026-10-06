<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sr_attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');

            $table->timestamp('check_in_at')->nullable();
            $table->decimal('check_in_lat', 10, 7)->nullable();
            $table->decimal('check_in_lng', 10, 7)->nullable();
            $table->string('check_in_photo')->nullable();
            $table->decimal('check_in_match_distance', 6, 4)->nullable(); // lower = better match

            $table->timestamp('check_out_at')->nullable();
            $table->decimal('check_out_lat', 10, 7)->nullable();
            $table->decimal('check_out_lng', 10, 7)->nullable();
            $table->string('check_out_photo')->nullable();
            $table->decimal('check_out_match_distance', 6, 4)->nullable();

            $table->enum('status', ['present', 'late'])->default('present');
            $table->timestamps();

            $table->unique(['user_id', 'date']); // one attendance row per SR per day
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sr_attendances');
    }
};
