#!/bin/bash
# Docker entrypoint script
# Runs migrations and then starts the PHP server

set -e

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║              🚀 Application Startup Script                    ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Get port from environment or use default
PORT=${PORT:-8000}

# Run migrations
echo "📊 Running database migrations..."
if php src/Database/migrate.php; then
    echo "✅ Migrations completed"
else
    echo "⚠️  Migrations warnings (application continues)"
fi

echo ""
echo "🌍 Starting PHP Development Server"
echo "   Listening on: 0.0.0.0:$PORT"
echo "   Document root: public/"
echo ""
echo "Press Ctrl+C to stop the server"
echo ""

# Start PHP server
exec php -S 0.0.0.0:${PORT} -t public/
