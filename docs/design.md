# StudyHub LMS — UI/UX Design Document

## 1. Project Overview

A responsive Learning Management System (LMS) for a college mini project, built with PHP, MySQL, Bootstrap, HTML, CSS, and JavaScript.

The system has three primary user roles:

- **Admin** — manages users, courses, categories, announcements, and system-level data.
- **Teacher** — manages their courses, lectures, materials, assignments, quizzes, submissions, and marks.
- **Student** — discovers courses, enrolls, studies lessons, submits assignments, takes quizzes, and tracks progress.

## 2. UI Goals

The interface should feel like a modern college portal rather than a generic CRUD application.

### Design principles

1. **Simple navigation** — users should know where they are and what to do next.
2. **Role-based UI** — each role gets a dashboard and navigation relevant to its work.
3. **Responsive first** — desktop, tablet, and mobile layouts must all remain usable.
4. **Reusable components** — cards, tables, forms, alerts, modals, badges, and navigation should share a consistent style.
5. **Clear hierarchy** — headings, primary actions, secondary actions, status badges, and content sections should be visually distinct.

## 3. Visual Design System

### 3.1 Color palette

Use a clean academic/productivity style.

- **Primary:** `#2563EB` — main buttons, active links, accents
- **Primary dark:** `#1D4ED8` — hover/active states
- **Success:** `#16A34A` — completed/success states
- **Warning:** `#D97706` — pending/deadline states
- **Danger:** `#DC2626` — delete/errors/overdue states
- **Background:** `#F8FAFC`
- **Surface:** `#FFFFFF`
- **Border:** `#E2E8F0`
- **Text:** `#0F172A`
- **Muted text:** `#64748B`

Do not use too many accent colors. Status colors should be reserved for status communication.

### 3.2 Typography

Use a modern sans-serif font stack. Prefer a web-safe system stack unless an approved web font is added later.

- Page title: 28–32px, semibold/bold
- Section title: 20–24px, semibold
- Card title: 16–18px, semibold
- Body text: 14–16px
- Small/muted text: 12–14px
- Buttons: 14–15px, medium/semibold

### 3.3 Shape and spacing

- Card radius: 12px
- Button radius: 8px
- Input radius: 8px
- Default spacing scale: 4 / 8 / 12 / 16 / 24 / 32px
- Use subtle borders and shadows rather than heavy effects.

## 4. Global Layout

### 4.1 Public pages

```text
┌─────────────────────────────────────────────────────────────┐
│ LOGO     Courses   About   Contact          Login  Register │
├─────────────────────────────────────────────────────────────┤
│                                                             │
│                    PAGE CONTENT                             │
│                                                             │
├─────────────────────────────────────────────────────────────┤
│ Footer: LMS | About | Contact | Privacy | © College         │
└─────────────────────────────────────────────────────────────┘
```

### 4.2 Authenticated dashboard pages

```text
┌───────────────┬─────────────────────────────────────────────┐
│ Logo          │ Topbar                         Profile ▼   │
│               ├─────────────────────────────────────────────┤
│ Dashboard     │                                             │
│ Courses       │              MAIN CONTENT                   │
│ Assignments   │                                             │
│ Quizzes       │                                             │
│ ...           │                                             │
│               │                                             │
│ Logout        │                                             │
└───────────────┴─────────────────────────────────────────────┘
```

Desktop uses a fixed/collapsible sidebar. On mobile, it becomes an offcanvas menu.

## 5. Public Website Pages

### 5.1 Home

Sections:

1. Navigation bar
2. Hero section
3. Search/browse courses CTA
4. Featured courses
5. Key LMS benefits
6. How it works
7. College announcement/notice area
8. Footer

Hero example:

```text
Learn. Practice. Grow.
Your college learning platform.

[ Explore Courses ]   [ Login ]
```

### 5.2 Courses

- Search box
- Category filter
- Teacher filter (optional)
- Course cards
- Pagination

Course card:

```text
┌──────────────────────────┐
│      Course Thumbnail    │
├──────────────────────────┤
│ PHP & MySQL              │
│ Learn backend web dev... │
│ 👤 Dr. Vivek Sharma           │
│ 12 Lessons   ⭐ 4.5      │
│ [ View Course ]          │
└──────────────────────────┘
```

### 5.3 Course Details

- Course image
- Course title and description
- Teacher information
- Lesson count
- Duration
- Category
- Course contents accordion
- Enrollment CTA

### 5.4 Login / Register

Use a centered card with clear labels and validation states.

Login fields:
- Email
- Password
- Remember me
- Forgot password
- Login button

Register fields:
- Full name
- Email
- Password
- Confirm password
- Role (student/teacher if college workflow allows self-selection)

## 6. Student UI

### 6.1 Student Dashboard

Top summary cards:

- Enrolled courses
- Active assignments
- Pending quizzes
- Average progress

Main sections:

- Continue learning
- Upcoming deadlines
- Recent announcements
- Recent quiz results

### 6.2 My Courses

Course cards with progress bars.

```text
PHP & MySQL
██████████████░░░░ 72%
18 / 25 lessons
[ Continue ]
```

### 6.3 Course Learning Page

