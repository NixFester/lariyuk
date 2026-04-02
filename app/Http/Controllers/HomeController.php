<?php
// app/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use App\Models\Event;

class HomeController extends Controller
{
    public function index()
    {
        $events = Event::with(['categories', 'highlights'])
            ->where('is_active', true)
            ->orderBy('date')
            ->paginate(9);

        return view('home', compact('events'));
    }
}
