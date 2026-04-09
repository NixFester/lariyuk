# LOCAL EMAIL SENDING WORKFLOW (No cPanel SSH needed!)

## 🎯 New Simple Approach

Instead of using cPanel Email Marketing, just use the admin panel in your app to send emails!

---

## Step 1: Generate Tokens Locally (5 minutes)

```bash
# On your local machine or laptop
cd d:\kerja\lariyuk

# Generate all tokens from emails.txt
php artisan apology:generate-tokens
```

**Output:**
```
✅ Token generation complete!
Success: 150
Failed: 0
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📋 Sample Re-registration Links:
📧 user1@gmail.com
   🔗 http://localhost/checkout/reregister/abc123...
... and 147 more

💾 Full mapping saved to: storage/apology_tokens_mapping.txt
```

**What was created:**
- ✅ All tokens in database
- ✅ Mapping file with all URLs

---

## Step 2: Test Locally (Optional but Recommended!)

```bash
# Start local dev server
php artisan serve

# In browser: http://localhost:8000
```

1. Go to: **Admin Dashboard → Email Permohonan Maaf**
2. Click "Kirim 150 Email" button
3. Watch emails send to console (or check logs)
4. Try clicking re-registration link from test email

---

## Step 3: Upload to cPanel (2 minutes)

**Option A: Using File Manager**
1. FTP/File Manager upload all files (as usual)
2. Run migration on server: 
   ```bash
   php artisan migrate --force
   ```
   OR through cPanel File Manager > PHP Tools > SSH and run command

**Option B: Using Git**
```bash
git add .
git commit -m "Add apology email system"
git push origin main
# Then deploy as usual
```

---

## Step 4: Send Emails from cPanel Admin Panel (2 minutes)

1. Log into cPanel domain: `https://yourdomain.com/admin/login`
2. Go to: **Admin Dashboard → Email Permohonan Maaf** (or in sidebar)
3. Click **📬 Kirim 150 Email**
4. Confirm dialog "Kirim 150 email permohonan maaf?"
5. Wait for "✅ Email Terkirim!" message
6. Done!

**What happens:**
- Laravel sends each email one-by-one
- All emails go out instantly
- Admin sees success/fail counts
- All logged to `storage/logs/laravel.log`

---

## What THIS Approach Has

✅ **No SSH needed** - Just use web interface  
✅ **Real-time feedback** - See success count  
✅ **Error handling** - Failed emails shown  
✅ **Individual test sending** - Send to one person first  
✅ **Resend capability** - If someone didn't get it, click "Send Test" button  
✅ **Token regeneration** - Auto-regen expired tokens  
✅ **Token tracking** - See which emails already got links  

---

## 🎯 Complete Timeline

### Day 1 (Local)
- [ ] Run: `php artisan apology:generate-tokens`
- [ ] Check local: `php artisan serve`
- [ ] Test admin panel: `http://localhost:8000/admin`
- [ ] Test sending 1-2 emails

### Day 2 (Upload & Deploy)
- [ ] Upload files to cPanel
- [ ] Run: `php artisan migrate` (if not already run)
- [ ] Log into cPanel: `https://yourdomain.com/admin`
- [ ] Go to: **Admin → Email Permohonan Maaf**
- [ ] Click "Kirim 150 Email"
- [ ] ✅ Done!

### Day 3-8 (Monitor)
- [ ] Users get emails
- [ ] Users click links and re-register
- [ ] Admin links registrations to correct events

---

## 📧 Email Configuration

Make sure `.env` has correct mail settings:

```env
# Laravel Mail (local testing)
MAIL_MAILER=log
MAIL_FROM_ADDRESS="noreply@domain.com"
MAIL_FROM_NAME="Panitia Event"

# When deploying to cPanel, change to:
MAIL_MAILER=smtp
MAIL_HOST=smtp.yourhostingprovider.com
MAIL_PORT=587
MAIL_USERNAME=noreply@yourdomain.com
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
```

**Test email locally:**
```bash
php artisan tinker

Mail::to('test@example.com')->send(
  new App\Mail\ApologyEmail('test@example.com', 'test_token_123')
);
```

Should print to `storage/logs/laravel.log`

---

## 🔥 If Email Doesn't Send from cPanel

**Check 1: Mail logs**
```bash
tail -f storage/logs/laravel.log | grep -i mail
```

**Check 2: SMTP credentials**
- Check `.env` file has correct SMTP settings
- Test with: `telnet smtp.provider.com 587`

**Check 3: From address**
- Make sure `MAIL_FROM_ADDRESS` is a real cPanel email address
- Example: `noreply@yourdomain.com` (must exist in cPanel)

**Check 4: Manually send one**
1. Go to Admin → Email Permohonan Maaf
2. Find one token
3. Click "Kirim Tes" (Send Test)
4. Check logs for errors

---

## 📊 Admin Panel Features

Location: **http://yourdomain.com/admin/apology-emails**

### Dashboard Shows:
- Total tokens generated
- Pending (not sent yet)
- Already sent
- Expired

### Buttons Available:
- **📬 Kirim 150 Email** - Send all pending emails
- **🔄 Regen Token Kadaluarsa** - Fix expired tokens
- **Kirim Tes** (per token) - Test/resend to specific person

### Table Shows:
- Email address
- Token (first 8 chars)
- Created date
- Expiry date
- Individual send button

---

## All New Files

| File | Purpose |
|------|---------|
| `ApologyEmailController.php` | Admin controller for sending emails |
| `admin/apology-emails/index.blade.php` | Admin dashboard |
| `admin/apology-emails/show.blade.php` | Token details page |
| Routes added to `routes/web.php` | API endpoints |

All integrated with existing auth system!

---

## Troubleshooting

### "Where's the admin email menu?"

In sidebar or dashboard, look for:
```
Admin Dashboard
├── Events
├── Registrations
├── Payment Methods
└── Email Permohonan Maaf ← HERE
```

If missing, check `routes/web.php` - routes were added.

### "Button shows 'Kirim 150 Email' but I only have 30?"

That's just the button label from template. Actual count updates when you refresh. Check stats at top.

### "How do I test one email?"

Find token in table → Click "Kirim Tes" button → Check logs

### "Email sent but recipient didn't get it?"

1. Check spam folder
2. Check logs: `tail storage/logs/laravel.log`
3. Verify SMTP settings in `.env`
4. Check cPanel: is the FROM address a real email account?

### "Token already used, can't send again"

This is expected! Link can only be used once. If needed, regenerate:
- Go to: Admin → Email → Regen Tokens
- Click "🔄 Regen Token Kadaluarsa"
- User gets new link

---

## Local to cPanel Checklist

Before deploying:

- [ ] Run `php artisan apology:generate-tokens` 
- [ ] Test locally: `php artisan serve`
- [ ] Access: `http://localhost:8000/admin/apology-emails`
- [ ] Test 1-2 emails
- [ ] Check logs: `tail storage/logs/laravel.log`

On cPanel:

- [ ] Upload files
- [ ] Run migration if needed
- [ ] Update `.env` with real SMTP credentials
- [ ] Visit: `https://yourdomain.com/admin/apology-emails`
- [ ] Click "📬 Kirim 150 Email"
- [ ] Verify logs for errors: cPanel Terminal or app's tail logs

---

## Done! 🎉

You can now:
✅ Generate tokens locally  
✅ Test locally  
✅ Upload normally  
✅ Send emails from admin panel  
✅ No SSH needed  
✅ No cPanel Email Marketing confusion  

**Just click a button and go!**

---

**Updated:** April 9, 2026  
**Status:** Ready for deployment
