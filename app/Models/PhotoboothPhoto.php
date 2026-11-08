<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PhotoboothPhoto extends Model
{
    protected $fillable = [
        'kind',
        'photo_number',
        'disk',
        'path',
        'mime_type',
        'size_bytes',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(PhotoboothOrder::class, 'photobooth_order_id');
    }
}
