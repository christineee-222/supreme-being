<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EssenceNumen extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $table = 'essence_numen';

    protected $fillable = ['type'];

    public function poll(): HasOne
    {
        return $this->hasOne(Poll::class, 'essence_numen_id');
    }
}
