<?php
// app/Http/Controllers/Admin/EventController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\EventCategory;
use App\Models\EventHighlight;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'total_events'        => Event::count(),
            'active_events'       => Event::where('is_active', true)->count(),
            'total_registrations' => Registration::count(),
            'paid_registrations'  => Registration::where('payment_status', 'paid')->count(),
            'total_revenue'       => Registration::where('payment_status', 'paid')->sum('total'),
        ];

        $recentRegistrations = Registration::with(['event', 'category'])
            ->latest()->limit(10)->get();

        return view('admin.dashboard', compact('stats', 'recentRegistrations'));
    }

    public function index()
    {
        $events = Event::with(['categories', 'registrations'])
            ->latest()->paginate(15);
        return view('admin.events.index', compact('events'));
    }

    public function create()
    {
        return view('admin.events.form', ['event' => null]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateEvent($request);

        $event = Event::create($this->eventData($validated));

        $this->handleImage($request, $event);
        $this->syncCategories($request, $event);
        $this->syncHighlights($request, $event);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil dibuat!');
    }

    public function edit(Event $event)
    {
        $event->load(['categories', 'highlights']);
        return view('admin.events.form', compact('event'));
    }

    public function update(Request $request, Event $event)
    {
        $validated = $this->validateEvent($request, $event->id);

        $event->update($this->eventData($validated));

        $this->handleImage($request, $event);
        $this->syncCategories($request, $event);
        $this->syncHighlights($request, $event);

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil diperbarui!');
    }

    public function destroy(Event $event)
    {
        if ($event->image) {
            Storage::disk('public')->delete($event->image);
        }
        $event->delete();

        return redirect()->route('admin.events.index')
            ->with('success', 'Event berhasil dihapus!');
    }

    // ---- Helpers ----

    private function validateEvent(Request $request, ?int $ignoreId = null): array
    {
        return $request->validate([
            'title'           => 'required|string|max:200',
            'location'        => 'required|string|max:100',
            'venue'           => 'required|string|max:200',
            'date'            => 'required|date',
            'time'            => 'required|string',
            'description'     => 'required|string',
            'slots'           => 'required|integer|min:1',
            'is_virtual'      => 'boolean',
            'is_beginner'     => 'boolean',
            'has_medal'       => 'boolean',
            'is_weekend'      => 'boolean',
            'is_active'       => 'boolean',
            'early_bird_until'=> 'nullable|date',
            'image'           => 'nullable|image',

            // Categories (repeated rows)
            'categories'               => 'required|array|min:1',
            'categories.*.name'        => 'required|string',
            'categories.*.normal_price'=> 'required|integer|min:0',
            'categories.*.limit'       => 'nullable|integer|min:1',

            // Highlights
            'highlights'   => 'nullable|array',
            'highlights.*' => 'required|string|max:200',
        ]);
    }

    private function eventData(array $validated): array
    {
        return [
            'title'            => $validated['title'],
            'slug'             => Str::slug($validated['title']) . '-' . time(),
            'location'         => $validated['location'],
            'venue'            => $validated['venue'],
            'date'             => $validated['date'],
            'time'             => $validated['time'],
            'description'      => $validated['description'],
            'slots'            => $validated['slots'],
            'is_virtual'       => $validated['is_virtual'] ?? false,
            'is_beginner'      => $validated['is_beginner'] ?? false,
            'has_medal'        => $validated['has_medal'] ?? true,
            'is_weekend'       => $validated['is_weekend'] ?? false,
            'is_active'        => $validated['is_active'] ?? true,
            'early_bird_until' => $validated['early_bird_until'] ?? null,
        ];
    }

    private function handleImage(Request $request, Event $event): void
    {
        if ($request->hasFile('image')) {
            // Delete old image
            if ($event->image) {
                Storage::disk('public')->delete($event->image);
            }
            $path = $request->file('image')->store('events', 'public');
            $event->update(['image' => $path]);
        }
    }

    private function syncCategories(Request $request, Event $event): void
    {
        $event->categories()->delete();

        foreach ($request->input('categories', []) as $cat) {
            $normalPrice = (int) $cat['normal_price'];
            $earlyBird   = $event->early_bird_until
                ? (int) round($normalPrice * 0.90) // 10% off
                : null;
            $limit = isset($cat['limit']) && $cat['limit'] ? (int) $cat['limit'] : 200;

            EventCategory::create([
                'event_id'          => $event->id,
                'name'              => $cat['name'],
                'normal_price'      => $normalPrice,
                'early_bird_price'  => $earlyBird,
                'limit'             => $limit,
            ]);
        }
    }

    private function syncHighlights(Request $request, Event $event): void
    {
        $event->highlights()->delete();
        foreach (array_filter($request->input('highlights', [])) as $h) {
            EventHighlight::create(['event_id' => $event->id, 'highlight' => $h]);
        }
    }
}
