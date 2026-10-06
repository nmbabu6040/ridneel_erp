<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SrFaceProfile extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'descriptor', 'photo_path', 'enrolled_at'];

    protected $casts = [
        'descriptor'  => 'array',
        'enrolled_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
