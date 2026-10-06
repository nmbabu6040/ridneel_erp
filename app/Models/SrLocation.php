<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SrLocation extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'latitude', 'longitude', 'accuracy_meters', 'recorded_at'];

    protected $casts = [
        'latitude'        => 'decimal:7',
        'longitude'       => 'decimal:7',
        'accuracy_meters' => 'decimal:2',
        'recorded_at'     => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
