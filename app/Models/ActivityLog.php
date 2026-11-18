<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'user_name',
        'role_name',
        'action',
        'module',
        'description',
        'ip_address',
        'user_agent',
        'properties',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'properties' => 'array',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * Pengguna yang melakukan aktivitas.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope query untuk filter pencarian teks.
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('description', 'like', "%{$term}%")
                ->orWhere('user_name', 'like', "%{$term}%")
                ->orWhere('ip_address', 'like', "%{$term}%");
        });
    }

    /**
     * Scope query untuk filter modul.
     */
    public function scopeByModule(Builder $query, ?string $module): Builder
    {
        return empty($module) ? $query : $query->where('module', $module);
    }

    /**
     * Scope query untuk filter aksi.
     */
    public function scopeByAction(Builder $query, ?string $action): Builder
    {
        return empty($action) ? $query : $query->where('action', $action);
    }

    /**
     * Scope query untuk filter user id.
     */
    public function scopeByUser(Builder $query, mixed $userId): Builder
    {
        return empty($userId) ? $query : $query->where('user_id', $userId);
    }
}
