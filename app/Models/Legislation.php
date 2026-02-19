<?php

namespace App\Models;

use App\Models\Concerns\HasUniqueSlug;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Legislation extends Model
{
    use HasUniqueSlug, HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'legislation';

    protected $fillable = [
        'title',
        'description',
        'status',
        'user_id',
        'essence_numen_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function essenceNumen(): BelongsTo
    {
        return $this->belongsTo(EssenceNumen::class, 'essence_numen_id');
    }

    protected static function booted(): void
    {
        static::creating(function ($legislation) {
            if (! $legislation->essence_numen_id) {
                $essence = EssenceNumen::create([
                    'type' => 'legislation',
                ]);

                $legislation->essence_numen_id = $essence->id;
            }
        });
    }
}
