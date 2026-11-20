# College LMS — Setup Guide

## Requirements
- XAMPP (Apache + MySQL)
- PHP 8.1+ recommended
- A modern browser

## 1. Copy the project
Extract the `LMS` folder into:

`C:\xampp\htdocs\LMS`

## 2. Start XAMPP
Start:
- Apache
- MySQL

## 3. Create the database
Open `http://localhost/phpmyadmin`.

Select **Import** and import:

`database.sql`

The script creates the `college_lms` database, tables and demo data.

## 4. Database configuration
The application defaults to:

- Host: `localhost`
- Database: `college_lms`
- User: `root`
- Password: empty

These values can be changed with environment variables or directly in `includes/db.php`.

## 5. Open the LMS
Visit:

`http://localhost/LMS/`

## Demo accounts

| Role | Email | Password |
|---|---|---|
| Admin | admin@college.edu | Admin@123 |
| Teacher | vivek@college.edu | Teacher@123 |
| Student | aarav@college.edu | Student@123 |
| Student | riya@college.edu | Student@123 |

## Smart authentication
There is **one login page** and **one student registration page**.

- Registration automatically creates a `student` account.
- Teachers and admins are created/provisioned by the admin.
- After login, the LMS reads the user's `role` from MySQL and automatically redirects to the correct dashboard.
- Users cannot access other role areas; server-side authorization checks every protected page.

## Password reset
The forgot-password flow is functional without an email server. For a mini-project/demo environment it generates a one-time reset link on the screen. SMTP can be added later without changing the database design.

## Upload directories
Writable upload folders are included:

- `uploads/courses/`
- `uploads/materials/`
- `uploads/assignments/`

## Production notes
Before public deployment:
- Move DB secrets out of source code.
- Enable HTTPS.
- Configure real email delivery for password reset.
- Add stricter file scanning/storage rules.
- Disable detailed PHP errors in production.
