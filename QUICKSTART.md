🚀 QUICKSTART - eShop + Railway + Docker
=========================================

Démarrez en 5 minutes ! Choisissez votre scénario :

---

## 🐳 Scénario 1: Développement avec Docker (⭐ Recommandé)

**Idéal pour:** Tester localement exactement comme en production

### Étapes:

```bash
# 1. Copier la configuration Docker
cp .env.docker .env

# 2. Démarrer les services (App + MySQL)
docker-compose up -d

# 3. Attendre ~30 secondes le démarrage

# 4. Ouvrir dans le navigateur
# http://localhost:8000      (Application)
# http://localhost:8080      (phpMyAdmin)
```

### Commandes utiles:

```bash
docker-compose logs -f app      # Voir les logs en direct
docker-compose exec app bash    # Terminal dans le container
docker-compose down             # Arrêter tout
```

📖 **Voir:** [DOCKER.md](DOCKER.md)

---

## 💻 Scénario 2: Développement local (PHP intégré)

**Idéal pour:** Développement rapide sans dépendances externes

### Étapes:

```bash
# 1. Copier la configuration locale
cp .env.example .env

# 2. Éditer .env avec votre MySQL local
nano .env

# 3. Installer les dépendances PHP
composer install

# 4. Lancer le serveur intégré
php -S localhost:8000 -t public/

# 5. Ouvrir http://localhost:8000
```

### Prérequis:

- MySQL local en cours d'exécution
- PHP 8.3+ installé
- Composer installé

📖 **Voir:** [SETUP.md](SETUP.md)

---

## 🚂 Scénario 3: Déployer sur Railway

**Idéal pour:** Production, HTTPS, domaine personnalisé

### Étapes:

```bash
# 1. Commitez tout (Docker config, .env.example, etc.)
git add .
git commit -m "feat: Railway with Docker PHP 8.3"

# 2. Pushez vers GitHub
git push origin main

# 3. Allez sur https://railway.app
#    → New Project
#    → Sélectionnez votre repo GitHub
#    → Railway détecte le Dockerfile
#    → Ajoutez MySQL
#    → Configurez DATABASE_URL
#    → Déployez !
```

**OK en 5 minutes !** Railway construit et déploie automatiquement.

📖 **Voir:** [RAILWAY.md](RAILWAY.md)

---

## 📁 Fichiers importants créés

| Fichier | Fonction | Utilisé par |
|---------|----------|------------|
| `Dockerfile` | Image PHP 8.3 + Composer | Docker, Railway |
| `railway.json` | Config Railway | Railway uniquement |
| `docker-compose.yml` | Dev local | Docker Compose |
| `.env.docker` | Config Docker dev | Développeurs |
| `.env.example` | Config template | Tous |
| `.dockerignore` | Optimise le build | Docker |

---

## 🔑 Hiérarchie des variables d'environnement

### Pour Railway:
```
DATABASE_URL=mysql://user:pass@host:port/db
```
✅ Automatique sur Railway

### Pour développement local:
```
MYSQLHOST=localhost
MYSQLUSER=eshop_user
MYSQLPASSWORD=eshop_password
MYSQLDATABASE=eshop_db
```
✅ Utilisé en fallback

---

## ❓ Besoin d'aide ?

| Question | Lire |
|----------|------|
| Comment utiliser Docker ? | [DOCKER.md](DOCKER.md) |
| Comment déployer sur Railway ? | [RAILWAY.md](RAILWAY.md) |
| Configuration développement sans Docker ? | [SETUP.md](SETUP.md) |
| Fichiers de déploiement ? | [DEPLOYMENT.md](DEPLOYMENT.md) |

---

## ⚡ TL;DR (Le plus rapide)

### Docker (Recommended):
```bash
cp .env.docker .env && docker-compose up -d
# http://localhost:8000
```

### Sans Docker:
```bash
cp .env.example .env && composer install && php -S localhost:8000 -t public/
# http://localhost:8000
```

### Railway:
```bash
git add . && git commit -m "feat" && git push
# Allez sur railway.app → déploiement auto
```

---

**Prêt à coder ! 🚀**
