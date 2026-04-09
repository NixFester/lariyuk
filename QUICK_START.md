# QUICK START CHECKLIST - System Error Recovery

## 🚀 Step-by-Step Implementation

### Phase 1: Database Setup (5 minutes)
- [ ] Run migration: `php artisan migrate`
- [ ] Verify table created: `SELECT * FROM apology_tokens LIMIT 1`

### Phase 2: Generate Tokens (2 minutes)
- [ ] Open terminal/SSH
- [ ] Run: `php artisan tinker`
- [ ] Paste token generation script (see SYSTEM_ERROR_RECOVERY_README.md)
- [ ] Verify tokens in database table

### Phase 3: Send Emails via cPanel (10 minutes)
- [ ] Log into cPanel
- [ ] Go to: Email Accounts → Email Marketing (or similar)
- [ ] Copy HTML template from APOLOGY_EMAIL_TEMPLATE.md
- [ ] Paste into email body
- [ ] Replace placeholder variables:
  - [ ] `{{REREGISTER_URL}}` → Token URLs
  - [ ] `[Nomor WhatsApp Anda]` → Your WhatsApp #
  - [ ] `[Email Support Anda]` → Your support email
  - [ ] `[Jam Operasional Anda]` → Your hours
- [ ] Add all recipients from emails.txt
- [ ] Send test email to yourself first
- [ ] Send to all recipients

### Phase 4: Monitor Re-registrations (Ongoing)
- [ ] Wait for users to receive emails
- [ ] Monitor re-registration submissions
- [ ] Check: `SELECT * FROM registrations WHERE event_id = 0`
- [ ] For each re-registration, update with correct `event_id` and `event_category_id`
- [ ] Send them ticket email after admin verification

### Phase 5: Verification (Daily)
- [ ] Check re-registration success rate: `SELECT COUNT(*) FROM apology_tokens WHERE used=true`
- [ ] Check pending registrations: `SELECT COUNT(*) FROM registrations WHERE event_id = 0`
- [ ] Verify registered counters are accurate per event

---

## 📋 What Users Will See

### Email
```
Subject: 🙏 Permohonan Maaf - Sistem Error Registrasi

Dear User,
We're sorry for the system error that deleted your registration...
[Green Button: Re-register Now]
```

### Re-registration Form
- Pre-filled: Email (read-only)
- Fresh fields: Name, KTP, etc.
- Same format as checkout page
- Clear messaging: "Payment already verified - just complete your data"

### Success Page
```
Registrasi Ulang Berhasil! 🎉

Invoice: INV-20260409-XXXXX
Admin will verify within 24 hours
You'll receive ticket by email
```

---

## 🔑 Key Points to Remember

✅ **Registration Counter NOT Incremented** - Coded in controller  
✅ **Payment Already Verified** - No re-payment needed  
✅ **One-Time Use** - Tokens become invalid after use  
✅ **7-Day Expiry** - Tokens expire after 7 days  
✅ **Email Read-Only** - Prevents account takeover  
✅ **Admin Follow-up** - Must link to correct event

---

## 🛠 Troubleshooting

| Issue | Solution |
|-------|----------|
| Token not generating | Check emails.txt format, no special chars |
| Email not sending | Test cPanel mail settings first |
| User can't see form | Clear browser cache, check URL format |
| Double-counting | Verify registered counter logic in controller |
| Token showing expired | Extend `expires_at` in migration or regenerate |

---

## 📞 Support Files

- `SYSTEM_ERROR_RECOVERY_README.md` → Full documentation
- `APOLOGY_EMAIL_TEMPLATE.md` → Email template & cPanel guide
- `generate_apology_tokens.sh` → Auto token generation script
- `RegistrationController.php` → Re-registration logic
- `reregister.blade.php` → Form view
- `reregister-success.blade.php` → Success page

---

## 📊 SQL Queries for Admin

### Check pending re-registrations
```sql
SELECT id, email, nama_peserta, invoice_number, event_id 
FROM registrations 
WHERE event_id = 0 AND payment_status = 'paid'
ORDER BY created_at DESC;
```

### Update registration with correct event
```sql
UPDATE registrations 
SET event_id = 5, event_category_id = 12 
WHERE id = 123;
```

### Check token status
```sql
SELECT email, token, used, used_at, expires_at 
FROM apology_tokens 
ORDER BY created_at DESC;
```

### Count successfully re-registered users
```sql
SELECT COUNT(*) as reregistered_count
FROM apology_tokens
WHERE used = true;
```

---

## 🎯 Expected Timeline

- Day 1: Send emails
- Day 1-2: Users start re-registering (peak hour)
- Day 2-7: Remaining users re-register
- Day 8+: Link expires, no more re-registrations accepted

---

**Last Updated:** April 9, 2026  
**Status:** Ready for deployment
