<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\EssenceNumen;

class Event extends Model
{
    protected $fillable = [
        'title',
        'description',
        'starts_at',
        'ends_at',
        'status',
        'user_id',
        'essence_numen_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function essenceNumen()
    {
        return $this->belongsTo(EssenceNumen::class, 'essence_numen_id');
    }

    protected static function booted()
    {
        static::creating(function ($event) {
            if (!$event->essence_numen_id) {
                $essence = EssenceNumen::create([
                    'type' => 'event',
                ]);

                $event->essence_numen_id = $essence->id;
            }
        });
    }
}

