# ADMIN FOLLOW-UP GUIDE - Linking Re-registered Users to Events

## Overview

When users re-register via the apology link, their registration is created with placeholder values:
- `event_id = 0`
- `event_category_id = 0`

**The admin must update these fields** to link the registration to the correct event they originally registered for.

---

## Option 1: Using Admin Dashboard (Recommended)

### Step 1: Find Pending Re-registrations

1. Go to: **Admin Dashboard → Registrations**
2. Look for registrations with:
   - `Event: Unknown` or `Event ID: 0`
   - `Payment Status: Paid` ✓
   - `Created: Recently`

### Step 2: Click on Registration

1. Click on the registration to view details
2. You'll see:
   - ✅ Email
   - ✅ Name
   - ✅ Phone
   - ✅ All participant data
   - ❓ Event: (Unknown/0)
   - ❓ Category: (Unknown/0)

### Step 3: Search for Original Event

Look for clues about which event they registered for:

**In this registration:**
- Check notes/comments if any
- Check previous email sent to them
- Check payment records

**Cross-reference **:
- Look up their email in other registrations (for same event)
- Check payment records with their name
- Ask via email confirmation: "Which event did you register for?"

### Step 4: Update Registration

If admin panel has edit feature:
1. Click "Edit" on the registration
2. Select proper event from dropdown
3. Select proper category from dropdown
4. Click "Save"
5. System automatically marks as ready

If admin panel doesn't have dropdown:
- Use SQL query below instead

---

## Option 2: Using Direct Database Updates (SQL)

### Finding Pending Registrations

```sql
-- Get all re-registrations needing linking
SELECT 
    id,
    email,
    nama_peserta,
    phone,
    invoice_number,
    event_id,
    event_category_id,
    created_at
FROM registrations
WHERE event_id = 0 AND event_category_id = 0 AND payment_status = 'paid'
ORDER BY created_at DESC;
```

### Researching the Correct Event

```sql
-- Find events that this email registered for before (if any history)
SELECT DISTINCT 
    r.event_id,
    e.title,
    e.date,
    r.event_category_id,
    ec.name as category_name,
    COUNT(r.id) as registration_count
FROM registrations r
JOIN events e ON r.event_id = e.id
JOIN event_categories ec ON r.event_category_id = ec.id
WHERE r.email = 'user@example.com' AND r.event_id != 0
GROUP BY r.event_id, r.event_category_id
ORDER BY e.date DESC;
```

### Manual Update (One Registration)

```sql
-- Verify first
SELECT * FROM registrations WHERE id = 123;

-- Then update
UPDATE registrations
SET 
    event_id = 5,
    event_category_id = 12,
    updated_at = NOW()
WHERE id = 123;

-- Verify update
SELECT * FROM registrations WHERE id = 123;
```

### Batch Update (Multiple Registrations)

```sql
-- Careful! Only do this if you're certain about multiple registrations
-- Example: All re-registrations for "10K" category of event ID 5

UPDATE registrations
SET 
    event_id = 5,
    event_category_id = 12
WHERE 
    event_id = 0 
    AND event_category_id = 0 
    AND payment_status = 'paid'
    AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY);

-- Then verify
SELECT COUNT(*) FROM registrations 
WHERE event_id = 5 AND event_category_id = 12 AND payment_status = 'paid';
```

---

## Verifying Registered Counts

### After updating a registrations, verify counts:

```sql
-- Check event registered counter
SELECT 
    id,
    title,
    registered,
    slots
FROM events
WHERE id = 5;

-- Count actual paid registrations for this event
SELECT COUNT(*) as paid_registrations
FROM registrations
WHERE event_id = 5 AND payment_status = 'paid';

-- Should match! If not, update manually:
UPDATE events
SET registered = (
    SELECT COUNT(*)
    FROM registrations
    WHERE event_id = 5 AND payment_status = 'paid'
)
WHERE id = 5;
```

---

## Multi-Step Process Example

**Scenario:** Admin finds re-registration for "user@example.com"

### Step 1: Identify User
```sql
SELECT * FROM registrations 
WHERE email = 'user@example.com' AND event_id = 0;
```
Result: Invoice INV-20260409-ABC123

### Step 2: Look for Previous Records
```sql
SELECT * FROM registrations 
WHERE email = 'user@example.com' 
ORDER BY created_at DESC 
LIMIT 10;
```
Find: They had registration for "Marathon 10K" (Event ID 5, Category ID 12) that was deleted

### Step 3: Verify Event Exists
```sql
SELECT e.id, e.title, ec.id, ec.name
FROM events e
JOIN event_categories ec ON e.id = ec.event_id
WHERE e.id = 5 AND ec.id = 12;
```
Result: ✓ Event exists, category exists

### Step 4: Update Re-registration
```sql
UPDATE registrations
SET event_id = 5, event_category_id = 12
WHERE email = 'user@example.com' AND event_id = 0;
```

### Step 5: Verify
```sql
SELECT event_id, event_category_id, payment_status
FROM registrations
WHERE email = 'user@example.com'
ORDER BY created_at DESC
LIMIT 2;
```
Should show: (Old record) and (New record with event_id=5)

