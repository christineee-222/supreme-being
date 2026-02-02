<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\EssenceNumen;
use App\Models\User;

class Polls extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
        'starts_at',
        'ends_at',
        'user_id',
        'essence_numen_id',
    ];

    // A poll belongs to a user (creator / owner)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // A poll belongs to an essence
    public function essenceNumen()
    {
        return $this->belongsTo(EssenceNumen::class, 'essence_numen_id');
    }

    // Auto-create essence when a poll is created
    protected static function booted()
    {
        static::creating(function ($poll) {
            if (!$poll->essence_numen_id) {
                $essence = EssenceNumen::create([
                    'type' => 'poll',
                ]);

                $poll->essence_numen_id = $essence->id;
            }
        });
    }
}


