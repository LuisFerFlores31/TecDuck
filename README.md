# Matecduck


**Matecduck** is a web application designed to help students learn math in an engaging and structured way. The platform includes user registration, login, and role-based redirection for different types of users (students, teachers, and admins).

## 🧠 Features

- 🔐 Secure login & registration system using PHP and MySQL
- 👥 Role-based access: users are redirected depending on their role (student/teacher/admin)
- 📄 Sessions for user persistence
- 🧾 Admin and teacher dashboards
- 🎨 Responsive HTML/CSS frontend
- 🚪 Logout functionality with session cleanup

  
## 🚀 How It Works

### Roles

- `0` → Professor → redirects to `/admins/profesor.php`
- `1` → Admin → redirects to `/admins/admin.php`

### Authentication

- Passwords are hashed using `password_hash()` and verified with `password_verify()`.
- Sessions are initialized via `session_start()` and checked with `check_session.php`.

### File Highlights

- `login.php`: Handles user authentication
- `register.php`: Handles new user registration
- `logout.php`: Safely destroys the session and redirects to login
- `check_session.php`: Protects private pages from unauthorized access

## 🛠️ Technologies Used

- PHP 
- MySQL / MariaDB
- HTML5
- CSS3
- Apache (via macOS Homebrew)

## ⚙️ Server Setup

Make sure your Apache `httpd.conf` includes:

```apache
DocumentRoot "/Users/yourname/Game/TecDuck/Login"
<Directory "/Users/yourname/Game/TecDuck/Login">
    Options Indexes FollowSymLinks
    AllowOverride All
    Require all granted
</Directory>

LoadModule php_module /opt/homebrew/opt/php/lib/httpd/modules/libphp.so
DirectoryIndex index.php index.html
AddType application/x-httpd-php .php
```

### Run your sevrer
```
brew services start httpd
```

### Access the project
```
http://localhost:8090/login.html

```

## 🔐 Notes
All redirections are based on PHP sessions and roles stored in the database.

Make sure config.php contains the correct DB credentials.

Session-protected pages (like admin.php or profesor.php) require check_session.php.

## 🧪 Future Improvements

- Google login integration (UI already in place)
- Password recovery system
- Email verification

