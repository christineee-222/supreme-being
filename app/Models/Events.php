<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EssenceNumen;

class Events extends Model
{
    use HasFactory;

        protected static function booted()
    {
        static::created(function ($event) {
            $essence = EssenceNumen::create([
                'type' => 'event',
            ]);

            $event->essence_numen_id = $essence->id;
            $event->save();
        });
    }

    public function essenceNumen()
    {
        return $this->belongsTo(EssenceNumen::class);
    }
}
