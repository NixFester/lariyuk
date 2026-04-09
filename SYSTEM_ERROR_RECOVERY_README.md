# SYSTEM ERROR RECOVERY - Apology Email & Re-Registration System

## Overview

This system allows you to send apology emails to users whose registrations were deleted due to a system error, while preventing duplicate event registration counting and limiting re-registration to once per user.

## Key Features

✅ **One-Time Re-registration Links** - Each user gets a unique token valid for 7 days  
✅ **No Duplicate Counts** - Registered counter in events table is NOT incremented  
✅ **Verified Payment** - Users don't need to pay again (payment already verified)  
✅ **Bilingual Support** - Email template in Bahasa Indonesia  
✅ **cPanel Compatible** - Easy to send via cPanel Mail Marketing  

---

## Files Created

### Database
- `database/migrations/2026_04_09_000000_create_apology_tokens_table.php`

### Models
- `app/Models/ApologyToken.php` - Token management model

### Controllers
- Updated `app/Http/Controllers/RegistrationController.php` with:
  - `showReregister()` - Display re-registration form
  - `storeReregister()` - Process re-registration submission
  - `reregisterSuccess()` - Show success page

### Views
- `resources/views/checkout/reregister.blade.php` - Re-registration form
- `resources/views/checkout/reregister-success.blade.php` - Success message
- `resources/views/emails/apology.blade.php` - Email template

### Mailable
- `app/Mail/ApologyEmail.php` - Email class

### Routes
- Added in `routes/web.php`:
  - `GET /checkout/reregister/{token}` - Show form
  - `POST /checkout/reregister/{token}` - Submit form
  - `GET /checkout/reregister-success/{invoice}` - Show success

### Documentation
- `APOLOGY_EMAIL_TEMPLATE.md` - HTML email template & cPanel instructions
- `generate_apology_tokens.sh` - Bash script for generating tokens

---

## Setup Instructions

### Step 1: Run Database Migration

```bash
php artisan migrate
```

This creates the `apology_tokens` table.

### Step 2: Generate Tokens for All Emails

Option A - Using Tinker (for all emails at once):

```bash
php artisan tinker
```

Then paste this code:

```php
use App\Models\ApologyToken;

$emailsFile = base_path('emails.txt');
$emails = array_filter(array_map('trim', file($emailsFile)));

$links = [];
foreach ($emails as $email) {
    $token = \Str::random(32);
    ApologyToken::create([
        'email' => $email,
        'token' => $token,
        'expires_at' => now()->addDays(7),
    ]);
    $links[] = [
        'email' => $email,
        'token' => $token,
        'url' => route('checkout.reregister', $token),
    ];
}

// Save to CSV or review
foreach ($links as $link) {
    echo $link['email'] . " | " . $link['url'] . "\n";
}
```

Option B - Using a single command (create Laravel Command):

```bash
php artisan make:command GenerateApologyTokens
```

Add this to `app/Console/Commands/GenerateApologyTokens.php`:

```php
<?php
namespace App\Console\Commands;

use App\Models\ApologyToken;
use Illuminate\Console\Command;

class GenerateApologyTokens extends Command
{
    protected $signature = 'apology:generate-tokens';
    protected $description = 'Generate apology tokens for all emails in emails.txt';

    public function handle()
    {
        $emailsFile = base_path('emails.txt');
        $emails = array_filter(array_map('trim', file($emailsFile)));

        $this->info("Generating tokens for " . count($emails) . " emails...\n");

        $tokens = [];
        foreach ($emails as $email) {
            $token = \Str::random(32);
            ApologyToken::create([
                'email' => $email,
                'token' => $token,
                'expires_at' => now()->addDays(7),
            ]);
            $tokens[$email] = $token;
            $this->info("[✓] $email");
        }

        // Save token mapping for reference
        file_put_contents(
            storage_path('tokens_mapping.txt'),
            "EMAIL | TOKEN | URL\n" .
            str_repeat("-", 100) . "\n" .
            collect($tokens)->map(fn($token, $email) => 
                "$email | $token | " . route('checkout.reregister', $token)
            )->join("\n")
        );

        $this->info("\n✅ All tokens generated!");
        $this->info("Token mapping saved to: storage/tokens_mapping.txt");
    }
}
```

Then run:

```bash
php artisan apology:generate-tokens
```

### Step 3: Prepare Email Content

Go to `APOLOGY_EMAIL_TEMPLATE.md` and copy the HTML template.

### Step 4: Send via cPanel

1. Open cPanel > Email Marketing (or Mail Marketing)
2. Create new mailing list or use existing one
3. Add all emails from `emails.txt`
4. Select HTML template option
5. Paste the HTML email template
6. Replace placeholders:
   - `{{REREGISTER_URL}}` → Individual token URL for each recipient
   - `[Nomor WhatsApp Anda]` → Your WhatsApp number
   - `[Email Support Anda]` → Your support email
   - `[Jam Operasional Anda]` → Your operating hours

**Note:** cPanel's Email Marketing may not support individual URLs per recipient. Alternative options:
- Use `Forwarders` + bulk mailing
- Use third-party service like Mailchimp/SendGrid with merge tags
- Send via Laravel command: `php artisan mail:send`

### Step 5: Users Click Link & Re-register

