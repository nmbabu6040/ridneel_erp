<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SrAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'date',
        'check_in_at', 'check_in_lat', 'check_in_lng', 'check_in_photo', 'check_in_match_distance',
        'check_out_at', 'check_out_lat', 'check_out_lng', 'check_out_photo', 'check_out_match_distance',
        'status',
    ];

    protected $casts = [
        'date'                     => 'date',
        'check_in_at'              => 'datetime',
        'check_out_at'             => 'datetime',
        'check_in_lat'             => 'decimal:7',
        'check_in_lng'             => 'decimal:7',
        'check_out_lat'            => 'decimal:7',
        'check_out_lng'            => 'decimal:7',
        'check_in_match_distance'  => 'decimal:4',
        'check_out_match_distance' => 'decimal:4',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isCheckedIn(): bool
    {
        return !is_null($this->check_in_at) && is_null($this->check_out_at);
    }

    public function isComplete(): bool
    {
        return !is_null($this->check_in_at) && !is_null($this->check_out_at);
    }

    public function workedMinutes(): ?int
    {
        if (!$this->check_in_at || !$this->check_out_at) {
            return null;
        }

        return (int) $this->check_in_at->diffInMinutes($this->check_out_at);
    }
}
