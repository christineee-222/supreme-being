<?php

namespace App\Models;

use App\Enums\ModeratorDecisionType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModeratorDecision extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'moderator_id',
        'report_id',
        'decision',
        'requires_cosign',
        'cosigned_by',
        'cosigned_at',
        'created_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'decision' => ModeratorDecisionType::class,
            'requires_cosign' => 'boolean',
            'cosigned_at' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function cosignedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cosigned_by');
    }
}