1. User receives email with unique link
2. Clicks link → sees `reregister.blade.php`
3. Fills in their data
4. Submits form
5. New registration created with:
   - Status: `paid` (already verified)
   - `registered` counter: NOT incremented
   - Token marked as `used`
6. User sees success page
7. Admin gets notified to link registration to correct event

---

## How It Works

### User Flow

```
User receives email with unique token
        ↓
User clicks link: /checkout/reregister/{token}
        ↓
Show re-registration form (email field read-only)
        ↓
User fills data and submits
        ↓
Validate token (must be valid, not expired, not used)
        ↓
Create new Registration record
        ↓
Mark token as USED
        ↓
Show success page
        ↓
Admin receives notification to link to correct event
```

### Database Structure

**apology_tokens table:**
```
id          | bigint(20)
email       | varchar(255)
token       | varchar(255) [UNIQUE]
used        | boolean [default: false]
used_at     | timestamp [nullable]
expires_at  | timestamp [nullable]
created_at  | timestamp
updated_at  | timestamp
```

**registrations table (modified):**
```
... existing fields ...
payment_verified_at | timestamp [for re-registered users]
```

---

## Important Notes

### 👥 Admin Follow-up Required

When users re-register via the apology link, their registration is created with:
- `event_id` = 0 (NEEDS TO BE UPDATED)
- `event_category_id` = 0 (NEEDS TO BE UPDATED)

**Admin must:**

1. Check registrations with `event_id = 0` in admin panel
2. Look up in payment records which event they originally registered for
3. Update `event_id` and `event_category_id` fields
4. Ensure `registered` counter is correct (NOT double-counted)

OR create a form in admin panel to help with this.

### 🔒 Security

- Tokens are one-time use only
- Tokens expire after 7 days
- Email field is read-only to prevent account takeover
- Payment already verified (no new payment required)

### 📊 Verification Checklist

- [ ] Migration ran successfully: `php artisan migrate`
- [ ] Routes added: `route('checkout.reregister')`
- [ ] Email template sent to all recipients
- [ ] Started receiving re-registration submissions
- [ ] Admin updating registrations with correct event data
- [ ] Registered counters are accurate

---

## Troubleshooting

### "Link expired or already used"

**Cause:** Token was already used or expired (7+ days)

**Solution:** Generate new token and resend email

### User's email doesn't show up

**Cause:** Email might not be in `emails.txt` or typo

**Solution:** 
- Verify email in `emails.txt`
- Check `apology_tokens` table: `SELECT * FROM apology_tokens WHERE email='user@example.com'`
- If missing, generate manually

### Wrong event linked

**Cause:** Admin didn't update `event_id` correctly

**Solution:**
- Cross-reference payment records
- Update via admin panel or database
- Check `registered` counter is not double-counted

### Email sending failed

**Cause:** cPanel mail settings or SMTP issues

**Solution:**
- Test cPanel email first
- Check spam folder
- Verify DKIM/SPF records
- Use Laravel testing: `MAIL_MAILER=log`

---

## Testing

### Test Token Generation

```php
php artisan tinker

use App\Models\ApologyToken;
$token = ApologyToken::create([
    'email' => 'test@example.com',
    'token' => 'test_token_123',
    'expires_at' => now()->addDays(7),
]);
echo route('checkout.reregister', $token->token);

// Visit URL in browser
```

### Test Email Sending

```php
Mail::to('test@example.com')->send(new ApologyEmail(
    'test@example.com',
    'test_token_123'
));
```

### Test Re-registration Flow

1. Visit: `http://localhost/checkout/reregister/{token}`
2. Fill form
3. Submit
4. Check success page
5. Verify token marked as used: `SELECT * FROM apology_tokens WHERE token='...' AND used=true`

---

## Configuration

### Email Validity Period

Modify in token generation (default: 7 days):

```php
ApologyToken::create([
    ...
    'expires_at' => now()->addDays(7),  // ← Change to 14, 30, etc.
]);
```

### Email Content

Edit template in:
- `resources/views/emails/apology.blade.php` (Laravel template)
- `APOLOGY_EMAIL_TEMPLATE.md` (HTML for cPanel)

### Field Restrictions

To restrict more fields (make read-only) in re-registration form, edit:
- `resources/views/checkout/reregister.blade.php`

Example: To make phone read-only:
```blade
<input type="tel" name="phone" value="{{ $email }}" readonly class="...">
```

---

## Files Reference

| File | Purpose |
|------|---------|
| `apology_tokens table` | Store token data |
| `ApologyToken model` | Query & manage tokens |
| `reregister.blade.php` | Form for re-registration |
| `reregister-success.blade.php` | Success page |
| `apology.blade.php` | Email template |
| `APOLOGY_EMAIL_TEMPLATE.md` | cPanel instructions |
| `RegistrationController` | Handle re-reg logic |
| `routes/web.php` | Re-reg route handlers |

---

## Support

For issues or questions, refer to:
1. This README
2. `APOLOGY_EMAIL_TEMPLATE.md`
3. Controller methods in `RegistrationController.php`
4. Email template in `resources/views/emails/apology.blade.php`

---

**Version:** 1.0  
**Created:** April 9, 2026  
**System:** Laravel 13.2.0
