<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EssenceNumen extends Model
{
    use HasFactory;

    protected $table = 'essence_numen';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'type',
    ];
}
