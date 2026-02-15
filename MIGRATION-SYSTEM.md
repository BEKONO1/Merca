╔═════════════════════════════════════════════════════════════════════════════╗
║                                                                             ║
║  🗄️  DATABASE MIGRATION SYSTEM                                            ║
║                                                                             ║
║  Automated migration runner with Composer and Docker integration           ║
║                                                                             ║
╚═════════════════════════════════════════════════════════════════════════════╝

═════════════════════════════════════════════════════════════════════════════
📋 QUICK START
═════════════════════════════════════════════════════════════════════════════

File Structure:
├── application/migrations/          ← Put your SQL files here
│   ├── 001_initial_schema.sql       ← Example migration
│   ├── 002_add_users_table.sql
│   └── 003_add_payments_table.sql
├── src/Database/
│   └── migrate.php                  ← Migration runner script
└── docker-entrypoint.sh             ← Auto-run migrations on Docker startup

Usage:
  # Run migrations manually
  php src/Database/migrate.php
  
  # Or via Composer
  composer migrate
  
  # Auto-runs on:
  • composer install (post-install-cmd hook)
  • Docker container startup (via entrypoint script)


═════════════════════════════════════════════════════════════════════════════
🎯 HOW IT WORKS
═════════════════════════════════════════════════════════════════════════════

1. Migration Files:
   └─ Place SQL files in: application/migrations/
   └─ Filename pattern: *.sql (e.g., 001_init.sql)
   └─ Can contain multiple SQL statements (split by ;)

2. Migration Tracking:
   └─ Table 'migrations' created automatically
   └─ Tracks which migrations have been executed
   └─ Prevents re-running completed migrations

3. Execution:
   └─ Reads all .sql files from application/migrations/
   └─ Checks 'migrations' table to skip already-executed ones
   └─ Executes new migrations in alphabetical order
   └─ Records successful migrations for tracking


═════════════════════════════════════════════════════════════════════════════
📝 CREATING MIGRATIONS
═════════════════════════════════════════════════════════════════════════════

