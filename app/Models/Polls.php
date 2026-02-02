<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Polls extends Model
{
    use HasFactory;

    public function essenceNumen()
    {
        return $this->belongsTo(EssenceNumen::class);
    }
}
