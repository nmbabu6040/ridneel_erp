<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One enrolled face per user. "descriptor" is the 128-number face
     * embedding produced client-side by face-api.js during enrollment —
     * we store the numbers, never raw biometric templates in any other
     * form, and the reference photo purely for a human to visually
     * audit/re-enroll if needed.
     */
    public function up(): void
    {
        Schema::create('sr_face_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->json('descriptor'); // 128 floats from face-api.js
            $table->string('photo_path')->nullable();
            $table->timestamp('enrolled_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sr_face_profiles');
    }
};
