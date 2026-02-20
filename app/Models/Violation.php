<?php

namespace App\Models;

use App\Enums\ViolationConsequence;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Violation extends Model
{
    use HasFactory, HasUuids;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'user_id',
        'report_id',
        'moderator_decision_id',
        'confirmed_by',
        'rule_reference',
        'violation_number',
        'consequence_applied',
        'restriction_ends_at',
        'applied_to_user',
        'moderator_note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'consequence_applied' => ViolationConsequence::class,
            'violation_number' => 'integer',
            'applied_to_user' => 'boolean',
            'restriction_ends_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function report(): BelongsTo
    {
        return $this->belongsTo(Report::class);
    }

    public function decision(): BelongsTo
    {
        return $this->belongsTo(ModeratorDecision::class, 'moderator_decision_id');
    }

    public function confirmedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }
}
