📁 APPLICATION/MIGRATIONS/

This directory contains SQL migration files for database schema management.

═════════════════════════════════════════════════════════════════════════════
📝 HOW TO USE
═════════════════════════════════════════════════════════════════════════════

1. Create a new .sql file in this directory
2. Name it with a prefix: 001_name.sql, 002_name.sql, etc.
3. Write your SQL statements (can have multiple, separated by ;)
4. Run: php src/Database/migrate.php
5. Migrations are tracked in the `migrations` table (won't re-run)

═════════════════════════════════════════════════════════════════════════════
📍 FILE NAMING
═════════════════════════════════════════════════════════════════════════════

Use one of these patterns:

  Numeric:      001_create_users.sql
                002_add_products.sql
                
  Timestamp:    20240215_143022_create_users.sql
                20240215_143023_add_products.sql
                
  Date-based:   2024-02-15_create_users.sql
                2024-02-16_add_products.sql

Files execute in ALPHABETICAL order, so:
  • 001_... → 002_... → 003_... (safe)
  • 20240215_... → 20240216_... (timestamp order)

⚠️ Don't rename files after first run! The migration system remembers by filename.

═════════════════════════════════════════════════════════════════════════════
✍️ EXAMPLE MIGRATION
═════════════════════════════════════════════════════════════════════════════

File: 001_create_users.sql

--- Create users table
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `email` VARCHAR(255) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `status` ENUM('active', 'inactive') DEFAULT 'active',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE INDEX idx_email ON `users`(`email`);
CREATE INDEX idx_status ON `users`(`status`);

═════════════════════════════════════════════════════════════════════════════
🚀 RUN MIGRATIONS
═════════════════════════════════════════════════════════════════════════════

$ php src/Database/migrate.php

or via Composer:

$ composer migrate

or automatically (on composer install):

$ composer install

═════════════════════════════════════════════════════════════════════════════
✅ BEST PRACTICES
═════════════════════════════════════════════════════════════════════════════

✓ Use IF NOT EXISTS in CREATE statements (idempotent)
✓ Use utf8mb4 with collation utf8mb4_unicode_ci
✓ Add indices for performance
✓ Include comments explaining changes
✓ Keep migrations focused and atomic
✓ Test migrations locally before production
✓ Never modify old migration files
✓ Use meaningful, descriptive names

═════════════════════════════════════════════════════════════════════════════
📚 EXAMPLE
═════════════════════════════════════════════════════════════════════════════

See: 001_example_schema.sql (included as example)

This file creates tables for a multi-vendor e-commerce marketplace.
You can use it as a template for your own migrations.

═════════════════════════════════════════════════════════════════════════════
