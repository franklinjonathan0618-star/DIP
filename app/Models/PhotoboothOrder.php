<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class PhotoboothOrder extends Model
{
    protected $fillable = [
        'public_id',
        'email',
        'phone',
        'layout',
        'amount',
        'payment_status',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'midtrans_payment_type',
        'snap_token',
        'snap_redirect_url',
        'delivery_status',
        'paid_at',
    ];

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
        ];
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PhotoboothPhoto::class);
    }
}
