# Event Category Registration Limits - Implementation Guide

## Overview
This document explains how to implement the new event category registration limits system. Each event category can now have its own registration limit (default: 200).

## Database Changes
A new migration has been created:
- **File**: `2026_04_08_000000_add_limit_to_event_categories.php`
- **Changes**: Adds a `limit` column to `event_categories` table with default value of 200

### To apply the migration:
```bash
php artisan migrate
```

## Backend Implementation

### EventCategory Model
The `EventCategory` model has been updated with three new methods:

```php
// Check if category has available slots
$category->hasAvailableSlots() : bool

// Get the number of available slots
$category->getAvailableSlots() : int

// Get current registration count for this category
$category->getRegistrationCount() : int
```

### RegistrationController
New methods added for API endpoints:

#### 1. Check Single Category Availability
- **Route**: `GET /checkout/api/categories/{categoryId}/availability`
- **Response**:
```json
{
  "id": 1,
  "name": "5K",
  "limit": 200,
  "registration_count": 150,
  "available_slots": 50,
  "has_slots": true,
  "message": "Sisa kuota: 50 dari 200"
}
```

#### 2. Get All Categories Availability for an Event
- **Route**: `GET /checkout/api/events/{eventSlug}/categories-availability`
- **Response**:
```json
{
  "event_id": 1,
  "event_slug": "marathon-2026",
  "event_title": "Marathon 2026",
  "categories": [
    {
      "id": 1,
      "name": "5K",
      "normal_price": 150000,
      "early_bird_price": 120000,
      "limit": 200,
      "registration_count": 150,
      "available_slots": 50,
      "has_slots": true
    },
    ...
  ]
}
```

### Registration Validation
The `store()` method in RegistrationController now validates category limits:
- If a category is full, registration is rejected with an error message
- The system counts only paid registrations (`payment_status = 'paid'`)

## Frontend Implementation

### JavaScript Utility
A JavaScript utility class is available at: `resources/js/CategoryLimitValidator.js`

#### Setup
Import in your blade template or JavaScript bundle:
```javascript
import CategoryLimitValidator from './CategoryLimitValidator.js';
```

#### Basic Usage

##### 1. Check Single Category Availability
```javascript
const category = {
    id: 1,
    name: "5K",
    limit: 200,
    registration_count: 150
};

const availability = CategoryLimitValidator.checkAvailability(category);
console.log(availability);
// Output: { available: true, slotsRemaining: 50, message: "Sisa kuota: 50 dari 200" }
```

##### 2. Check Multiple Categories
```javascript
const categories = [
    { id: 1, name: "5K", limit: 200, registration_count: 150 },
    { id: 2, name: "10K", limit: 200, registration_count: 200 },
];

const statuses = CategoryLimitValidator.checkMultipleAvailability(categories);
// Returns availability status for each category
```

##### 3. Real-time Form Validation
```javascript
const categorySelect = document.getElementById('event_category_id');
const statusDiv = document.getElementById('category-status');
const categories = [...]; // Your categories array

CategoryLimitValidator.setupRealTimeValidation(
    categorySelect,
    statusDiv,
    categories
);
```

##### 4. Fetch Availability from Server
```javascript
const availability = await CategoryLimitValidator.fetchCategoryAvailability(1);
console.log(availability);
```

##### 5. Display Availability in UI
```javascript
const category = { /* ... */ };
const element = document.getElementById('availability-message');

CategoryLimitValidator.displayStatus(element, category, { 
    showPercentage: true 
});
```

## Example Blade Template Integration

### Checkout Form with Real-time Validation
```blade
<form action="{{ route('checkout.store') }}" method="POST">
    @csrf
    
    <div class="form-group">
        <label for="event_category_id">Pilih Kategori</label>
        <select name="event_category_id" id="event_category_id" required>
            <option value="">-- Pilih Kategori --</option>
            @foreach($event->categories as $cat)
                <option value="{{ $cat->id }}">
                    {{ $cat->name }} - Rp{{ number_format($cat->active_price) }}
                </option>
            @endforeach
        </select>
        <small id="category-status"></small>
    </div>
    
    <!-- Other form fields -->
    
    <button type="submit">Daftar</button>
</form>

<script type="module">
    import CategoryLimitValidator from '{{ asset('js/CategoryLimitValidator.js') }}';
    
    const categories = @json($event->categories->map(fn($c) => [
        'id' => $c->id,
        'name' => $c->name,
        'limit' => $c->limit,
        'registration_count' => $c->getRegistrationCount(),
    ]));
    
    const selectElement = document.getElementById('event_category_id');
    const statusElement = document.getElementById('category-status');
    
    CategoryLimitValidator.setupRealTimeValidation(
        selectElement,
        statusElement,
        categories
    );
</script>
```

## Validation Logic

### How Slots Are Counted
- **Counted**: Only registrations with `payment_status = 'paid'`
- **Not Counted**: Pending registrations that haven't completed payment

### Limit Enforcement
1. **Backend**: When a user submits the registration form, the system checks if the category is full
2. **Frontend**: Display real-time availability status as users select categories
3. **Error Message**: If a category is full:
   ```
   Maaf, kuota untuk kategori [Category Name] telah penuh. Silakan pilih kategori lain.
   ```

## Management

### Updating Category Limits
```php
// In Laravel Tinker or custom command
$category = EventCategory::find(1);
$category->update(['limit' => 250]);

// Check current availability
echo $category->getAvailableSlots(); // Returns available slots
echo $category->getRegistrationCount(); // Returns paid registrations
```

### Checking Category Status (Artisan Tinker)
```php
php artisan tinker

$category = EventCategory::findOrFail(1);
$category->hasAvailableSlots();        // bool
$category->getAvailableSlots();        // int
$category->getRegistrationCount();     // int
```

## API Testing

### Using cURL
```bash
# Check single category
curl http://localhost:8000/checkout/api/categories/1/availability

# Get all categories for an event
curl http://localhost:8000/checkout/api/events/marathon-2026/categories-availability
```

### Using JavaScript Fetch
```javascript
// Check category availability
const response = await fetch('/checkout/api/categories/1/availability');
const data = await response.json();
console.log(data);
```

## Notes
- Default limit for new categories is **200**
- The system is strict about category capacity - it does NOT allow overbooking
- Only confirms registrations (paid status) count toward the limit
- The JavaScript validator provides UI feedback before submission
- The backend validator provides final authorization
