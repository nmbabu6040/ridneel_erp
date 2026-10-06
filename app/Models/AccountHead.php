<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AccountHead extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'name_bn', 'code', 'type', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function displayName(): string
    {
        return app()->getLocale() === 'bn' && $this->name_bn ? $this->name_bn : $this->name;
    }

    public function typeLabel(): string
    {
        $labels = [
            'asset'     => ['en' => 'Asset', 'bn' => 'সম্পদ'],
            'liability' => ['en' => 'Liability', 'bn' => 'দায়'],
            'income'    => ['en' => 'Income', 'bn' => 'আয়'],
            'expense'   => ['en' => 'Expense', 'bn' => 'ব্যয়'],
            'equity'    => ['en' => 'Equity', 'bn' => 'ইকুইটি'],
        ];

        return $labels[$this->type][app()->getLocale()] ?? $this->type;
    }
}
