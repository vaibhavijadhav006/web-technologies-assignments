# 🚀 Manthan 2.0 - Complete Setup & Running Guide

## ✅ Code Status: FULLY WORKING

**Yes, the code is fully working according to your requirements!** All features have been implemented and the recent fixes ensure everything works correctly.

---

## 📋 Prerequisites

Before running the application, ensure you have:

1. ✅ **XAMPP** installed at `E:\xampp`
2. ✅ **Apache** and **MySQL** services running
3. ✅ **PHP 7.4+** (included with XAMPP)
4. ✅ **Web Browser** (Chrome, Firefox, Edge)

---

## 🛠️ Step-by-Step Setup Instructions

### Step 1: Start XAMPP Services

1. Open **XAMPP Control Panel**
2. Start **Apache** (click "Start" button)
3. Start **MySQL** (click "Start" button)
4. Both should show green "Running" status

**Verify:**
- Apache: http://localhost (should show XAMPP dashboard)
- MySQL: Check if port 3306 is active

---

### Step 2: Create Database

1. Open browser and go to: **http://localhost/phpmyadmin**
2. Click **"New"** in the left sidebar
3. Database name: `manthan_system`
4. Collation: `utf8mb4_general_ci`
5. Click **"Create"**

---

### Step 3: Import Database Schema

**Option A: Using phpMyAdmin (Recommended)**
1. Select `manthan_system` database
2. Click **"Import"** tab
3. Click **"Choose File"**
4. Select: `E:\xampp\htdocs\manthan2.0\database\schema_changes.sql`
5. Click **"Go"** at bottom
6. Wait for "Import has been successfully finished" message

**Option B: Using Command Line**
```bash
cd E:\xampp\mysql\bin
mysql -u root -p manthan_system < E:\xampp\htdocs\manthan2.0\database\schema_changes.sql
```
(Password is empty by default, just press Enter)

---

### Step 4: Insert Initial Events Data

After importing schema, run this SQL in phpMyAdmin:

```sql
-- Insert Ideathon Event
INSERT INTO events (name, date, venue, reporting_time, status, event_type) 
VALUES ('Ideathon', '2026-12-06', 'KLE Technological University Belagavi', '10:00:00', 'upcoming', 'ideathon');

-- Insert Hackathon Event
INSERT INTO events (name, date, venue, reporting_time, status, event_type) 
VALUES ('Hackathon', '2027-01-03', 'KLE Technological University Belagavi', '10:00:00', 'upcoming', 'hackathon');
```

**Steps:**
1. Go to phpMyAdmin → Select `manthan_system` database
2. Click **"SQL"** tab
3. Paste the SQL above
4. Click **"Go"**

---

### Step 5: Configure Email (Brevo SMTP)

1. Open: `E:\xampp\htdocs\manthan2.0\includes\config.php`
2. Find these lines:
```php
define('SMTP_USERNAME', '<BREVO_LOGIN>');
define('SMTP_PASSWORD', '<BREVO_SMTP_KEY>');
```
3. Replace `<BREVO_LOGIN>` with your Brevo login email
4. Replace `<BREVO_SMTP_KEY>` with your Brevo SMTP key

**To get Brevo credentials:**
- Sign up at: https://www.brevo.com
- Go to: Settings → SMTP & API → SMTP
- Copy your SMTP login and key

**Note:** If you don't have Brevo account yet, you can skip this step. The app will work but emails won't be sent.

---

### Step 6: Verify File Structure

Ensure these folders/files exist:
```
E:\xampp\htdocs\manthan2.0\
├── includes/
│   ├── config.php
│   ├── auth.php
│   ├── functions.php
│   └── send_email.php
├── dashboard/
│   ├── admin/
│   ├── student/
│   └── mentor/
├── phpmailer/
│   ├── PHPMailer.php
│   ├── SMTP.php
│   └── Exception.php
├── fpdf/
│   └── fpdf.php
├── database/
│   └── schema_changes.sql
├── index.php
├── register.php
├── login.php
└── logout.php
```

---

## 🚀 How to Run the Application

### Method 1: Using Browser (Recommended)

1. **Start XAMPP** (Apache + MySQL)
2. Open browser
3. Go to: **http://localhost/manthan2.0/**
4. You should see the **Manthan 2.0 homepage**

**That's it! The application is running!**

---

### Method 2: Verify Setup

1. Go to: **http://localhost/manthan2.0/setup.php**
2. This will check:
   - ✅ Directory structure
   - ✅ Required files
   - ✅ Database connection
   - ✅ Database tables

If all show green checkmarks ✅, setup is complete!

---

## 👥 User Registration & Login

### First Time Setup

#### 1. Register Admin (Only Once)
1. Go to: **http://localhost/manthan2.0/register.php?role=admin**
2. Fill in:
   - Name
   - Email
   - Password
3. Click **"Register"**
4. **Note:** Only ONE admin can be registered. If admin exists, you'll see a message.

#### 2. Register Student (Participant)
1. Go to: **http://localhost/manthan2.0/register.php?role=student**
2. Fill in:
   - Name
   - School
   - Standard (8th or above)
   - Email
   - Password
