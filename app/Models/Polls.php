<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EssenceNumen;

class Polls extends Model
{
    use HasFactory;

    protected $table = 'polls';

     protected static function booted()
    {
        static::created(function ($poll) {
            $essence = EssenceNumen::create();
            $poll->essence_numen_id = $essence->id;
            $poll->save();
        });
    }

    public function essenceNumen()
    {
        return $this->belongsTo(EssenceNumen::class);
    }
}
