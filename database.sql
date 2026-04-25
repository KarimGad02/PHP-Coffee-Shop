-- 1. Rooms Table (Independent)
CREATE TABLE IF NOT EXISTS rooms (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    extension TEXT
);

-- 2. Categories Table (Independent)
CREATE TABLE IF NOT EXISTS categories (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT UNIQUE NOT NULL
);

-- 3. Users Table (Depends on Rooms)
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT DEFAULT 'customer',
    room_id INTEGER, -- Default room
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    updated_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL,
    CHECK (role IN ('admin', 'customer'))
);

CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);

-- 4. Password Resets Table
CREATE TABLE IF NOT EXISTS password_resets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL,
    token TEXT NOT NULL,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

-- 5. Products Table (Depends on Categories)
CREATE TABLE IF NOT EXISTS products (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    price REAL NOT NULL,
    image TEXT,
    is_available INTEGER DEFAULT 1, -- admin can toggle this
    category_id INTEGER,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- 6. Orders Table (Depends on Users and Rooms)
CREATE TABLE IF NOT EXISTS orders (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    room_id INTEGER NOT NULL, -- Exact delivery destination
    status TEXT DEFAULT 'processing',
    total_amount REAL NOT NULL,
    notes TEXT,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL,
    CHECK (status IN ('processing', 'out for delivery', 'done', 'canceled'))
);

-- 7. Order Items Junction Table (Depends on Orders and Products)
CREATE TABLE IF NOT EXISTS order_items (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    order_id INTEGER NOT NULL,
    product_id INTEGER NOT NULL,
    quantity INTEGER NOT NULL DEFAULT 1,
    historical_price REAL NOT NULL, -- Locks in the price at the time of checkout!
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- =========================================
-- DEFAULT SEED DATA
-- =========================================

-- Insert default rooms
INSERT INTO rooms (name, extension) VALUES ('admin office', '0000');
INSERT INTO rooms (name, extension) VALUES ('room 2010', '5605');

-- Insert default admin user (Password: admin123)
-- Make sure to paste the exact $2y$ hash you generated earlier here!
INSERT INTO users (name, email, password, role, room_id) VALUES 
('admin user', 'admin@cafeteria.com', '$2y$10$9iFpmbwRGWr2BXar4hFEuewPKELkKNjOCWJc5yxlzPuCiNGMXbHWK', 'admin', 1);