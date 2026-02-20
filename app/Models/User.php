<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasUuids, Notifiable;

    public $incrementing = false;

    protected $keyType = 'string';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'workos_id',
        'avatar',
        'role',
        'is_moderator_probationary',
        'violation_count',
        'appeal_count',
        'is_indefinitely_restricted',
        'restriction_ends_at',
        'next_appeal_eligible_at',
        'account_created_at',
        'is_system_bot',
        'moderation_metadata',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'workos_id',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_admin' => 'boolean',
            'is_moderator_probationary' => 'boolean',
            'violation_count' => 'integer',
            'appeal_count' => 'integer',
            'is_indefinitely_restricted' => 'boolean',
            'restriction_ends_at' => 'datetime',
            'next_appeal_eligible_at' => 'datetime',
            'account_created_at' => 'datetime',
            'is_system_bot' => 'boolean',
            'moderation_metadata' => 'array',
        ];
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function moderatorApplication(): HasOne
    {
        return $this->hasOne(ModeratorApplication::class);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isModerator(): bool
    {
        return in_array($this->role, ['moderator', 'admin'], true);
    }
}
