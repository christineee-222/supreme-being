<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EssenceNumen extends Model
{
    protected $table = 'essence_numen';

    protected $fillable = ['type'];

    public function poll()
    {
        return $this->hasOne(Poll::class, 'essence_numen_id');
    }
}

