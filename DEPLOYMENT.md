# 📦 Fichiers de déploiement Docker/Railway

Cette documentation explique les fichiers de configuration Docker et Railway.

---

## 📋 Fichiers créés

### 1. **Dockerfile** - Image de conteneur

```dockerfile
FROM php:8.3-cli
```

- Utilise **PHP 8.3** (dernière LTS stable)
- Installe les extensions MySQL (`mysqli`, `pdo_mysql`)
- Exécute `composer install --no-dev` durant le build
- Démarre le serveur: `php -S 0.0.0.0:${PORT} -t public/`

**Utilisé par:** Railway, Docker, docker-compose

---

### 2. **railway.json** - Configuration Railway

```json
{
  "build": {
    "builder": "dockerfile"
  },
  "deploy": {
    "startCommand": "php -S 0.0.0.0:${PORT} -t public/"
  }
}
```

**Fonction:**
- Configure Railway pour utiliser le Dockerfile
- Définit la commande de démarrage
- Configure les variables d'environnement par défaut
- Active le healthcheck

**Utilisé par:** Railway uniquement

---

### 3. **docker-compose.yml** - Développement local

Services:
- **app**: Application PHP + Composer
- **db**: MySQL 8.0
- **adminer**: Interface phpMyAdmin

**Commande:**
```bash
docker-compose up -d
```

**Accès:**
- App: http://localhost:8000
- phpMyAdmin: http://localhost:8080

---

### 4. **.env.docker** - Configuration Docker

Variables pour les tests locaux:
```ini
DATABASE_URL=mysql://eshop_user:eshop_password@db:3306/eshop_db
MYSQLHOST=db
APP_DEBUG=true
```

**Utilisation:**
```bash
cp .env.docker .env
docker-compose up -d
```

---

### 5. **compose.production.yml** - Production

Version de production de docker-compose:
- Redémarrage automatique
- Limites de ressources (CPU, RAM)
- Healthcheck renforcé
- Volume persistant MySQL

**Commande:**
```bash
docker-compose -f compose.production.yml up -d
```

---

### 6. **.dockerignore** - Optimisation de build

Fichiers ignorés lors du `COPY` dans Dockerfile:
- `vendor/` (sera regénéré par composer)
- Logs, cache, uploads
- `.git`, `node_modules`
- Documentation

**Réduit la taille du build de ~90%**

---

## 🔄 Flux de déploiement

```
┌─────────────────────────────────────────────────────┐
│          Développement Local                        │
│                                                      │
│  docker-compose.yml + .env.docker                   │
│  → mysql:8.0 + php:8.3 + Composer                   │
│  → http://localhost:8000                            │
└────────────────┬────────────────────────────────────┘
                 │
                 │ git push
                 │
┌────────────────▼────────────────────────────────────┐
│          Railway.app                                │
│                                                      │
│  Dockerfile + railway.json                          │
│  → Détecte Dockerfile                              │
│  → Build: composer install --no-dev                │
│  → Deploy: php -S 0.0.0.0:$PORT -t public/         │
│  → https://yourdomain.railway.app                   │
└─────────────────────────────────────────────────────┘
```

---

## 🛠️ Commandes de déploiement

### Développement (docker-compose)

```bash
# Démarrer
docker-compose up -d

# Logs
docker-compose logs -f app

# Arrêter
docker-compose down
```

### Staging/Production (docker-compose.production.yml)

```bash
# Démarrer
docker-compose -f compose.production.yml up -d

# Logs
docker-compose -f compose.production.yml logs -f app

# Arrêter
docker-compose -f compose.production.yml down
```

### Railway

```bash
# 1. Commitez
git add .
git commit -m "feat: Docker & Railway"

# 2. Pushez
git push origin main

# 3. Railway déploie automatiquement
# (Voir https://logs.railway.app)
```

---

## ⏱️ Temps de déploiement

| Étape | Durée | Notes |
|-------|-------|-------|
| Build Docker | 2-5 min | Télécharge PHP 8.3, installe composer, packages |
| Composer install | 1-3 min | Dépend de la taille des packages |
| Deploy | 30-60 sec | Railway lance le serveur |
| **Total** | **3-9 min** | Première fois, puis ~2-3 min après |

---

## 🔒 Sécurité

### Dockerfile

- ✅ PHP officiel (maintenu par Docker)
- ✅ `composer install --no-dev` (retire les dépendances de test)
- ✅ Crée répertoires `storage/` avec permissions correctes
- ⚠️ Sans reverse proxy (ajouter nginx pour production)

### Variables d'environnement

- ✅ `.env` git-ignored
- ✅ Stockées dans Railway Variables
- ✅ Jamais loggées (conf.php log:false)

### Chemins sensibles

- ✅ `/app/src/Config/` hors webroot
- ✅ `/app/storage/` hors webroot
- ✅ Seul `/app/public/` est accessible

---

## 📊 Architecture réseaux Docker

```
┌──────────────────────────────────────┐
│      docker-compose network          │
│      (eshop-network)                 │
│                                       │
│  ┌─────────────┐  ┌──────────────┐  │
│  │   app:8000  │◄─┤  db:3306     │  │
│  │  (Port sur  │  │  (MySQL)     │  │
│  │  8000 host) │  │  Interne     │  │
│  └─────────────┘  └──────────────┘  │
│       ▲                               │
│       │ adminer:8080                  │
│       │ (phpMyAdmin)                  │
│  ┌────▼──────────┐                   │
│  │  adminer:8080 │                   │
│  │  (Port 8080)  │                   │
│  └───────────────┘                   │
│                                       │
└──────────────────────────────────────┘
```

Réseau: `eshop-network` (bridge)
- Services peut se communiquer par nom (app → db)
- Isolé du réseau hôte

---

## 🚨 Troubleshooting

### Build Docker échoue

```bash
docker-compose build --no-cache --progress=plain
# Affiche les erreurs détaillées
```

### MySQL ne démarre pas

```bash
docker-compose logs db
# Chercher "Can't initialize InnoDB"
# → Volume corrompu: docker-compose down -v
```

### Port 8000 déjà utilisé

```bash
# Dans docker-compose.yml, changer:
ports:
  - "9000:8000"  # Puis accédez à localhost:9000
```

---

## 📚 Références

- [Docker PHP Official Images](https://hub.docker.com/_/php)
- [Docker Compose Version](https://docs.docker.com/compose/compose-file/)
- [Railway Dockerfile Deployments](https://docs.railway.app/deploy/builds)
- [PHP 8.3 Release Notes](https://www.php.net/releases/8.3/)

---

**Besoin d'aide ?**
- [DOCKER.md](DOCKER.md) - Guide utilisation Docker
- [RAILWAY.md](RAILWAY.md) - Déploiement Railway
- [SETUP.md](SETUP.md) - Développement sans Docker
