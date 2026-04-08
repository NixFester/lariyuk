<?php
// app/Models/EventCategory.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventCategory extends Model
{
    protected $fillable = ['event_id', 'name', 'normal_price', 'early_bird_price', 'limit'];

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

    /**
     * Check if the category has available slots for registration.
     * Returns false if the limit has been exceeded.
     */
    public function hasAvailableSlots(): bool
    {
        $registrationCount = $this->registrations()
            ->where('payment_status', 'paid')
            ->count();
        
        return $registrationCount < $this->limit;
    }

    /**
     * Get the number of available slots for this category.
     */
    public function getAvailableSlots(): int
    {
        $registrationCount = $this->registrations()
            ->where('payment_status', 'paid')
            ->count();
        
        return max(0, $this->limit - $registrationCount);
    }

    /**
     * Get the current registration count for this category.
     */
    public function getRegistrationCount(): int
    {
        return $this->registrations()
            ->where('payment_status', 'paid')
            ->count();
    }
}
