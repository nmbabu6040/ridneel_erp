<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Append-only GPS ping trail. The browser sends one row every
     * ~90 seconds while an SR's attendance page stays open after
     * check-in (see public/js/sr-tracking.js). The "live" map simply
     * reads each SR's most recent row; a full route/beat replay for a
     * given day can be built later from the same table without any
     * schema change.
     */
    public function up(): void
    {
        Schema::create('sr_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->decimal('latitude', 10, 7);
            $table->decimal('longitude', 10, 7);
            $table->decimal('accuracy_meters', 8, 2)->nullable();
            $table->timestamp('recorded_at');
            $table->timestamps();

            $table->index(['user_id', 'recorded_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sr_locations');
    }
};