Step 1: Create SQL file
  File: application/migrations/001_initial_schema.sql
  
  Example content:
  ┌─────────────────────────────────────────────────────────────────────┐
  │ -- Create users table                                               │
  │ CREATE TABLE IF NOT EXISTS `users` (                                │
  │   `id` INT AUTO_INCREMENT PRIMARY KEY,                             │
  │   `email` VARCHAR(255) NOT NULL UNIQUE,                            │
  │   `password` VARCHAR(255) NOT NULL,                                │
  │   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,                │
  │   `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE       │
  │     CURRENT_TIMESTAMP                                              │
  │ ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4                            │
  │   COLLATE=utf8mb4_unicode_ci;                                      │
  │                                                                     │
  │ -- Add index on email                                              │
  │ CREATE INDEX idx_email ON `users`(`email`);                        │
  └─────────────────────────────────────────────────────────────────────┘

Step 2: Naming Convention
  ├─ Use timestamp prefix: 20240215_create_users_table.sql
  ├─ Or numeric prefix: 001_create_users_table.sql
  ├─ Or date-based: 2024-02-15_initial_schema.sql
  └─ Files execute in alphabetical order (so naming matters!)

Step 3: Best Practices
  ✓ Use IF NOT EXISTS to make migrations idempotent
  ✓ Use fixed character set: utf8mb4
  ✓ Use COLLATE: utf8mb4_unicode_ci (emoji support)
  ✓ Add indices for performance
  ✓ Include comments explaining changes
  ✓ Keep migrations simple and focused
  ✓ Test migrations locally before deployment


═════════════════════════════════════════════════════════════════════════════
🚀 RUNNING MIGRATIONS
═════════════════════════════════════════════════════════════════════════════

Locally:
  php src/Database/migrate.php

Via Composer:
  composer migrate

Docker:
  docker-compose up

Automatic (on deploy):
  composer install          # Runs post-install-cmd → migrations
  Docker startup            # Runs docker-entrypoint.sh → migrations


═════════════════════════════════════════════════════════════════════════════
📊 MIGRATION TRACKING TABLE
═════════════════════════════════════════════════════════════════════════════

The script automatically creates this table:

  CREATE TABLE migrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    migration VARCHAR(255) NOT NULL UNIQUE,    -- Filename
    executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  )

Example contents:
  ┌────┬──────────────────────────────┬─────────────────────────┐
  │ id │ migration                    │ executed_at             │
  ├────┼──────────────────────────────┼─────────────────────────┤
  │ 1  │ 001_initial_schema.sql       │ 2024-02-15 14:30:25    │
  │ 2  │ 002_add_users_table.sql      │ 2024-02-15 14:30:26    │
  │ 3  │ 003_add_products_table.sql   │ 2024-02-15 14:30:28    │
  └────┴──────────────────────────────┴─────────────────────────┘

Query to see completed migrations:
  SELECT * FROM migrations ORDER BY executed_at;


═════════════════════════════════════════════════════════════════════════════
⚙️ DATABASE CONFIGURATION
═════════════════════════════════════════════════════════════════════════════

Migrations script reads configuration from:

1. DATABASE_URL (if set - Railway format)
   Example: mysql://user:pass@host:3306/dbname

2. Individual variables (local/.env):
   MYSQLHOST=localhost
   MYSQLUSER=root
   MYSQLPASSWORD=password
   MYSQLDATABASE=eshop_db

3. .env file (if using phpdotenv):
   Load automatically in src/Database/migrate.php

Configuration flow:
  1. Load .env if phpdotenv available
  2. Check ENVIRONMENT variable (production/development)
  3. Parse DATABASE_URL if set
  4. Fall back to MYSQL* variables
  5. Connect to database
  6. Create database if doesn't exist


═════════════════════════════════════════════════════════════════════════════
🔍 MIGRATION OUTPUT EXAMPLE
═════════════════════════════════════════════════════════════════════════════

$ php src/Database/migrate.php

╔════════════════════════════════════════════════════════════════╗
║              🗄️  DATABASE MIGRATION SCRIPT                     ║
╚════════════════════════════════════════════════════════════════╝

📊 Database Configuration:
   Host: localhost
   Database: eshop_db
   Charset: utf8mb4

✅ Connected to MySQL
✅ Database created or already exists
✅ Selected database: eshop_db
✅ Migrations tracking table ready

🔍 Reading migration files from: application/migrations/

═════════════════════════════════════════════════════════════════
🚀 EXECUTING MIGRATIONS
═════════════════════════════════════════════════════════════════

✅ Migrated: 001_initial_schema.sql (3 statements)
⏭️  Skipped: 002_add_users_table.sql (already executed)
✅ Migrated: 003_add_products_table.sql (5 statements)

═════════════════════════════════════════════════════════════════
📊 MIGRATION SUMMARY
═════════════════════════════════════════════════════════════════

   Executed: 2
   Skipped:  1
   Failed:   0

✅ Migration completed successfully


═════════════════════════════════════════════════════════════════
🐳 DOCKER INTEGRATION
═════════════════════════════════════════════════════════════════

Dockerfile Changes:
  • Added docker-entrypoint.sh
  • Migrations run automatically on container startup
  • Runs before PHP server starts
  • If migration fails, server still starts (non-blocking)

docker-entrypoint.sh does:
  1. Print startup banner
  2. Get PORT from environment (default 8000)
  3. Run: php src/Database/migrate.php
  4. Start PHP server on 0.0.0.0:$PORT

Deployment Flow:
  # Local Development
  $ php src/Database/migrate.php
  $ php -S localhost:8000 -t public/
  
  # Docker Local
  $ docker-compose up
    (entrypoint runs migrations automatically)
  
  # Railway Production
  $ git push
    → Railway builds Docker image
    → Migrations run during build: RUN php src/Database/migrate.php
    → Migrations run on startup: ENTRYPOINT runs entrypoint.sh
    → Application starts


═════════════════════════════════════════════════════════════════
📦 COMPOSER INTEGRATION
═════════════════════════════════════════════════════════════════

Added scripts to composer.json:

  "scripts": {
    "migrate": "php src/Database/migrate.php",
    "post-install-cmd": [
      "@migrate"
    ]
  }

Usage:
  # Manual migration run
  composer migrate
  
  # Automatic (on composer install)
  composer install
    → Runs: @migrate
    → Which runs: php src/Database/migrate.php


═════════════════════════════════════════════════════════════════
🔐 SECURITY CONSIDERATIONS
═════════════════════════════════════════════════════════════════

✓ SQL statements are prepared/executed via PDO
✓ Database credentials from environment variables
✓ .env file is git-ignored (never committed)
✓ Railway stores secrets in Variables dashboard
✓ No hardcoded credentials in code
✓ Migrations table tracks execution (prevents duplicates)

⚠️  Important:
  • Don't put sensitive data (passwords, API keys) in SQL migrations
  • Use environment variables for such data
  • Never commit .env file to repository
  • Review migration SQL before running on production


═════════════════════════════════════════════════════════════════
❌ TROUBLESHOOTING
═════════════════════════════════════════════════════════════════

Error: "Migrations directory not found"
  → Create: mkdir -p application/migrations
  → Add your first .sql file

Error: "No SQL migration files found"
  → Check directory: application/migrations/
  → Files must end in .sql
  → Example: 001_init.sql

Error: "Connection Error"
  → Check DATABASE_URL is set (Railway)
  → Or check .env has MYSQLHOST, MYSQLUSER, MYSQLPASSWORD
  → Verify database server is running

Error: "Migration failed"
  → Check SQL syntax in your migration file
  → Test SQL command manually
  → Check database permissions
  → Review error log: error_log messages

Error: "Table migrations doesn't exist"
  → Script creates it automatically
  → If creation fails, check 'CREATE TABLE' permissions


═════════════════════════════════════════════════════════════════
📚 EXAMPLE MIGRATIONS
═════════════════════════════════════════════════════════════════

See examples in: application/migrations/

Or create your first one:

File: application/migrations/001_create_tables.sql

Content:
┌─────────────────────────────────────────────────────────────┐
│ -- Create products table for e-shop                         │
│ CREATE TABLE IF NOT EXISTS `products` (                     │
│   `id` INT AUTO_INCREMENT PRIMARY KEY,                      │
│   `sku` VARCHAR(100) NOT NULL UNIQUE,                       │
│   `name` VARCHAR(255) NOT NULL,                             │
│   `description` TEXT,                                       │
│   `price` DECIMAL(10,2) NOT NULL,                           │
│   `stock` INT DEFAULT 0,                                    │
│   `status` ENUM('active', 'inactive') DEFAULT 'active',    │
│   `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,         │
│   `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON       │
│     UPDATE CURRENT_TIMESTAMP                                │
│ ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4                     │
│   COLLATE=utf8mb4_unicode_ci;                               │
│                                                             │
│ CREATE INDEX idx_sku ON `products`(`sku`);                 │
│ CREATE INDEX idx_status ON `products`(`status`);           │
│                                                             │
│ -- Create categories table                                 │
│ CREATE TABLE IF NOT EXISTS `categories` (                  │
│   `id` INT AUTO_INCREMENT PRIMARY KEY,                     │
│   `name` VARCHAR(100) NOT NULL UNIQUE,                     │
│   `slug` VARCHAR(100) UNIQUE,                              │
│   `description` TEXT                                       │
│ ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4                    │
│   COLLATE=utf8mb4_unicode_ci;                              │
│                                                             │
│ -- Create junction table for products and categories       │
│ CREATE TABLE IF NOT EXISTS `product_categories` (          │
│   `product_id` INT NOT NULL,                               │
│   `category_id` INT NOT NULL,                              │
│   PRIMARY KEY (`product_id`, `category_id`),               │
│   FOREIGN KEY (`product_id`)                               │
│     REFERENCES `products`(`id`) ON DELETE CASCADE,          │
│   FOREIGN KEY (`category_id`)                              │
│     REFERENCES `categories`(`id`) ON DELETE CASCADE         │
│ ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4                    │
│   COLLATE=utf8mb4_unicode_ci;                              │
└─────────────────────────────────────────────────────────────┘

Then run:
  php src/Database/migrate.php
  
Output:
  ✅ Migrated: 001_create_tables.sql (3 statements)
  ✅ Migration completed successfully


═════════════════════════════════════════════════════════════════
🎯 NEXT STEPS
═════════════════════════════════════════════════════════════════

1. Create application/migrations/ if it doesn't exist
2. Add your first migration SQL file
3. Run: php src/Database/migrate.php
4. Or: composer migrate
5. Verify tables created: SELECT * FROM migrations;


═════════════════════════════════════════════════════════════════
📖 RELATED FILES
═════════════════════════════════════════════════════════════════

src/Database/migrate.php          - Migration runner (290+ lines)
docker-entrypoint.sh              - Docker startup script
composer.json                     - Contains "migrate" script
Dockerfile                        - Uses entrypoint and runs migrations
MIGRATION-SYSTEM.md               - This documentation file


═════════════════════════════════════════════════════════════════
