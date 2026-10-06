<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SalesTarget extends Model
{
    use HasFactory;

    protected $fillable = ['target_type', 'target_ref_id', 'period_month', 'target_amount', 'notified_at', 'created_by'];

    protected $casts = [
        'period_month'   => 'date',
        'target_amount'  => 'decimal:2',
        'notified_at'    => 'datetime',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /** The User (if target_type=sr) or Partner (if target_type=dealer) this target is for. */
    public function subject(): User|Partner|null
    {
        return $this->target_type === 'sr'
            ? User::find($this->target_ref_id)
            : Partner::find($this->target_ref_id);
    }

    public function subjectName(): string
    {
        return $this->subject()?->name ?? '—';
    }

    /**
     * Actual confirmed sales for this target's subject within its month.
     * SR → their own Secondary Sales (sales they personally created).
     * Dealer → Primary Sales billed to that dealer.
     * Same "derive, don't duplicate-store" principle as every other
     * running total in this project (stock, partner balance).
     */
    public function achievement(): float
    {
        $start = $this->period_month->copy()->startOfMonth();
        $end   = $this->period_month->copy()->endOfMonth();

        $query = Sale::where('status', 'confirmed')->whereBetween('sale_date', [$start, $end]);

        if ($this->target_type === 'sr') {
            $query->where('type', 'secondary')->where('created_by', $this->target_ref_id);
        } else {
            $query->where('type', 'primary')->where('partner_id', $this->target_ref_id);
        }

        return (float) $query->sum('total');
    }

    public function achievementPercent(): float
    {
        if ((float) $this->target_amount <= 0) {
            return 0;
        }

        return round(($this->achievement() / (float) $this->target_amount) * 100, 1);
    }
}
