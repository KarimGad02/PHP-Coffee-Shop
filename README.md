
# Cafeteria Management System

A native PHP full-stack application built for an ITI training project. This system handles role-based authentication (Admin/Customer), product catalog management, and order processing using a localized SQLite database.

## Architecture & Current Status (Phase 1 Complete)

The core foundation of the application is fully established:
1. **Fully Normalized Database:** SQLite tables for Rooms, Categories, Products, Users, Orders, and Order Items.
2. **Core Data Models:** OOP classes mapping to the database (`User.php`, `Room.php`, `Category.php`, `Product.php`).
3. **Global UI Template:** A unified Bootstrap 5 layout using PHP includes (`header.php`, `footer.php`) and a custom glassmorphism CSS theme.
4. **Authentication Flow:** Secure login, simulated password recovery, and strict session-based role routing.
5. **Plug-and-Play Environment:** Automated setup scripts for instant local deployment.

## Tech Stack

- **Backend:** PHP 8+, PDO, custom routing architecture.
- **Database:** SQLite (Zero-setup).
- **Frontend:** HTML5, Bootstrap 5, Vanilla JavaScript (Fetch API), Custom CSS.

## Project Structure

```text
PHP-Coffee-Shop/
├── app/
│   ├── config/
│   │   └── Database.php
│   ├── controllers/
│   │   ├── AuthController.php
│   │   ├── AdminUserController.php
│   │   └── UserController.php
│   ├── models/
│   │   ├── Category.php
│   │   ├── Product.php
│   │   ├── Room.php
│   │   └── User.php
│   └── routes/
│       └── web.php
├── public/
│   ├── admin/
│   │   └── dashboard.php
│   ├── css/
│   │   └── style.css
│   ├── includes/
│   │   ├── footer.php
│   │   └── header.php
│   ├── js/
│   │   └── app.js
│   ├── forgot-password.php
│   ├── login.php
│   └── reset-password.php
├── scripts/
│   ├── setup.bat
│   └── start.bat
├── storage/
│   └── cafeteria.sqlite (Generated)
├── database.sql
├── setup.php
└── README.md
```

## Plug And Play Setup

### Option A (Windows batch scripts)

1. Setup the environment and database:
```bat
scripts\setup.bat
```

2. Start the local server:
```bat
scripts\start.bat
```

### Option B (Direct PHP commands)

1. Initialize the SQLite Database:
```bash
php setup.php
```

2. Start the PHP Server:
```bash
php -S localhost:8000 -t public
```

## Fresh Reset (Optional)

If you need a clean database during development to wipe all test data:

```bash
php setup.php --fresh
```
*Note: This drops all tables, recreates `storage/cafeteria.sqlite`, and inserts the seed data (default rooms and the admin account).*

## Default Admin Account

- **Email:** admin@cafeteria.com
- **Password:** admin123

## UI Routes (Frontend)

- `/login.php` - System entry point.
- `/forgot-password.php` - Simulated email recovery.
- `/reset-password.php` - Secure token password update.
- `/admin/dashboard.php` - Protected admin landing page.

## API Endpoints (Backend)

### Auth
- `POST /auth/login`
- `POST /auth/logout`
- `POST /auth/register`
- `POST /auth/forgot-password`
- `POST /auth/reset-password`
- `GET /auth/me`

### Customer Self CRUD
- `GET /users/me`
- `PUT /users/me`
- `DELETE /users/me`

### Admin Users CRUD
- `GET /admin/users`
- `POST /admin/users`
- `GET /admin/users/:id`
- `PUT /admin/users/:id`
- `DELETE /admin/users/:id`

## Request Examples

### Login
```json
POST /auth/login
{
  "email": "admin@cafeteria.com",
  "password": "admin123"
}
```

### Create Customer (Admin)
*Note: Users are now tied relationally to the `rooms` table via `room_id`.*
```json
POST /admin/users
{
  "name": "Sara Ali",
  "email": "sara@example.com",
  "password": "123456",
  "room_id": 2,
  "role": "customer"
}
```

## Troubleshooting

### Error: "could not find driver"
Enable the required SQLite extensions in your `php.ini` file:
- `extension=pdo_sqlite`
- `extension=sqlite3`
*(Restart your PHP server after saving changes).*

### Database Not Initializing / White Screens
- Ensure the `storage/` directory has write permissions.
- Ensure all root-level PHP files use `__DIR__` for includes (e.g., `include __DIR__ . '/includes/header.php';`).
- Run `php setup.php --fresh`.
