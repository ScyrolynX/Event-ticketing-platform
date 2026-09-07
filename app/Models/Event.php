<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'organizer_id',
        'promoter_id',
        'title',
        'category',
        'description',
        'venue',
        'event_date',
    ];

    protected $casts = [
        'event_date' => 'datetime',
    ];

    public function ticketTypes()
    {
        return $this->hasMany(TicketType::class);
    }

    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    public function promoter()
    {
        return $this->belongsTo(Promoter::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class);
    }
}
