<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Slot extends Model
{
    protected $fillable = ['name', 'coordinates', 'status'];

    protected $casts = [
        'coordinates' => 'array',
    ];

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public $timestamps = false;
}
