<?php

namespace App\Models;

use App\Casts\BinaryUuidFk;
use App\Models\Concerns\HasUniqueSlug;
use App\Models\Concerns\UsesBinaryUuidV7;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Forum extends Model
{
    use HasFactory, HasUniqueSlug, UsesBinaryUuidV7;

    protected $fillable = [
        'title',
        'description',
        'status',
        'user_id',
        'essence_numen_id',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => BinaryUuidFk::class,
            'essence_numen_id' => BinaryUuidFk::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function essenceNumen(): BelongsTo
    {
        return $this->belongsTo(EssenceNumen::class, 'essence_numen_id');
    }

    protected static function booted(): void
    {
        static::creating(function ($forum) {
            if (! $forum->essence_numen_id) {
                $essence = EssenceNumen::create([
                    'type' => 'forum',
                ]);

                $forum->essence_numen_id = $essence->id;
            }
        });
    }
}
