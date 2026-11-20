-- Run this once in phpMyAdmin on your existing StudyHubLms database.
-- Converts video lessons to text/article lessons and supplies starter reading notes.
UPDATE lectures
SET lesson_type = 'article'
WHERE lesson_type = 'video';

UPDATE lectures
SET content = 'PHP (Hypertext Preprocessor) is a server-side scripting language used to build dynamic websites. PHP code runs on the web server, and the generated HTML is sent to the visitor’s browser. This means PHP can work with forms, sessions, files, and databases before the page is displayed.\n\nHow PHP works:\n1. A visitor requests a PHP page in the browser.\n2. The web server runs the PHP code.\n3. PHP can process data or communicate with a database.\n4. The server returns the generated HTML to the browser.\n\nA PHP script usually starts with <?php and statements commonly end with a semicolon. PHP can be embedded inside HTML. Use clear variable names, validate user input, and keep database credentials out of public files.\n\nExample:\n<?php\n$name = "Mahi";

\necho "Hello, " . $name;

\n?>\n\nKey points: PHP is executed on the server, can generate dynamic content, and is often used with MySQL to create database-driven applications.'
WHERE title = 'Introduction to PHP';

UPDATE lectures
SET content = 'A variable stores a value that a program can use. In PHP, variable names begin with the dollar sign ($), for example $name or $marks. Variable names are case-sensitive, so $name and $Name are different.\n\nCommon PHP data types:\n• String: text, such as "Hello".\n• Integer: a whole number, such as 18.\n• Float: a decimal number, such as 85.5.\n• Boolean: true or false.\n• Array: a collection of
values.\n• NULL: represents a variable with no value.\n\nExample:\n$name = "Mahi";

\n$age = 18;

\n$percentage = 85.5;

\n$isStudent = true;

\n\nUse var_dump($name) when you want to inspect a value and its type. Choose meaningful variable names and give variables a value before relying on them.'
WHERE title = 'Variables and Data Types';

UPDATE lectures
SET content = 'Control flow determines which statements run and how many times they run.\n\nConditions:\n• if runs code when a condition is true.\n• elseif checks another condition.\n• else runs when earlier conditions are false.\n• switch compares one expression with several cases.\n\nLoops repeat code:\n• for is useful when the number of repetitions is known.\n• while repeats as long as a condition remains true.\n• foreach is commonly used to read each value in an array.\n\nExample:\n$marks = 85;

\nif ($marks >= 40) {\n    echo "Pass";

\n} else {\n    echo "Try again";

\n}\n\nRemember to use comparison operators such as == or === carefully, and make sure loop conditions eventually become false to avoid an endless loop.'
WHERE title = 'Control Flow';

UPDATE lectures
SET content = 'HTML forms collect information
from users. A form can send data using GET or POST. GET places form
values in the URL and is useful for searches or filters. POST sends
values in the request body and is commonly used for registrations and other submissions.\n\nIn PHP, read submitted
values with $_GET or $_POST. Always validate required fields and check the expected data type. Never trust user input simply because it came
from your own form.\n\nExample form:\n<form method="post">\n  <input name="student_name" required>\n  <button type="submit">Submit</button>\n</form>\n\nExample PHP check:\nif ($_SERVER["REQUEST_METHOD"] === "POST") {\n    $name = trim($_POST["student_name"] ?? "");

\n}\n\nWhen displaying user-provided text in HTML, escape it to help prevent cross-site scripting. Use server-side validation even when HTML validation is present.'
WHERE title = 'HTML Forms with PHP';

UPDATE lectures
SET content = 'PHP can connect to MySQL using PDO (PHP Data Objects). PDO provides a consistent interface for database access and supports prepared statements.\n\nBasic steps:\n1.
Set the database host, database name, username, and password.\n2. Create a PDO connection.\n3. Use a prepared statement for queries containing user input.\n4. Handle connection errors safely.\n\nExample:\n$pdo = new PDO(\n    "mysql:host=localhost;

dbname=college;

charset=utf8mb4",\n    $username,\n    $password,\n    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]\n);

\n\nPrepared statements help protect queries
from SQL injection. Keep credentials private, use a database account with only the permissions it needs, and avoid showing detailed database errors to website visitors.'
WHERE title = 'Connecting PHP to MySQL';

UPDATE lectures
SET content = 'CRUD stands for Create, Read,
Update, and Delete—the four common operations used to manage database records.\n\n• Create: insert a new record with INSERT.\n• Read: retrieve records with SELECT.\n•
Update: change an existing record with
UPDATE.\n• Delete: remove a record with DELETE.\n\nIn a PHP application, validate form
values, use PDO prepared statements, and check whether an operation affected the expected number of rows. Ask for confirmation before destructive actions and enforce access permissions on the server.\n\nExample SQL:\nSELECT id, name
FROM students
WHERE id = ?;

\n\nThe question mark is a placeholder that can be bound to a value using a prepared statement. Never build SQL by directly joining untrusted input into the query string.'
WHERE title = 'CRUD Operations';
