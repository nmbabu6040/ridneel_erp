<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'name_bn',
        'slug',
        'permissions',
        'is_system',
    ];

    protected $casts = [
        'permissions' => 'array',
        'is_system'   => 'boolean',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Does this role carry the given permission key?
     * "*" in the permissions array grants everything (Super Admin).
     */
    public function hasPermission(string $key): bool
    {
        $permissions = $this->permissions ?? [];

        return in_array('*', $permissions, true) || in_array($key, $permissions, true);
    }

    /**
     * Return the role's display name in the currently active app locale.
     */
    public function displayName(): string
    {
        if (app()->getLocale() === 'bn' && !empty($this->name_bn)) {
            return $this->name_bn;
        }

        return $this->name;
    }
}
