# Email Setup Guide - Manthan 2.0

## 📧 PHPMailer Installation Status

**Good News:** PHPMailer is **already installed** in your project! 

The PHPMailer library files are located in the `phpmailer/` folder:
- `phpmailer/PHPMailer.php`
- `phpmailer/SMTP.php`
- `phpmailer/Exception.php`

**You do NOT need to install anything additional.** The files are already there.

---

## ⚙️ Email Configuration Required

However, you **DO need to configure SMTP credentials** for emails to actually send.

### Step 1: Get Brevo (formerly Sendinblue) SMTP Credentials

1. Go to [https://www.brevo.com/](https://www.brevo.com/)
2. Sign up for a free account (free tier allows 300 emails/day)
3. After logging in, go to **SMTP & API** section
4. You'll need:
   - **SMTP Login** (your Brevo account email)
   - **SMTP Key** (generate one in the SMTP settings)

### Step 2: Update Configuration File

Open `includes/config.php` and replace the placeholders:

```php
// Email Configuration - Brevo SMTP
define('SMTP_HOST', 'smtp-relay.brevo.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-brevo-email@example.com');   // ← Replace this
define('SMTP_PASSWORD', 'your-smtp-key-here');              // ← Replace this
define('SMTP_FROM_EMAIL', 'manthan2.smtp@gmail.com');
define('SMTP_FROM_NAME', 'Manthan Team');
define('SMTP_SECURE', 'tls');
```

**Important:** Replace:
- `<BREVO_LOGIN>` with your Brevo account email
- `<BREVO_SMTP_KEY>` with your SMTP key from Brevo

---

## ✅ What Works Without Configuration

### 1. **Direct Email Button** (Works Immediately)
The "Direct Email" button in the mentor dashboard uses `mailto:` links, which:
- ✅ **Works immediately** - no server configuration needed
- Opens the user's default email client (Gmail, Outlook, etc.)
- Pre-fills the recipient email address
- User can compose and send email from their own email account

**This button will work right now, even without SMTP configuration!**

### 2. **Email Logging** (Works Immediately)
All email attempts are logged to the `email_logs` table in your database, showing:
- Recipient email and name
- Subject and message
- Status (sent/failed)
- Error messages (if failed)

You can view these logs in phpMyAdmin.

---

## ❌ What Requires SMTP Configuration

### 1. **Automatic Email Sending**
When you:
- Assign a mentor to a team
- Send notifications via email
- Any automated email from the system

These require SMTP configuration to actually send emails.

**Current Status:**
- ✅ PHPMailer files are present
- ✅ Email logging works
- ❌ Emails won't send until SMTP credentials are configured

---

## 🔍 How to Check Email Status

### View Email Logs in Database

1. Open phpMyAdmin
2. Select `manthan_system` database
3. Open `email_logs` table
4. Check the `status` column:
   - `sent` = Email was sent successfully
   - `failed` = Email failed (check `error_message` column)

### Common Error Messages

- **"PHPMailer files not found"** → PHPMailer files missing (shouldn't happen)
- **"PHPMailer class not found"** → PHPMailer files corrupted
- **"SMTP connect() failed"** → SMTP credentials incorrect or network issue
- **"Authentication failed"** → Wrong SMTP username/password

---

## 🚀 Quick Setup Checklist

- [ ] PHPMailer files exist in `phpmailer/` folder ✅ (Already done)
- [ ] Create Brevo account
- [ ] Get SMTP credentials from Brevo
- [ ] Update `includes/config.php` with credentials
- [ ] Test by assigning a mentor to a team
- [ ] Check `email_logs` table for status

---

## 📝 Alternative: Use Gmail SMTP (For Testing)

If you want to use Gmail instead of Brevo:

1. Enable "Less secure app access" in Gmail (or use App Password)
2. Update `includes/config.php`:

```php
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-gmail@gmail.com');
define('SMTP_PASSWORD', 'your-app-password');  // Use App Password, not regular password
define('SMTP_FROM_EMAIL', 'your-gmail@gmail.com');
define('SMTP_FROM_NAME', 'Manthan Team');
define('SMTP_SECURE', 'tls');
```

**Note:** Gmail has strict security, so you'll need to:
- Enable 2-factor authentication
- Generate an "App Password" for this application

---

## 🎯 Summary

| Feature | Status | Requires Setup? |
|---------|--------|-----------------|
| PHPMailer Library | ✅ Installed | No |
| Direct Email Button | ✅ Works | No |
| Email Logging | ✅ Works | No |
| Automatic Email Sending | ❌ Needs Config | Yes (SMTP credentials) |

**Bottom Line:**
- Direct email button works **right now** ✅
- Automatic emails need SMTP configuration ⚙️
- No additional installation needed ✅
