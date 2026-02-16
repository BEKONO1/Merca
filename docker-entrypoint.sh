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

# Vérifier que le répertoire racine existe
echo "📁 Vérification du DocumentRoot..."
if [ ! -d "/var/www/html" ]; then
    echo "❌ ERREUR: Le répertoire /var/www/html n'existe pas!"
    exit 1
fi

# Vérifier que index.php existe
if [ ! -f "/var/www/html/index.php" ]; then
    echo "⚠️  Attention: index.php n'existe pas à la racine!"
    echo "   Fichiers trouvés dans /var/www/html:"
    ls -la /var/www/html/ | head -10
fi

# Afficher les permissions
echo "🔐 Permissions actuelles:"
ls -la /var/www/html/ | head -10

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
echo "   DocumentRoot: /var/www/html"
echo ""

# Démarrer Apache en premier plan
exec apache2-foreground
