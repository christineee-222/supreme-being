<?php

namespace App\Models;

use App\Casts\BinaryUuidFk;
use App\Models\Concerns\UsesBinaryUuidV7;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    use UsesBinaryUuidV7;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'body',
        'user_id',
        'forum_id',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => BinaryUuidFk::class,
            'forum_id' => BinaryUuidFk::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function forum(): BelongsTo
    {
        return $this->belongsTo(Forum::class);
    }
}

