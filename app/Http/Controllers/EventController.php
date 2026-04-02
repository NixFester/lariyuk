<?php
// app/Http/Controllers/EventController.php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request)
    {
        $query = Event::with(['categories', 'highlights'])
            ->where('is_active', true);

        if ($search = $request->get('q')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('venue', 'like', "%{$search}%");
            });
        }

        if ($location = $request->get('location')) {
            $query->where('location', $location);
        }

        if ($category = $request->get('category')) {
            $query->whereHas('categories', fn($q) => $q->where('name', $category));
        }

        $sort = $request->get('sort', 'date_asc');
        match ($sort) {
            'date_desc'  => $query->orderByDesc('date'),
            'price_asc'  => $query->orderBy('date'),   // price sort done in-memory or join
            default      => $query->orderBy('date'),
        };

        $events    = $query->paginate(9)->withQueryString();
        $locations = Event::where('is_active', true)->distinct()->pluck('location');

        return view('events.index', compact('events', 'locations', 'sort', 'search'));
    }

    public function show(string $slug)
    {
        $event = Event::with(['categories', 'highlights'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return view('events.show', compact('event'));
    }
}