### Step 6: Send Ticket Email

Once linked, send ticket:
```bash
php artisan mail:send --model=App\\Models\\Registration --model-id=123
```
Or manually via admin panel if available.

---

## Troubleshooting Admin Issues

### "I can't find which event they registered for"

**Solution:**
1. Email the user: "Hi, welcome back! Which event did you re-register for?"
2. Once they confirm, update registration
3. Send ticket

### "Multiple possible events"

**Solution:**
1. If same person registered for multiple events before:
   - Show them list of events
   - Ask which one they want
2. Create separate registration for each event if needed

### "Event doesn't exist anymore"

**Solution:**
1. Check if event was deleted
2. If event is deleted, registration cannot be linked
3. Inform user: "Sorry, that event is no longer available"
4. Offer refund or alternative event

### "I updated but counter is wrong"

**Solution:**
```sql
-- Manually recalculate and fix
UPDATE events
SET registered = (
    SELECT COUNT(*)
    FROM registrations
    WHERE event_id = events.id 
    AND payment_status = 'paid'
)
WHERE id IN (5, 6, 7);  -- Your event IDs
```

---

## Daily Admin Workflow

### Morning (Check for new re-registrations)
```sql
SELECT COUNT(*) as pending_reregistrations
FROM registrations
WHERE event_id = 0 
AND created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY);
```

### Process Each (Identity → Research → Update → Verify)
1. Get email: `SELECT email FROM registrations WHERE id = X`
2. Search history: `SELECT * FROM registrations WHERE email = '...'`
3. Find event: Identify event_id and event_category_id
4. Update: `UPDATE registrations SET event_id=X, event_category_id=Y WHERE id=Z`
5. Verify: `SELECT * FROM registrations WHERE id = Z`

### Send Tickets
```sql
-- Get all re-registrations that are now linked
SELECT id, email, invoice_number
FROM registrations
WHERE event_id != 0 
AND payment_status = 'paid'
AND ticket_email_sent = false
AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY);
```

Then send ticket email for each.

### End of Day (Status Report)
```sql
SELECT 
    COUNT(CASE WHEN event_id = 0 THEN 1 END) as pending,
    COUNT(CASE WHEN ticket_email_sent = true THEN 1 END) as completed,
    COUNT(*) as total_reregistrations
FROM registrations
WHERE payment_status = 'paid'
AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY);
```

---

## Sample Email to Send (After Linking)

```
Subject: 📧 Tiket Anda Telah Dikirimkan – [Event Name]

Halo [Nama],

Terima kasih telah menyelesaikan registrasi ulang Anda.

✅ Registrasi Anda telah kami verifikasi dan diterima untuk:
   Event: [Event Name]
   Tanggal: [Date]
   Kategori: [Category Name]
   Invoice: [Invoice Number]

📧 Tiket elektronik telah kami kirimkan ke email ini.
   Silakan cek folder Inbox atau Spam.

📲 Jika ada pertanyaan, hubungi kami melalui WhatsApp: [Number]

Terima kasih!
Tim Panitia
```

---

## Quality Checklist

For each re-registration processed:

- [ ] Identified correct event
- [ ] Identified correct category
- [ ] Updated event_id correctly
- [ ] Updated event_category_id correctly
- [ ] Verified no data loss
- [ ] Checked registered counter accuracy
- [ ] Sent ticket email
- [ ] Marked ticket_email_sent = true
- [ ] Documented in admin notes (if available)

---

## SQL Quick Reference Scripts

Save these for repeated use:

### Find all pending
```sql
SELECT id, email, nama_peserta, invoice_number, created_at 
FROM registrations WHERE event_id = 0 AND event_category_id = 0;
```

### Get stats
```sql
SELECT 
  COUNT(*) as total,
  COUNT(CASE WHEN event_id = 0 THEN 1 END) as pending,
  COUNT(CASE WHEN ticket_email_sent THEN 1 END) as tickets_sent
FROM registrations WHERE payment_status = 'paid' AND created_at > DATE_SUB(NOW(), INTERVAL 7 DAY);
```

### Update one
```sql
UPDATE registrations SET event_id = ?, event_category_id = ? WHERE id = ?;
```

### Fix counts
```sql
UPDATE events SET registered = (SELECT COUNT(*) FROM registrations WHERE event_id = events.id AND payment_status = 'paid') WHERE id = ?;
```

---

## Timeline

**Days 1-7:** Users re-registering  
**Day 1-7 (Daily):** Admin links registrations to events  
**Day 1-7:** Tickets sent within 24 hours of linking  
**Day 8:** Tokens expire, process ends  
**Day 8+:** No more re-registrations accepted  

---

## Prevention for Future

To prevent this in future:

1. **Backup registrations** before bulk operations
2. **Create test registrations** before running delete/update queries
3. **Double-check event_id values** before CASCADE DELETE operations
4. **Implement audit log** for registration changes
5. **Require confirmation** for bulk delete operations
6. **Archive instead of delete** whenever possible

---

**Last Updated:** April 9, 2026  
**Admin Guide Version:** 1.0
