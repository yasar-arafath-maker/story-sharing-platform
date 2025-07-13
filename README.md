# story-sharing-platform
👥 User Roles
Author

Login and manage stories (create, edit, delete).

View their own profile.

See recent subscribers and comments on their stories.

Reader

View authors and their stories.

Like and comment on stories using emoji buttons.

Subscribe to favorite authors.

🧩 Database Setup
Database name: storydb

Run this SQL in phpMyAdmin:

sql
Copy
Edit
CREATE DATABASE storydb;
USE storydb;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('author', 'reader') NOT NULL DEFAULT 'reader'
);

CREATE TABLE stories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    author_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    likes INT NOT NULL DEFAULT 0,
    FOREIGN KEY (author_id) REFERENCES users(id)
);

CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    story_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (story_id) REFERENCES stories(id),
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    author_id INT NOT NULL,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (author_id) REFERENCES users(id)
);
🔑 Login Credentials Format (Example)
Use register.php to register:

Role: Choose "author" or "reader"

Passwords are hashed during registration.

📂 File Structure
cpp
Copy
Edit
storyshare/
├── db_connect.php
├── login.php
├── register.php
├── logout.php
├── author_dashboard.php
├── reader_home.php
├── view_author.php
├── view_story.php
├── post_story.php
├── like_story.php
├── comment_story.php
├── subscribe.php
└── assets/
    ├── css/
    └── js/
▶️ How to Run the Project
Start Apache & MySQL from XAMPP Control Panel.

Place your project folder inside: C:\xampp\htdocs\storyshare

Open browser and go to:
🔗 http://localhost/storyshare/register.php to create a user
🔗 http://localhost/storyshare/login.php to log in

✅ Features
Author login → post & manage stories

Reader login → view, like, comment, and subscribe

Notifications via dashboards (comments, subscribers)

Emoji-based comment and like system

Clean UI with purple-pink aesthetic
