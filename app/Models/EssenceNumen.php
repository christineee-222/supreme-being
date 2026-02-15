<?php

namespace App\Models;

use App\Models\Concerns\UsesBinaryUuidV7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class EssenceNumen extends Model
{
    use UsesBinaryUuidV7;

    protected $table = 'essence_numen';

    protected $fillable = ['type'];

    public function poll(): HasOne
    {
        return $this->hasOne(Poll::class, 'essence_numen_id');
    }
}
