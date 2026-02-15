# 🐳 Guide Docker - Développement Local

Ce guide explique comment utiliser Docker pour développer eShop localement, identique à l'environnement Railway.

---

## 📋 Prérequis

- [Docker Desktop](https://www.docker.com/products/docker-desktop) installé
- [Docker Compose](https://docs.docker.com/compose/install/) (inclus dans Docker Desktop)
- Minimum 4GB RAM alloué à Docker

---

## 🚀 Démarrage rapide

### 1. Configurer l'environnement

```bash
# Copier la configuration Docker
cp .env.docker .env

# Ou éditer .env manuellement:
MYSQLHOST=db
MYSQLUSER=eshop_user
MYSQLPASSWORD=eshop_password
MYSQLDATABASE=eshop_db
```

### 2. Lancer les conteneurs

```bash
# Construire les images et démarrer les services
docker-compose up -d

# Afficher les logs
docker-compose logs -f app

# Attendre que MySQL soit prêt (~30 secondes)
```

### 3. Accéder à l'application

- **Application**: http://localhost:8000
- **Admin**: http://localhost:8000/admin
- **Seller**: http://localhost:8000/seller
- **phpMyAdmin**: http://localhost:8080
  - Serveur: `db`
  - Utilisateur: `eshop_user`
  - Mot de passe: `eshop_password`

---

## 📁 Structure Docker vs Code

| Local | Container | Description |
|-------|-----------|-------------|
| `./` | `/app` | Racine du projet |
| `./public` | `/app/public` | Webroot |
| `./src` | `/app/src` | Code applicatif |
| `./storage` | `/app/storage` | Logs, cache (sync) |
| `./vendor` | `/app/vendor` | Dépendances (conteneur) |

Le `/vendor` est **ignoré** en local pour économiser l'espace disque.

---

## 🔧 Commandes courantes

### Démarrer/Arrêter

```bash
# Démarrer les services
docker-compose up -d

# Arrêter les services
docker-compose down

# Arrêter et supprimer les volumes (données MySQL perdues)
docker-compose down -v

# Redémarrer un service
docker-compose restart app
```

### Logs

```bash
# Logs en temps réel
docker-compose logs -f app

# Logs du dernier message
docker-compose logs app | tail -50

# Logs MySQL
docker-compose logs db
```

### Exécuter des commandes

```bash
# Accéder au bash du container app
docker-compose exec app bash

# Exécuter une commande PHP
docker-compose exec app php -v

# Exécuter une commande Composer
docker-compose exec app composer install

# Exécuter une migration CodeIgniter
docker-compose exec app php index.php migrate
```

### Base de données

```bash
# Accéder à MySQL directement
docker-compose exec db mysql -u eshop_user -p eshop_db

# Exporter la base de données
docker-compose exec db mysqldump -u eshop_user -p eshop_db > backup.sql

# Importer un fichier SQL
docker-compose exec -T db mysql -u eshop_user -p eshop_db < backup.sql
```

---

## 🧹 Nettoyage & Rebuild

### Les images deviennent obsolètes après les modifications

```bash
# Rebuild complètement
docker-compose down -v
docker-compose build --no-cache
docker-compose up -d

# Ou plus rapidement (si seulement le code a changé)
docker-compose up -d --build
```

### Nettoyer les ressources non utilisées

```bash
# Supprimer les images inutilisées
docker image prune

# Supprimer les volumes inutilisés
docker volume prune

# Supprimer tout (⚠️ Be careful)
docker system prune -a
```

---

## 📊 Vérification

### Conteneurs actifs

```bash
docker-compose ps
```

Résultat attendu:
```
NAME                STATUS          PORTS
eshop-app           Up 2 minutes    0.0.0.0:8000->8000/tcp
eshop-db            Up 2 minutes    0.0.0.0:3306->3306/tcp
eshop-adminer       Up 2 minutes    0.0.0.0:8080->8080/tcp
```

### Test de connexion

```bash
# Dans votre terminal
curl http://localhost:8000

# Ou allez à http://localhost:8000
# Vous devriez voir la page d'accueil eShop
```

### Santé MySQL

```bash
docker-compose exec db mysqladmin ping -h localhost
```

Output: `mysqld is alive`

---

## 🐛 Debugging

### Le conteneur démarre mais l'app ne répond pas

```bash
# Vérifier les logs
docker-compose logs app

# Entrer dans le conteneur
docker-compose exec app bash
cd /app
ls -la
php -v
```

### Erreur "Cannot connect to database"

1. Vérifier que MySQL est prêt:
```bash
docker-compose logs db | grep "ready for connections"
```

2. Tester la connexion:
```bash
docker-compose exec app \
  php -r \
  "mysqli_connect('db', 'eshop_user', 'eshop_password', 'eshop_db') or die('Connection failed');"
```

### Port déjà utilisé (8000 ou 3306)

```bash
# Option 1: Changer le port dans docker-compose.yml
ports:
  - "9000:8000"  # Utiliser 9000 au lieu de 8000

# Option 2: Tuer le processus utilisant le port
# Sur Windows:
netstat -ano | findstr :8000
taskkill /PID <PID> /F

# Sur Mac/Linux:
lsof -i :8000
kill -9 <PID>
```

### Fichiers non synchronisés entre local et container

```bash
# Redémarrer le service
docker-compose restart app

# Ou reconstruire
docker-compose up -d --build
```

---

## 🔄 Workflows courants

### Ajouter une dépendance PHP

```bash
# À partir de votre machine locale (ou dans le container)
docker-compose exec app composer require monolog/monolog

# Le vendoordirectory se mettra à jour automatiquement
```

### Migrer la base de données

```bash
docker-compose exec app php index.php migrate
```

### Importer une base SQL initiale

```bash
# Avant le premier docker-compose up:
# Les fichiers .sql dans /docker-entrypoint-initdb.d/ s'exécutent automatiquement
# Ou manuellement:
docker-compose exec -T db mysql -u eshop_user -p eshop_db < eshop_vendor.sql
```

---

## 📦 Passer à Railway

Une fois que tout fonctionne en Docker local:

```bash
# Commitez vos changements
git add .
git commit -m "feat: configuration Docker & Railway"

# Pushez vers votre repo
git push origin main

# Sur Railway:
# 1. Créez un nouveau projet
# 2. Connectez votre repository GitHub
# 3. Railway détecte automatiquement le Dockerfile
# 4. Configurez DATABASE_URL dans Variables
# 5. Déployez!
```

---

## ⚙️ Configuration avancée

### Ajouter un service Redis (cache)

```yaml
# Dans docker-compose.yml
redis:
  image: redis:7-alpine
  ports:
    - "6379:6379"
  networks:
    - eshop-network
```

### Ajouter un service ElasticSearch (recherche)

```yaml
elasticsearch:
  image: docker.elastic.co/elasticsearch/elasticsearch:8.0.0
  environment:
    - discovery.type=single-node
  ports:
    - "9200:9200"
  networks:
    - eshop-network
```

### Limiter les ressources

```yaml
# Dans docker-compose.yml > services > app
resources:
  limits:
    cpus: '1'
    memory: 512M
  reservations:
    cpus: '0.5'
    memory: 256M
```

---

## 📞 Support

- [Documentation Docker](https://docs.docker.com/)
- [Documentation Docker Compose](https://docs.docker.com/compose/)
- [Railway Documentation](https://docs.railway.app/)

---

**Conseil**: Utilisez Docker pour développer localement exactement comme en production (Railway). Cela élimine les surprises au déploiement ! 🚀

---

**Besoin d'aide ?** Consultez:
- [RAILWAY.md](RAILWAY.md) - Déploiement sur Railway
- [SETUP.md](SETUP.md) - Configuration développement local (sans Docker)
