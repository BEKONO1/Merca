#!/usr/bin/env bash
# ============================================================
# QUICK START GUIDE - eShop für Railway
# ============================================================
# Ce script aide à initialiser le projet pour Railway

set -e

echo "============================================================"
echo "  🚂 eShop Railway Setup"
echo "============================================================"
echo ""

# Vérifier les prérequis
echo "📋 Vérification des prérequis..."

if ! command -v docker &> /dev/null; then
    echo "❌ Docker n'est pas installé: https://docker.com/products/docker-desktop"
    exit 1
fi
echo "✅ Docker installé"

if ! command -v git &> /dev/null; then
    echo "❌ Git n'est pas installé"
    exit 1
fi
echo "✅ Git installé"

echo ""
echo "============================================================"
echo "  Choix d'environnement"
echo "============================================================"
echo ""
echo "1) Dev local avec Docker (docker-compose)"
echo "2) Dev local sans Docker (PHP intégré)"
echo "3) Déploiement sur Railway"
echo ""
read -p "Choisir (1-3): " choice

case $choice in
  1)
    echo ""
    echo "============================================================"
    echo "  🐳 Docker Setup (Développement)"
    echo "============================================================"
    echo ""
    
    if [ ! -f .env ]; then
      echo "Création du fichier .env..."
      cp .env.docker .env
      echo "✅ .env créé (copie de .env.docker)"
    else
      echo "⚠️  .env existe déjà"
    fi
    
    echo ""
    echo "Démarrage des conteneurs..."
    docker-compose up -d
    
    echo ""
    echo "⏳ Attente du démarrage de MySQL (30 secondes)..."
    sleep 30
    
    echo ""
    echo "============================================================"
    echo "  ✅ Configuration terminée !"
    echo "============================================================"
    echo ""
    echo "Accéder à l'application:"
    echo "  🌐 http://localhost:8000"
    echo ""
    echo "phpMyAdmin:"
    echo "  🛠️  http://localhost:8080"
    echo "  - Serveur: db"
    echo "  - Utilisateur: eshop_user"
    echo "  - Mot de passe: eshop_password"
    echo ""
    echo "Commandes utiles:"
    echo "  docker-compose logs -f app     → Logs en temps réel"
    echo "  docker-compose exec app bash   → Terminal dans le container"
    echo "  docker-compose down            → Arrêter les services"
    echo ""
    echo "📖 Pour plus: voir DOCKER.md"
    echo ""
    ;;

  2)
    echo ""
    echo "============================================================"
    echo "  🏃 Dev local sans Docker"
    echo "============================================================"
    echo ""
    
    if [ ! -f .env ]; then
      echo "Création du fichier .env..."
      cp .env.example .env
      echo "✅ .env créé"
      echo ""
      echo "⚠️  Éditer .env avec votre configuration MySQL locale:"
      echo "  MYSQLHOST=localhost"
      echo "  MYSQLUSER=root"
      echo "  MYSQLPASSWORD=..."
      echo "  MYSQLDATABASE=eshop_db"
    else
      echo "⚠️  .env existe déjà"
    fi
    
    echo ""
    echo "Installation des dépendances..."
    if command -v composer &> /dev/null; then
      composer install
    else
      echo "⚠️  Composer n'est pas installé"
      echo "Installer via: https://getcomposer.org/download/"
      exit 1
    fi
    
    echo ""
    echo "============================================================"
    echo "  ✅ Configuration terminée !"
    echo "============================================================"
    echo ""
    echo "Démarrer le serveur:"
    echo "  php -S localhost:8000 -t public/"
    echo ""
    echo "Puis accéder à:"
    echo "  🌐 http://localhost:8000"
    echo ""
    echo "📖 Pour plus: voir SETUP.md"
    echo ""
    ;;

  3)
    echo ""
    echo "============================================================"
    echo "  🚂 Railway Deployment"
    echo "============================================================"
    echo ""
    echo "1️⃣  Vérifier git repository:"
    git status
    echo ""
    
    echo "2️⃣  Commitez les changements:"
    echo "   git add ."
    echo "   git commit -m 'feat: Railway setup with Docker'"
    echo ""
    
    echo "3️⃣  Pushez vers GitHub:"
    echo "   git push origin main"
    echo ""
    
    echo "4️⃣  Sur Railway.app:"
    echo "   - Créez un nouveau projet"
    echo "   - Connectez votre repository GitHub"
    echo "   - Railway détecte le Dockerfile automatiquement"
    echo "   - Ajoutez une base de données MySQL"
    echo "   - Configurez les variables (DATABASE_URL, etc.)"
    echo "   - Déployez !"
    echo ""
    
    echo "📖 Pour plus: voir RAILWAY.md et DEPLOYMENT.md"
    echo ""
    ;;

  *)
    echo "❌ Choix invalide (1-3)"
    exit 1
    ;;
esac
