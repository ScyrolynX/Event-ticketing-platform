<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promoter extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'contact_email', 'contact_phone', 'commission_rate'];

    public function events()
    {
        return $this->hasMany(Event::class);
    }
}
