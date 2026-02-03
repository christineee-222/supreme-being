<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\EssenceNumen;

class Legislation extends Model
{
    protected $table = 'legislation';

    protected $fillable = [
        'title',
        'description',
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
        static::creating(function ($legislation) {
            if (!$legislation->essence_numen_id) {
                $essence = EssenceNumen::create([
                    'type' => 'legislation',
                ]);

                $legislation->essence_numen_id = $essence->id;
            }
        });
    }
}

