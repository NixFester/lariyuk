<?php
// app/Models/EventHighlight.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EventHighlight extends Model
{
    protected $fillable = ['event_id', 'highlight'];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
