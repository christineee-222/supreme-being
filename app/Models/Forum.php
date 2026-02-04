<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\EssenceNumen;
use App\Models\Comment;


class Forum extends Model
{
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

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }


    public function essenceNumen()
    {
        return $this->belongsTo(EssenceNumen::class, 'essence_numen_id');
    }

    protected static function booted()
    {
        static::creating(function ($forum) {
            if (!$forum->essence_numen_id) {
                $essence = EssenceNumen::create([
                    'type' => 'forum',
                ]);

                $forum->essence_numen_id = $essence->id;
            }
        });
    }
}

