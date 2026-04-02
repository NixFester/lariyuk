# IPaymu Integration Implementation Guide

## Overview

The IPaymu payment integration is now fully implemented and ready for testing. The system is **completely reversible** - you can switch between the classic (manual verification) and IPaymu (automatic) payment flows with a single configuration change.

## Current Status ✅

- ✅ IPaymu Service created and tested
- ✅ IPaymuController implemented with webhook support
- ✅ IPaymu pending view UI created
- ✅ Registration model updated with IPaymu fields
- ✅ Routes configured
- ✅ CSRF exemption for webhooks configured
- ✅ Database migration created and applied
- ✅ All PHP files validated for syntax
- ✅ localStorage integration for payment tracking
- ✅ Configuration system ready

## How to Switch Payment Methods

### Use IPaymu (New Flow)
```bash
# In .env, set:
PAYMENT_GATEWAY=ipaymu
```

### Use Classic (Old Flow)
```bash
# In .env, set:
PAYMENT_GATEWAY=classic
```

That's it! Just change the `.env` variable and the next registration will use the selected payment method.

## Files Created/Modified

### New Files Created:
1. **app/Services/IPaymu/IPaymuService.php** - Core payment integration
2. **app/Http/Controllers/IPaymuController.php** - Payment flow controller
3. **resources/views/checkout/ipaymu-pending.blade.php** - Payment UI with polling
4. **config/payment.php** - Payment gateway configuration
5. **database/migrations/2026_04_01_000000_add_ipaymu_fields_to_registrations.php** - DB schema

### Modified Files:
1. **.env** - Added `PAYMENT_GATEWAY=classic`
2. **app/Models/Registration.php** - Added IPaymu fields to fillable and casts
3. **app/Http/Controllers/RegistrationController.php** - Added routing logic
4. **config/services.php** - Added IPaymu service configuration
5. **routes/web.php** - Added IPaymu routes
6. **bootstrap/app.php** - Added CSRF exemption for webhook

## Payment Flows

### Classic Flow (Current/Default)
```
User Registration Form
    ↓
Redirected to checkout.pending
    ↓
Select Payment Method (QRIS/Bank)
    ↓
Confirm via WhatsApp
    ↓
Admin manually verifies payment
    ↓
User receives ticket email
```

### IPaymu Flow (New)
```
User Registration Form
    ↓
Redirected to checkout.ipaymu.initiate
    ↓
IPaymu Pending Page (auto-polling)
    ↓
JavaScript checks payment status every 5 seconds
    ↓
IPaymu API confirms payment
    ↓
System marks registration as paid
    ↓
Ticket email sent automatically
    ↓
User redirected to success page
```

## Key Features

### Automatic Payment Detection
- No admin verification needed for IPaymu
- Instant ticket delivery upon payment confirmation

### localStorage Integration
- Payment data saved for future email sync capabilities
- Tracks device information (user agent, language, timezone)
- Can be extended to sync to email later

### Webhook Support
- Real-time payment notifications from IPaymu
- Signature verification for security
- CSRF exemption configured

### 30-Minute Timeout
- Auto-countdown timer on payment page
- Session expires after 30 minutes
- User can cancel and re-register

### Status Polling
- JavaScript polls `/checkout/ipaymu/check-status` every 5 seconds
- Clean UI with loading states
- Immediate feedback on payment status

## Testing Checklist

### Prerequisites
- Start dev server: `php artisan serve`
- Ensure `.env` has IPaymu credentials (already configured):
  - `IPAYMU_VA=1179001476652656`
  - `IPAYMU_API_KEY=5EE51567-B651-4BCF-8D82-BE4E802A3122`
  - `IPAYMU_BASE_URL=https://sandbox.ipaymu.com/api/v2`

### Test IPaymu Flow
1. Set `.env` to `PAYMENT_GATEWAY=ipaymu`
2. Visit home page and register for an event
3. Fill out registration form completely
4. Submit form
5. Should be redirected to IPaymu pending page
6. See countdown timer and polling message
7. (Manual testing) Complete payment in IPaymu sandbox
8. Should auto-detect payment and redirect to success page
9. Check email for ticket