Desktop layout:

```text
┌──────────────────────┬──────────────────────────────────────┐
│ Course modules       │ Lesson title                         │
│                      │                                      │
│ ✓ Intro              │ Video / PDF / lesson content        │
│ ✓ HTML               │                                      │
│ → CSS                │                                      │
│ ○ PHP                │ [ Mark Complete ]                    │
│ ○ MySQL              │                                      │
│                      │                                      │
└──────────────────────┴──────────────────────────────────────┘
```

### 6.4 Assignments

Show assignment status with badges:

- Not submitted
- Submitted
- Graded
- Overdue

### 6.5 Assignment Submission

- Assignment details
- Deadline
- Instructions
- File upload
- Optional comments
- Submission confirmation

### 6.6 Quiz

Keep the quiz screen distraction-free.

- Progress indicator
- Question number
- Question text
- Radio-button options
- Timer if enabled
- Previous/Next controls
- Submit confirmation

### 6.7 Results

Show:

- Score
- Percentage
- Correct/incorrect count
- Attempt date
- Teacher feedback where applicable

### 6.8 Profile

Profile summary with:

- Profile photo
- Name
- Enrollment number
- Department
- Semester
- Email
- Phone

## 7. Teacher UI

### 7.1 Teacher Dashboard

Summary cards:

- My courses
- Total students
- Pending submissions
- Upcoming deadlines

Main content:

- Course performance
- Recent submissions
- Recent announcements

### 7.2 Course Management

Table columns:

- Course
- Category
- Students
- Status
- Updated
- Actions

Actions:

- View
- Edit
- Manage content
- Publish/Unpublish
- Delete

### 7.3 Course Builder

Use tabs/sections:

- Basic information
- Modules
- Lessons
- Materials
- Assignments
- Quizzes

### 7.4 Submissions

Teacher review page:

```text
Student | Assignment | Submitted | Status | Marks | Action
```

Actions include View, Download, Grade, and Feedback.

## 8. Admin UI

### 8.1 Admin Dashboard

Primary statistics:

- Students
- Teachers
- Courses
- Enrollments

Secondary widgets:

- Recent registrations
- Recently added courses
- Enrollment trend
- Recent announcements

### 8.2 User Management

Separate tabs or filters for Students and Teachers.

Functions:

- Search
- Filter
- Add
- Edit
- View
- Activate/Deactivate
- Delete

### 8.3 Course Management

Admin can view all courses and manage:

- Approval/status
- Category
- Teacher
- Enrollment count
- Visibility

## 9. Reusable Components

Create reusable PHP include components and shared CSS classes.

Recommended components:

- Navbar
- Sidebar
- Topbar
- Footer
- Breadcrumb
- Page header
- Stat card
- Course card
- Progress bar
- Data table
- Search/filter bar
- Form group
- Modal
- Confirmation dialog
- Alert/toast
- Status badge
- Pagination
- Empty state
- Loading state

## 10. UX States

Every important page should account for:

- Loading/processing
- Empty data
- Validation error
- Permission denied
- Success confirmation
- Delete confirmation
- Network/server error

Examples:

```text
No courses found.
Try changing your search or filter.

[ Clear Filters ]
```

## 11. Responsive Behavior

### Desktop

- Full sidebar
- Multi-column cards
- Wide tables
- Two-column learning layout

### Tablet

- Reduced card columns
- Collapsible sidebar
- Condensed tables

### Mobile

- Offcanvas navigation
- Single-column cards
- Stacked forms
- Horizontally scrollable tables where necessary
- Full-width primary buttons

## 12. Accessibility Basics

- Use labels for all form controls.
- Maintain readable contrast.
- Do not rely only on color to communicate status.
- Use semantic headings and buttons.
- Provide visible focus states.
- Add meaningful alt text to informative images.

## 13. UI Page Inventory

### Public

- Home
- Courses
- Course details
- Login
- Register
- Forgot password

### Student

- Dashboard
- My courses
- Course learning
- Assignments
- Assignment details/submission
- Quizzes
- Quiz attempt
- Results
- Announcements
- Profile

### Teacher

- Dashboard
- My courses
- Create/edit course
- Course content
- Assignments
- Submissions/grading
- Quizzes
- Quiz builder
- Students
- Announcements
- Profile

### Admin

- Dashboard
- Students
- Teachers
- Courses
- Categories
- Enrollments
- Announcements
- Reports
- Profile/settings

## 14. UI Build Order

1. Global layout and theme
2. Login/Register
3. Public home and course pages
4. Student dashboard
5. Student learning pages
6. Teacher dashboard and course management
7. Admin dashboard and management tables
8. Assignments and quizzes
9. Progress/results/notifications
10. Responsive and accessibility polish

## 15. Technical UI Direction

- Framework: Bootstrap 5
- Backend: PHP
- Database: MySQL
- JavaScript: Vanilla JavaScript first; add a library only when needed
- Icons: Bootstrap Icons or another consistent icon set
- CSS: One shared stylesheet plus small page-specific additions when necessary

The initial implementation should avoid unnecessary frontend frameworks. The objective is a clean, maintainable PHP + MySQL college LMS with a professional interface.
