<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_item_id',
        'unique_code',
        'status',
    ];

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }
}
