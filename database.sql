CREATE DATABASE IF NOT EXISTS college_lms CHARACTER
SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE college_lms;

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS password_resets,
    remember_tokens,
    course_ratings,
    notifications,
    announcements,
    quiz_answers,
    quiz_attempts,
    quiz_options,
    questions,
    quizzes,
    submissions,
    assignments,
    lesson_progress,
    lectures,
    course_modules,
    enrollments,
    courses,
    categories,
    users;

SET FOREIGN_KEY_CHECKS=1;

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
  full_name VARCHAR(120) NOT NULL,
    
  email VARCHAR(190) NOT NULL UNIQUE,
    
  password_hash VARCHAR(255) NOT NULL,
    
  role ENUM('admin',
    'teacher',
    'student') NOT NULL DEFAULT 'student',
    
  status ENUM('active',
    'inactive',
    'pending') NOT NULL DEFAULT 'active',
    
  enrollment_no VARCHAR(50) NULL UNIQUE,
    
  department VARCHAR(120) NULL,
    
  semester TINYINT UNSIGNED NULL,
    
  phone VARCHAR(20) NULL,
    
  bio TEXT NULL,
    
  avatar VARCHAR(255) NULL,
    
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON
UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
  name VARCHAR(100) NOT NULL UNIQUE,
    
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE courses (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
  teacher_id INT UNSIGNED NOT NULL,
    
  category_id INT UNSIGNED NULL,
    
  title VARCHAR(180) NOT NULL,
    
  short_description VARCHAR(255) NULL,
    
  description TEXT NULL,
    
  thumbnail VARCHAR(255) NULL,
    
  level ENUM('Beginner',
    'Intermediate',
    'Advanced') DEFAULT 'Beginner',
    
  duration_hours DECIMAL(5,
    1) DEFAULT 0,
    
  status ENUM('draft',
    'published',
    'pending') DEFAULT 'draft',
    
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON
UPDATE CURRENT_TIMESTAMP,
    
  CONSTRAINT fk_courses_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    
  CONSTRAINT fk_courses_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE
SET NULL,
    
  INDEX idx_courses_status(status),
    INDEX idx_courses_teacher(teacher_id)
) ENGINE=InnoDB;

