<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title', 'slug', 'location', 'venue', 'date', 'time',
        'image', 'description', 'slots', 'registered',
        'is_virtual', 'is_beginner', 'has_medal', 'is_weekend',
        'is_active', 'early_bird_until',
    ];

    protected $casts = [
        'date'            => 'date',
        'early_bird_until'=> 'datetime',
        'is_virtual'      => 'boolean',
        'is_beginner'     => 'boolean',
        'has_medal'       => 'boolean',
        'is_weekend'      => 'boolean',
        'is_active'       => 'boolean',
    ];

    // ---- Relationships ----

    public function categories()
    {
        return $this->hasMany(EventCategory::class);
    }

    public function highlights()
    {
        return $this->hasMany(EventHighlight::class);
    }

    public function registrations()
    {
        return $this->hasMany(Registration::class);
    }

    // ---- Accessors ----

    public function getIsEarlyBirdActiveAttribute(): bool
    {
        return $this->early_bird_until && now()->lt($this->early_bird_until);
    }

    public function getSlotPercentAttribute(): int
    {
        if ($this->slots === 0) return 100;
        return (int) round(($this->registered / $this->slots) * 100);
    }

    public function getIsAlmostFullAttribute(): bool
    {
        return $this->slot_percent >= 90;
    }

    public function getImageUrlAttribute(): string
    {
        if ($this->image && \Storage::disk('public')->exists($this->image)) {
            return \Storage::url($this->image);
        }
        // Fallback placeholder
        return "https://images.unsplash.com/photo-1513593771513-7b58b6c4af38?w=600&h=400&fit=crop";
    }
}
