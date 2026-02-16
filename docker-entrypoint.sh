#!/bin/bash
# Docker entrypoint script pour Railway avec Apache

set -e

echo "╔════════════════════════════════════════════════════════════════╗"
echo "║              🚀 Application Startup Script                    ║"
echo "╚════════════════════════════════════════════════════════════════╝"
echo ""

# Utiliser le port défini par Railway (défaut: 80)
PORT=${PORT:-80}

echo "🔧 Configuration d'Apache sur le port ${PORT}..."

# Désactiver tous les MPMs sauf un pour éviter les conflits
echo "🔧 Configuration MPM Apache..."
a2dismod mpm_worker mpm_event 2>/dev/null || true
a2enmod mpm_prefork 2>/dev/null || true

# Modifier le port d'écoute Apache
sed -i "s/Listen 80/Listen ${PORT}/g" /etc/apache2/ports.conf 2>/dev/null || true
sed -i "s/:80/:${PORT}/g" /etc/apache2/sites-available/000-default.conf 2>/dev/null || true

# Exécuter les migrations
echo "📊 Exécution des migrations..."
if php src/Database/migrate.php 2>/dev/null; then
    echo "✅ Migrations terminées"
else
    echo "⚠️  Migrations ignorées (peut être normal)"
fi

echo ""
echo "🌍 Démarrage du serveur Apache"
echo "   Port: ${PORT}"
echo "   DocumentRoot: /var/www/html/public"
echo ""

# Démarrer Apache en premier plan
exec apache2-foreground
