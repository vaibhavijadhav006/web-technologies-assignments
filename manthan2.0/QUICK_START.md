# ⚡ Quick Start Guide - Manthan 2.0

## ✅ YES, THE CODE IS FULLY WORKING!

All your requirements are implemented and working correctly.

---

## 🚀 5-Minute Setup

### Step 1: Start XAMPP
1. Open **XAMPP Control Panel**
2. Click **Start** for **Apache**
3. Click **Start** for **MySQL**

### Step 2: Create Database
1. Open: **http://localhost/phpmyadmin**
2. Click **"New"** → Database name: `manthan_system` → **Create**

### Step 3: Import Database
1. Select `manthan_system` database
2. Click **"Import"** tab
3. Choose file: `E:\xampp\htdocs\manthan2.0\database\schema_changes.sql`
4. Click **"Go"**

### Step 4: Insert Events
1. Still in phpMyAdmin, click **"SQL"** tab
2. Copy and paste this:
```sql
INSERT INTO events (name, date, venue, reporting_time, status, event_type) 
VALUES ('Ideathon', '2026-12-06', 'KLE Technological University Belagavi', '10:00:00', 'upcoming', 'ideathon');

INSERT INTO events (name, date, venue, reporting_time, status, event_type) 
VALUES ('Hackathon', '2027-01-03', 'KLE Technological University Belagavi', '10:00:00', 'upcoming', 'hackathon');
```
3. Click **"Go"**

### Step 5: Run Application
1. Open browser
2. Go to: **http://localhost/manthan2.0/**
3. **Done!** 🎉

---

## 📝 First User Registration

### Register Admin (First Time Only)
- URL: http://localhost/manthan2.0/register.php?role=admin
- Fill: Name, Email, Password
- Click Register

### Register Student
- URL: http://localhost/manthan2.0/register.php?role=student
- Fill: Name, School, Standard (8+), Email, Password
- Click Register

### Register Mentor
- URL: http://localhost/manthan2.0/register.php?role=mentor
- Fill: Name, SRN, Contact, Email, Password, Semester (1-7)
- Click Register

### Login
- URL: http://localhost/manthan2.0/login.php
- Enter Email and Password
- Click Login

---

## ✅ All Features Working

- ✅ 3 User Roles (Admin, Student, Mentor)
- ✅ Registration with role selection
- ✅ Login system
- ✅ Events display (Ideathon & Hackathon)
- ✅ Student event registration
- ✅ Admin mentor assignment
- ✅ Notifications (FIXED - users only see their own)
- ✅ Ideathon → Hackathon automatic registration (FIXED)
- ✅ Email notifications (FIXED)
- ✅ Certificate generation
- ✅ Edit limit (2 times)
- ✅ Team visibility
- ✅ Duplicate prevention

---

## 🔧 Optional: Configure Email

1. Open: `E:\xampp\htdocs\manthan2.0\includes\config.php`
2. Replace:
   - `<BREVO_LOGIN>` with your Brevo email
   - `<BREVO_SMTP_KEY>` with your Brevo SMTP key
3. Save

**Note:** App works without email, but emails won't be sent.

---

## 🆘 Troubleshooting

**"Connection failed"**
→ Check MySQL is running in XAMPP

**"Page not found"**
→ Check Apache is running
→ URL should be: http://localhost/manthan2.0/

**"Table doesn't exist"**
→ Re-import `schema_changes.sql` in phpMyAdmin

---

## 📚 Full Documentation

For detailed setup instructions, see: **SETUP_AND_RUN_GUIDE.md**

---

**That's it! Your application is ready to use! 🎉**