### Test Classic Flow
1. Set `.env` to `PAYMENT_GATEWAY=classic`
2. Register for event again
3. Should be redirected to classic pending page
4. See payment method selection
5. Choose payment method
6. Confirm via WhatsApp
7. Wait for admin verification

### Test Reversibility
1. Register with IPaymu (`PAYMENT_GATEWAY=ipaymu`)
2. Change `.env` to `PAYMENT_GATEWAY=classic`
3. Register again with classic flow
4. Both payments should coexist in database
5. Previous registration data preserved

## Database Schema

New columns added to `registrations` table:
- `ipaymu_transaction_id` (VARCHAR 255, NULL) - IPaymu transaction reference
- `ipaymu_paid_at` (TIMESTAMP, NULL) - When IPaymu confirmed payment

Existing columns used:
- `payment_status` - 'pending' or 'paid'
- `payment_verified_at` - When payment confirmed
- `ticket_email_sent` - Whether ticket was sent

## API Endpoints

### Registration Flow
- `POST /checkout/register` - Create registration
- Routes to either IPaymu or classic based on config

### IPaymu Specific Routes
- `GET /checkout/ipaymu/initiate/{invoice}` - Start IPaymu payment
- `GET /checkout/ipaymu/check-status?invoice=INV-XXX` - Check payment (AJAX)
- `POST /checkout/ipaymu/webhook` - Receive webhooks (CSRF exempt)

### Common Routes
- `GET /checkout/pending/{invoice}` - Classic pending page
- `GET /checkout/success/{invoice}` - Success/ticket page
- `POST /checkout/cancel/{invoice}` - Cancel registration

## localStorage Data Structure

Payment data saved as:
```javascript
{
    invoice: "INV-20260401-XXXXXX",
    email: "user@example.com",
    amount: 115000,
    timestamp: "2026-04-01T10:30:00Z",
    device: {
        userAgent: "Mozilla/5.0...",
        language: "id-ID",
        timezone: "Asia/Jakarta"
    },
    status: "paid" // Added after payment
}
```

Keys used:
- `lariyuk_payment_{invoice}` - Individual payment data
- `lariyuk_pending_payments` - Array of all pending payments

## Error Handling

### IPaymu API Errors
- Connection failures fall back to manual entry
- Errors logged to `storage/logs/laravel.log`
- User sees friendly error messages

### Webhook Verification
- Signature validation ensures authenticity
- Invalid signatures logged as warnings
- Protects against unauthorized payment confirms

## Future Enhancements

### Email Sync (Ready to Implement)
- Track payment data in localStorage
- Send summary to user email
- Sync with backend for records

### Multiple Payment Methods
- Can extend to support other gateways
- Same reversible pattern applies

### Advanced Reporting
- Payment method analytics
- Conversion tracking
- Revenue reports by gateway

## Troubleshooting

### "Payment not detected"
1. Check `.env` PAYMENT_GATEWAY setting
2. Verify IPaymu credentials in `.env`
3. Check `storage/logs/laravel.log` for API errors
4. Verify webhook URL accessible from IPaymu

### "Redirect loop"
1. Clear browser cache
2. Check if payment_status changed without page update
3. Verify POST requests completing successfully

### "Token errors"
1. CSRF exemption for webhook should be configured
2. Check `bootstrap/app.php` has correct path

### "Missing payment data"
- Check .env `PAYMENT_GATEWAY` value
- Verify migration ran: `php artisan migrate:status`
- Check database columns exist: `php artisan tinker` → `DB::table('registrations')->first()`

## Rollback Plan

If critical issues arise:

1. Immediate: Change `.env` to `PAYMENT_GATEWAY=classic`
   - All new registrations use old flow immediately
   - Existing IPaymu data preserved

2. Full revert: `php artisan migrate:rollback --step=1`
   - Removes ipaymu_transaction_id and ipaymu_paid_at columns
   - Can be re-applied anytime

3. Safe: Keep both systems and test thoroughly before switching

## Next Steps

1. Start dev server: `php artisan serve`
2. Test IPaymu flow with sandbox credentials
3. Monitor logs in dev for any issues
4. When ready, consider production deployment
5. Implement email sync feature if needed

## Support

For issues:
1. Check logs: `storage/logs/laravel.log`
2. Verify .env variables
3. Test API calls manually using Postman
4. Check IPaymu dashboard for transaction records
