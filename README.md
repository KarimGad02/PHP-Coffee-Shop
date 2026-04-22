# Users Management API (Admin / Customer)

Simple native PHP backend for a training project focused only on:
- Authentication
- Users CRUD
- Two roles: admin and customer

Database is local SQLite for zero-setup development.

## What's done So far

1. Users Model, Controllers, and Routes.
2. Authentication for Logging in.
3. Added plug-and-play setup scripts so others can run quickly after clone.

## Tech Stack

- PHP 8+
- PDO + SQLite
- Plain PHP routing and controllers

## Current Project Structure

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
│   │   └── User.php
│   └── routes/
│       └── web.php
├── public/
│   └── index.php
├── scripts/
│   ├── setup.bat
│   └── start.bat
├── storage/
├── database.sql
├── setup.php
└── README.md
```

## Plug And Play Setup

### Option A (Windows batch scripts)

1. Setup:
```bat
scripts\setup.bat
```

2. Start server:
```bat
scripts\start.bat
```

### Option B (Direct PHP commands)

1. Setup:
```bash
php setup.php
```

2. Start server:
```bash
php -S localhost:8000 -t public
```

## Fresh Reset (Optional)

If you want a clean database:

```bash
php setup.php --fresh
```

This recreates `storage/cafeteria.sqlite` and re-runs schema seed data.

## Default Admin Account

- Email: admin@cafeteria.com
- Password: admin123

## API Endpoints

### Auth

- POST `/auth/login`
- POST `/auth/logout`
- POST `/auth/register` (creates customer)
- POST `/auth/forgot-password`
- GET `/auth/me`

### Customer Self CRUD

- GET `/users/me`
- PUT `/users/me`
- DELETE `/users/me`

### Admin Users CRUD

- GET `/admin/users`
- POST `/admin/users`
- GET `/admin/users/:id`
- PUT `/admin/users/:id`
- DELETE `/admin/users/:id`

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

```json
POST /admin/users
{
  "name": "Sara Ali",
  "email": "sara@example.com",
  "password": "123456",
  "room_number": "203",
  "extension": "4567",
  "role": "customer"
}
```

### Update My Profile

```json
PUT /users/me
{
  "name": "Updated Name",
  "email": "me@example.com",
  "room_number": "305",
  "extension": "5555",
  "password": ""
}
```

## Notes

- Role is limited to `admin` or `customer`.
- Admin-only routes enforce session role checks.

## Troubleshooting

### Error: could not find driver

Enable in `php.ini`:
- extension=pdo_sqlite
- extension=sqlite3

Then restart PHP.

### DB not initializing

- Ensure `storage/` is writable
- Run `php setup.php --fresh`

## License

This project is part of ITI training work.
