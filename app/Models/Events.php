<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\EssenceNumen;

class Events extends Model
{
    use HasFactory;

    public function essenceNumen()
    {
        return $this->belongsTo(EssenceNumen::class);
    }
}
