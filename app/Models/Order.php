<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_uuid',
        'customer_name',
        'customer_phone',
        'customer_email',
        'total_before_discount',
        'discount_amount',
        'discount_percent',
        'promocode_code',
        'total',
        'status',
    ];

    protected $casts = [
        'total_before_discount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function getRouteKeyName(): string
    {
        return 'order_uuid';
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