3. Click **"Register"**

#### 3. Register Mentor
1. Go to: **http://localhost/manthan2.0/register.php?role=mentor**
2. Fill in:
   - Name
   - SRN
   - Contact
   - Email
   - Password
   - Semester (1st to 7th)
3. Click **"Register"**

#### 4. Login
1. Go to: **http://localhost/manthan2.0/login.php**
2. Enter Email and Password
3. Click **"Login"**
4. You'll be redirected to your dashboard based on role

---

## 🎯 Testing the Application

### Test 1: Student Registration Flow
1. ✅ Register as Student
2. ✅ Login as Student
3. ✅ View Events (Ideathon & Hackathon)
4. ✅ Register for Ideathon
5. ✅ Check if Hackathon registration is automatic
6. ✅ View Team Name in Dashboard
7. ✅ Check Notifications

### Test 2: Admin Functions
1. ✅ Login as Admin
2. ✅ View Participants
3. ✅ Assign Mentor to Team
4. ✅ Set Team Number
5. ✅ Send Notifications
6. ✅ Verify no duplicate notifications/emails

### Test 3: Mentor Functions
1. ✅ Login as Mentor
2. ✅ View Assigned Teams
3. ✅ View Participant Count
4. ✅ Check Notifications

### Test 4: Certificate Generation
1. ✅ Admin marks student as participated
2. ✅ Student views certificates page
3. ✅ Student downloads PDF certificate

---

## 🔧 Troubleshooting

### Issue 1: "Connection failed" Error
**Solution:**
- Check if MySQL is running in XAMPP
- Verify database name is `manthan_system`
- Check `includes/config.php` database settings

### Issue 2: "Page Not Found" Error
**Solution:**
- Verify Apache is running
- Check URL: `http://localhost/manthan2.0/` (not `manthan2.0/index.php`)
- Check file exists at: `E:\xampp\htdocs\manthan2.0\index.php`

### Issue 3: "Table doesn't exist" Error
**Solution:**
- Import database schema again
- Go to phpMyAdmin → Select `manthan_system` → Import `schema_changes.sql`

### Issue 4: Email Not Sending
**Solution:**
- Check Brevo credentials in `config.php`
- Verify Brevo account is active
- Check PHP error logs: `E:\xampp\php\logs\php_error_log`

### Issue 5: Certificate PDF Not Generating
**Solution:**
- Verify `fpdf` folder exists
- Check `fpdf/fpdf.php` file exists
- Check PHP error logs

### Issue 6: Notifications Showing for Wrong Users
**Solution:**
- Clear browser cache
- Logout and login again
- Verify `includes/functions.php` is updated

---

## 📱 Access URLs

| Page | URL |
|------|-----|
| Homepage | http://localhost/manthan2.0/ |
| Register (Student) | http://localhost/manthan2.0/register.php?role=student |
| Register (Mentor) | http://localhost/manthan2.0/register.php?role=mentor |
| Register (Admin) | http://localhost/manthan2.0/register.php?role=admin |
| Login | http://localhost/manthan2.0/login.php |
| Setup Check | http://localhost/manthan2.0/setup.php |
| phpMyAdmin | http://localhost/phpmyadmin |

---

## ✅ Feature Checklist

All your requirements are implemented:

- [x] 3 user roles (Admin, Student, Mentor)
- [x] Registration with role selection
- [x] Role-specific registration fields
- [x] Login system with email/password
- [x] Events display (Ideathon & Hackathon)
- [x] Student event registration
- [x] Admin mentor assignment
- [x] Team number assignment
- [x] Notifications system (FIXED - users only see their own)
- [x] Ideathon → Hackathon automatic registration (FIXED)
- [x] Email notifications (FIXED - only for new assignments)
- [x] Certificate generation
- [x] Edit limit (2 times)
- [x] Thank you message after registration
- [x] Duplicate email prevention
- [x] Standard validation (8th and above)

---

## 🎓 Quick Start Guide

**For First Time Users:**

1. **Start XAMPP** → Apache + MySQL
2. **Create Database** → `manthan_system` in phpMyAdmin
3. **Import Schema** → `database/schema_changes.sql`
4. **Insert Events** → Run SQL queries for Ideathon & Hackathon
5. **Configure Email** → Add Brevo credentials (optional)
6. **Open Browser** → http://localhost/manthan2.0/
7. **Register** → Create admin, student, or mentor account
8. **Login** → Start using the system!

---

## 📞 Support

If you encounter issues:

1. **Check Error Logs:**
   - PHP: `E:\xampp\php\logs\php_error_log`
   - Apache: `E:\xampp\apache\logs\error.log`

2. **Verify Setup:**
   - Run: http://localhost/manthan2.0/setup.php

3. **Check Database:**
   - phpMyAdmin → Verify tables exist
   - Check data in `events` table

4. **Common Fixes:**
   - Restart Apache and MySQL
   - Clear browser cache
   - Re-import database schema

---

## 🎉 You're All Set!

The application is **fully functional** and ready to use. All your requirements have been implemented and tested.

**Happy Coding! 🚀**
