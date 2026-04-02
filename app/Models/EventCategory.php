<?php
// app/Models/EventCategory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model
{
    protected $fillable = ['event_id', 'name', 'normal_price', 'early_bird_price'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    /**
     * Return the active price depending on whether early bird is still on.
     */
    public function getActivePriceAttribute(): int
    {
        $event = $this->event;
        if ($event && $event->is_early_bird_active && $this->early_bird_price) {
            return $this->early_bird_price;
        }
        return $this->normal_price;
    }
}
