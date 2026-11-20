# College LMS

A full-stack mini-project Learning Management System built with **PHP + MySQL + Bootstrap 5 + vanilla JavaScript**.

## Roles
- Student
- Teacher
- Admin

Authentication is role-aware and automatic: users use the same login form, and the backend detects the stored role from MySQL.

## Main functionality
- Authentication and sessions
- Smart role-based navigation and authorization
- Course catalog and enrollment
- Course modules and lectures
- Lesson completion and progress tracking
- Assignments and file submissions
- Assignment grading and feedback
- MCQ quizzes with automatic scoring
- Quiz results
- Announcements and notifications
- Student, teacher and admin dashboards
- Teacher course/content management
- Admin user, course, category and enrollment management
- Profile and password change
- Password reset flow
- Responsive Bootstrap UI

## Architecture

```text
LMS/
├── admin/
├── teacher/
├── student/
├── auth/
├── includes/
├── assets/
├── uploads/
├── docs/
├── index.php
├── courses.php
├── course-details.php
├── database.sql
├── setup.md
└── readme.md
```

## Start
See [setup.md](setup.md) for installation and demo credentials.

## Page map

### Public
- `index.php`
- `courses.php`
- `course-details.php`

### Authentication
- `auth/login.php`
- `auth/register.php`
- `auth/forgot-password.php`
- `auth/reset-password.php`
- `auth/logout.php`

### Student
- `student/dashboard.php`
- `student/courses.php`
- `student/learning.php`
- `student/assignments.php`
- `student/assignment-details.php`
- `student/quizzes.php`
- `student/quiz-attempt.php`
- `student/results.php`
- `student/announcements.php`
- `student/profile.php`
- `student/certificate.php`

### Teacher
- `teacher/dashboard.php`
- `teacher/courses.php`
- `teacher/course-form.php`
- `teacher/course-content.php`
- `teacher/assignments.php`
- `teacher/submissions.php`
- `teacher/quizzes.php`
- `teacher/quiz-builder.php`
- `teacher/students.php`
- `teacher/announcements.php`
- `teacher/profile.php`

### Admin
- `admin/dashboard.php`
- `admin/students.php`
- `admin/teachers.php`
- `admin/courses.php`
- `admin/categories.php`
- `admin/enrollments.php`
- `admin/announcements.php`
- `admin/reports.php`
- `admin/profile.php`
