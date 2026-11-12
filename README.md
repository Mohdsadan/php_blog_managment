
# Blog Management System

A content management system built using Core PHP, MySQL,
PDO, Bootstrap 5, HTML, CSS and JavaScript.

## Features

- Author registration and login
- Admin and author roles
- Create, edit and delete blog posts
- Draft and published post status
- Blog categories
- Featured image uploads
- Public blog homepage
- Blog details pages
- Comment submission and moderation
- Dashboard statistics
- Secure PDO database operations
- CSRF protection and password hashing

## Requirements

- XAMPP
- PHP 8+
- MySQL
- VS Code
- Web browser

## Installation

1. Copy the project into C:/xampp/htdocs/blog-management.
2. Start Apache and MySQL.
3. Open phpMyAdmin.
4. Import database.sql.
5. Check config/database.php.
6. Create the uploads directory.
7. Open http://localhost/blog-management/.

## Default database configuration

Host: localhost
Database: blog_management
Username: root
Password: blank (default XAMPP setup)

## Admin account setup

Register an account through the website.
Then promote that account using phpMyAdmin:

UPDATE users
SET role = 'admin'
WHERE email = 'your - email Id ';

Log out and log in again to refresh the role in the session.

## Security

- Password hashing
- PDO prepared statements
- CSRF tokens
- HTML output escaping
- Role-based authorization
- Image MIME validation