CREATE TABLE enrollments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
  student_id INT UNSIGNED NOT NULL,
    
  course_id INT UNSIGNED NOT NULL,
    
  progress_percent DECIMAL(5,
    2) DEFAULT 0,
    
  enrolled_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
  UNIQUE KEY uq_enrollment(student_id,
    course_id),
    
  CONSTRAINT fk_enroll_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    
  CONSTRAINT fk_enroll_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE course_modules (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
  course_id INT UNSIGNED NOT NULL,
    
  title VARCHAR(180) NOT NULL,
    
  sort_order INT DEFAULT 0,
    
  CONSTRAINT fk_modules_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE lectures (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
  module_id INT UNSIGNED NOT NULL,
    
  title VARCHAR(180) NOT NULL,
    
  lesson_type ENUM('video',
    'article',
    'pdf',
    'link') DEFAULT 'article',
    
  content TEXT NULL,
    
  file_path VARCHAR(255) NULL,
    
  duration_minutes INT DEFAULT 0,
    
  sort_order INT DEFAULT 0,
    
  CONSTRAINT fk_lectures_module FOREIGN KEY (module_id) REFERENCES course_modules(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE lesson_progress (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
  student_id INT UNSIGNED NOT NULL,
    
  lecture_id INT UNSIGNED NOT NULL,
    
  completed TINYINT(1) DEFAULT 0,
    
  completed_at DATETIME NULL,
    
  UNIQUE KEY uq_lesson_progress(student_id,
    lecture_id),
    
  CONSTRAINT fk_progress_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    
  CONSTRAINT fk_progress_lecture FOREIGN KEY (lecture_id) REFERENCES lectures(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE assignments (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
  course_id INT UNSIGNED NOT NULL,
    
  title VARCHAR(180) NOT NULL,
    
  instructions TEXT NULL,
    
  max_marks INT DEFAULT 100,
    
  deadline DATETIME NULL,
    
  status ENUM('draft',
    'open',
    'closed') DEFAULT 'draft',
    
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
  CONSTRAINT fk_assignments_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE submissions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
  assignment_id INT UNSIGNED NOT NULL,
    
  student_id INT UNSIGNED NOT NULL,
    
  file_path VARCHAR(255) NULL,
    
  comment TEXT NULL,
    
  marks DECIMAL(6,
    2) NULL,
    
  feedback TEXT NULL,
    
  submitted_at TIMESTAMP NULL,
    
  graded_at TIMESTAMP NULL,
    
  status ENUM('submitted',
    'graded',
    'late') DEFAULT 'submitted',
    
  UNIQUE KEY uq_submission(assignment_id,
    student_id),
    
  CONSTRAINT fk_sub_assignment FOREIGN KEY (assignment_id) REFERENCES assignments(id) ON DELETE CASCADE,
    
  CONSTRAINT fk_sub_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE quizzes (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
  course_id INT UNSIGNED NOT NULL,
    
  title VARCHAR(180) NOT NULL,
    
  time_limit_minutes INT DEFAULT 10,
    
  status ENUM('draft',
    'published') DEFAULT 'draft',
    
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
  CONSTRAINT fk_quizzes_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE questions (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
  quiz_id INT UNSIGNED NOT NULL,
    
  question_text TEXT NOT NULL,
    
  marks INT DEFAULT 1,
    
  sort_order INT DEFAULT 0,
    
  CONSTRAINT fk_questions_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE quiz_options (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
  question_id INT UNSIGNED NOT NULL,
    
  option_text VARCHAR(500) NOT NULL,
    
  is_correct TINYINT(1) DEFAULT 0,
    
  CONSTRAINT fk_options_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE quiz_attempts (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
  quiz_id INT UNSIGNED NOT NULL,
    
  student_id INT UNSIGNED NOT NULL,
    
  score DECIMAL(6,
    2) DEFAULT 0,
    
  percentage DECIMAL(5,
    2) DEFAULT 0,
    
  started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
  submitted_at TIMESTAMP NULL,
    
  CONSTRAINT fk_attempt_quiz FOREIGN KEY (quiz_id) REFERENCES quizzes(id) ON DELETE CASCADE,
    
  CONSTRAINT fk_attempt_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    
  INDEX idx_attempt_student(student_id)
) ENGINE=InnoDB;

CREATE TABLE quiz_answers (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
  attempt_id INT UNSIGNED NOT NULL,
    
  question_id INT UNSIGNED NOT NULL,
    
  option_id INT UNSIGNED NULL,
    
  marks_awarded DECIMAL(6,
    2) DEFAULT 0,
    
  CONSTRAINT fk_answer_attempt FOREIGN KEY (attempt_id) REFERENCES quiz_attempts(id) ON DELETE CASCADE,
    
  CONSTRAINT fk_answer_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    
  CONSTRAINT fk_answer_option FOREIGN KEY (option_id) REFERENCES quiz_options(id) ON DELETE
SET NULL
) ENGINE=InnoDB;

CREATE TABLE announcements (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
  author_id INT UNSIGNED NOT NULL,
    
  title VARCHAR(180) NOT NULL,
    
  content TEXT NOT NULL,
    
  audience ENUM('all',
    'students',
    'teachers',
    'course') DEFAULT 'all',
    
  course_id INT UNSIGNED NULL,
    
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
  CONSTRAINT fk_announce_author FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE,
    
  CONSTRAINT fk_announce_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE
SET NULL
) ENGINE=InnoDB;

CREATE TABLE notifications (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
  user_id INT UNSIGNED NOT NULL,
    
  title VARCHAR(180) NOT NULL,
    
  message TEXT NOT NULL,
    
  is_read TINYINT(1) DEFAULT 0,
    
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
  CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
  INDEX idx_notifications_user(user_id,
    is_read)
) ENGINE=InnoDB;

CREATE TABLE course_ratings (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
  course_id INT UNSIGNED NOT NULL,
    
  student_id INT UNSIGNED NOT NULL,
    
  rating TINYINT UNSIGNED NOT NULL,
    
  review VARCHAR(500) NULL,
    
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON
UPDATE CURRENT_TIMESTAMP,
    
  UNIQUE KEY uq_course_rating(course_id,
    student_id),
    
  CONSTRAINT fk_rating_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    
  CONSTRAINT fk_rating_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE remember_tokens (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
  user_id INT UNSIGNED NOT NULL,
    
  token_hash CHAR(64) NOT NULL UNIQUE,
    
  expires_at DATETIME NOT NULL,
    
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
  CONSTRAINT fk_remember_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE password_resets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    
  user_id INT UNSIGNED NOT NULL,
    
  token_hash CHAR(64) NOT NULL UNIQUE,
    
  expires_at DATETIME NOT NULL,
    
  used_at DATETIME NULL,
    
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
  CONSTRAINT fk_reset_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

INSERT INTO users (id,
    full_name,
    email,
    password_hash,
    role,
    status,
    enrollment_no,
    department,
    semester,
    phone,
    bio)
VALUES
(1,
    'LMS Administrator',
    'admin@college.edu',
    '$2y$12$MDjWeYKUxGJBuSnWI2hvKeEexs7krURFkEiHUhAgf24qOIXN1L6Q2',
    'admin',
    'active',
    NULL,
    'Administration',
    NULL,
    '9876500000',
    'College LMS administrator.'),
    
(2,
    'Dr. Vivek Sharma',
    'vivek@college.edu',
    '$2y$12$EdvTT.kxYUEujNthcZ2kxuFRfNV2Nnz39bjU09Vq4HIzsKvVigQpO',
    'teacher',
    'active',
    NULL,
    'Computer Engineering',
    NULL,
    '9876500001',
    'Faculty member and course instructor.'),
    
(3,
    'Aarav Mehta',
    'aarav@college.edu',
    '$2y$12$PAQn6CKZTljuX3uAw3um4udySNuYFv9iaW.DEC5HxaWc7oe79x13W',
    'student',
    'active',
    '24SDSCE01001',
    'Computer Engineering',
    5,
    '9876500002',
    'Student demo account.'),
    
(4,
    'Riya Shah',
    'riya@college.edu',
    '$2y$12$PAQn6CKZTljuX3uAw3um4udySNuYFv9iaW.DEC5HxaWc7oe79x13W',
    'student',
    'active',
    '24SDSCE01014',
    'Computer Engineering',
    5,
    '9876500003',
    'Student demo account.');

INSERT INTO categories (id,
    name)
VALUES
(1,
    'Web Development'),
    (2,
    'Programming'),
    (3,
    'Database'),
    (4,
    'Design'),
    (5,
    'Computer Science');

INSERT INTO courses (id,
    teacher_id,
    category_id,
    title,
    short_description,
    description,
    level,
    duration_hours,
    status)
VALUES
(1,
    2,
    1,
    'PHP & MySQL',
    'Build backend skills with PHP, SQL, forms, CRUD, and sessions.',
    'Learn server-side PHP development, MySQL database design, authentication, CRUD workflows and secure form handling through a practical project.',
    'Beginner',
    18,
    'published'),
    
(2,
    2,
    2,
    'Python Programming',
    'Learn Python fundamentals, functions, files, and practical problem solving.',
    'A practical introduction to Python syntax, data structures, functions, files and problem solving.',
    'Beginner',
    22,
    'published'),
    
(3,
    2,
    3,
    'Database Management',
    'Understand relational databases, SQL queries, normalization, and transactions.',
    'Study SQL, relational design, normalization, joins, transactions and real-world database operations.',
    'Intermediate',
    16,
    'published'),
    
(4,
    2,
    4,
    'UI/UX Fundamentals',
    'Learn user-centered design, layouts, components, and usability.',
    'Explore design principles, wireframes, components, typography, accessibility and prototyping.',
    'Beginner',
    14,
    'published'),
    
(5,
    2,
    5,
    'Data Structures',
    'Master arrays, linked lists, stacks, queues, trees, and algorithms.',
    'Build strong data structure fundamentals and learn to analyze common algorithms.',
    'Intermediate',
    24,
    'draft');

INSERT INTO course_modules (id,
    course_id,
    title,
    sort_order)
VALUES
(1,
    1,
    'PHP Fundamentals',
    1),
    (2,
    1,
    'Forms & MySQL',
    2),
    (3,
    1,
    'Authentication & CRUD',
    3),
    
(4,
    2,
    'Python Basics',
    1),
    (5,
    2,
    'Collections & Functions',
    2),
    
(6,
    3,
    'Relational Concepts',
    1),
    (7,
    3,
    'SQL Queries',
    2),
    
(8,
    4,
    'Design Foundations',
    1),
    (9,
    4,
    'Components & Prototyping',
    2);

INSERT INTO lectures (id,
    module_id,
    title,
    lesson_type,
    content,
    duration_minutes,
    sort_order)
VALUES
(1,
    1,
    'Introduction to PHP',
    'article',
    'PHP is a server-side language commonly used to generate dynamic web pages.',
    18,
    1),
    
(2,
    1,
    'Variables and Data Types',
    'article',
    'Strings, integers, floats, booleans, arrays and null
values in PHP.',
    22,
    2),
    
(3,
    1,
    'Control Flow',
    'article',
    'Conditions and loops using if, switch, for, while and foreach.',
    20,
    3),
    
(4,
    2,
    'HTML Forms with PHP',
    'article',
    'Receive and validate form data with GET and POST.',
    24,
    1),
    
(5,
    2,
    'Connecting PHP to MySQL',
    'article',
    'Use PDO to connect PHP applications with MySQL.',
    25,
    2),
    
(6,
    2,
    'CRUD Operations',
    'pdf',
    'Create, read,
update and delete database records safely.',
    30,
    3),
    
(7,
    3,
    'Session Authentication',
    'article',
    'Build login sessions and role-aware access control.',
    28,
    1),
    
(8,
    3,
    'Secure Password Hashing',
    'article',
    'Use password_hash and password_verify for secure authentication.',
    16,
    2),
    
(9,
    3,
    'Mini Project Workflow',
    'article',
    'Connect course forms, database operations and navigation.',
    32,
    3),
    
(10,
    4,
    'Python Syntax',
    'article',
    'Python indentation, variables and basic expressions.',
    20,
    1),
    
(11,
    4,
    'Conditions and Loops',
    'article',
    'Build logic with conditions and loops.',
    22,
    2),
    
(12,
    5,
    'Lists and Tuples',
    'article',
    'Python collection fundamentals.',
    24,
    1),
    
(13,
    5,
    'Functions',
    'article',
    'Define, call and reuse Python functions.',
    24,
    2),
    
(14,
    6,
    'Tables and Keys',
    'article',
    'Relational tables and key constraints.',
    20,
    1),
    
(15,
    7,
    'SELECT and
WHERE',
    'article',
    'Query and filter records with SQL.',
    18,
    1),
    
(16,
    7,
    'JOINs',
    'article',
    'Combine related data with
JOIN queries.',
    26,
    2),
    
(17,
    8,
    'Design Principles',
    'article',
    'Balance, hierarchy, contrast and spacing.',
    18,
    1),
    
(18,
    9,
    'Reusable Components',
    'article',
    'Build a consistent UI component system.',
    22,
    1);

INSERT INTO assignments (id,
    course_id,
    title,
    instructions,
    max_marks,
    deadline,
    status)
VALUES
(1,
    1,
    'PHP CRUD Application',
    'Build a small CRUD application using PHP and MySQL. Submit the source archive and a short README.',
    20,
    '2026-09-28 23:59:00',
    'open'),
    
(2,
    1,
    'Authentication Flow',
    'Implement secure login, logout, password hashing and role-aware access.',
    15,
    '2026-10-03 23:59:00',
    'open'),
    
(3,
    2,
    'Python File Processor',
    'Create a script that reads a text file and generates a summary report.',
    20,
    '2026-10-01 23:59:00',
    'open'),
    
(4,
    3,
    'SQL Query
Set',
    'Write SELECT,
JOIN,
GROUP BY and aggregate queries for a sample schema.',
    25,
    '2026-10-05 23:59:00',
    'open');

INSERT INTO quizzes (id,
    course_id,
    title,
    time_limit_minutes,
    status)
VALUES
(1,
    1,
    'PHP Basics Quiz',
    10,
    'published'),
    (2,
    1,
    'MySQL & PDO Quiz',
    8,
    'published'),
    (3,
    2,
    'Python Fundamentals Quiz',
    10,
    'published'),
    (4,
    3,
    'SQL Basics Quiz',
    8,
    'published');

INSERT INTO questions (id,
    quiz_id,
    question_text,
    marks,
    sort_order)
VALUES
(1,
    1,
    'Which function creates a secure password hash in PHP?',
    1,
    1),
    
(2,
    1,
    'Which superglobal reads POST form data?',
    1,
    2),
    
(3,
    1,
    'Which statement starts a PHP block?',
    1,
    3),
    
(4,
    2,
    'Which PHP extension is recommended for MySQL access?',
    1,
    1),
    
(5,
    2,
    'Which SQL statement reads rows
from a table?',
    1,
    2),
    
(6,
    3,
    'Which keyword defines a function in Python?',
    1,
    1),
    
(7,
    3,
    'Which data type is ordered and mutable?',
    1,
    2),
    
(8,
    4,
    'Which SQL clause filters rows?',
    1,
    1);

INSERT INTO quiz_options (question_id,
    option_text,
    is_correct)
VALUES
(1,
    'password_hash()',
    1),
    (1,
    'hash_password()',
    0),
    (1,
    'encrypt_password()',
    0),
    (1,
    'secure_hash()',
    0),
    
(2,
    '$_POST',
    1),
    (2,
    '$_FORM',
    0),
    (2,
    '$_INPUT',
    0),
    (2,
    '$_REQUESTS',
    0),
    
(3,
    '<?php',
    1),
    (3,
    '<php>',
    0),
    (3,
    '<?',
    0),
    (3,
    '<script php>',
    0),
    
(4,
    'PDO',
    1),
    (4,
    'GD',
    0),
    (4,
    'cURL',
    0),
    (4,
    'XML',
    0),
    
(5,
    'SELECT',
    1),
    (5,
    'GET',
    0),
    (5,
    'READ',
    0),
    (5,
    'FETCH',
    0),
    
(6,
    'def',
    1),
    (6,
    'func',
    0),
    (6,
    'function',
    0),
    (6,
    'define',
    0),
    
(7,
    'List',
    1),
    (7,
    'Tuple',
    0),
    (7,
    'Set',
    0),
    (7,
    'String',
    0),
    
(8,
    'WHERE',
    1),
    (8,
    'FILTER',
    0),
    (8,
    'HAVING ONLY',
    0),
    (8,
    'LIMIT',
    0);

INSERT INTO enrollments (student_id,
    course_id,
    progress_percent)
VALUES (3,
    1,
    66.67),
    (3,
    3,
    50.00),
    (4,
    1,
    20.00),
    (4,
    2,
    45.00);

INSERT INTO lesson_progress (student_id,
    lecture_id,
    completed,
    completed_at)
VALUES (3,
    1,
    1,
    NOW()),
    (3,
    2,
    1,
    NOW()),
    (3,
    3,
    1,
    NOW()),
    (3,
    4,
    1,
    NOW()),
    (3,
    5,
    1,
    NOW()),
    (3,
    6,
    1,
    NOW()),
    (4,
    1,
    1,
    NOW()),
    (4,
    2,
    1,
    NOW());

INSERT INTO submissions (assignment_id,
    student_id,
    comment,
    marks,
    feedback,
    submitted_at,
    graded_at,
    status)
VALUES
(1,
    3,
    'CRUD project submitted for review.',
    18,
    'Good structure and clear database operations.',
    NOW(),
    NOW(),
    'graded'),
    
(2,
    3,
    'Authentication workflow submitted.',
    14,
    'Good use of sessions and password hashing.',
    NOW(),
    NOW(),
    'graded'),
    
(1,
    4,
    'First version submitted.',
    NULL,
    NULL,
    NOW(),
    NULL,
    'submitted');

INSERT INTO quiz_attempts (quiz_id,
    student_id,
    score,
    percentage,
    started_at,
    submitted_at)
VALUES
(1,
    3,
    3,
    100,
    NOW(),
    NOW()),
    (3,
    3,
    2,
    100,
    NOW(),
    NOW());

INSERT INTO announcements (author_id,
    title,
    content,
    audience,
    course_id)
VALUES
(1,
    'Welcome to the College LMS',
    'Use your college account to access courses, quizzes, assignments and results.',
    'all',
    NULL),
    
(2,
    'PHP Assignment Deadline',
    'The PHP CRUD Application assignment is due on 28 September 2026 at 11:59 PM.',
    'course',
    1),
    
(1,
    'Faculty
Update',
    'Please keep course content and announcements updated for students.',
    'teachers',
    NULL);

INSERT INTO course_ratings(course_id,
    student_id,
    rating,
    review)
VALUES (1,
    3,
    5,
    'Clear explanations and useful project work.');

INSERT INTO notifications (user_id,
    title,
    message,
    is_read)
VALUES
(3,
    'New assignment',
    'PHP CRUD Application has been published.',
    0),
    
(3,
    'Assignment graded',
    'Your Authentication Flow assignment has been graded.',
    0),
    
(4,
    'New course material',
    'New PHP course material is available.',
    1);
