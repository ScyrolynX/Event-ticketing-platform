<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiscountCode extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'type', 'value', 'event_id', 'valid_from', 'valid_until'];

    protected $casts = [
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Whether this code can be applied right now, to this specific event.
     * A null event_id on the code means it's valid sitewide.
     */
    public function isValidFor(int $eventId): bool
    {
        $now = now();

        if ($this->valid_from && $now->lt($this->valid_from)) return false;
        if ($this->valid_until && $now->gt($this->valid_until)) return false;
        if ($this->event_id !== null && $this->event_id !== $eventId) return false;

        return true;
    }

    public function apply(float $price): float
    {
        return $this->type === 'percentage'
            ? round($price * (1 - $this->value / 100), 2)
            : max(0, round($price - $this->value, 2));
    }
}
