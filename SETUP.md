# ⚙️ Guide de Configuration - Développement Local

Ce guide explique comment configurer eShop pour le développement local avec la nouvelle structure.

---

## 1️⃣ Configuration initiale

### Créer le fichier `.env`

Copiez le template :

```bash
cp .env.example .env
```

### Éditer `.env` pour le développement local

```ini
APP_ENV=development
APP_DEBUG=true
ENVIRONMENT=development

# Configuration locale MySQL
MYSQLHOST=localhost
MYSQLPORT=3306
MYSQLUSER=root
MYSQLPASSWORD=              # Laisser vide pour XAMPP/WAMP
MYSQLDATABASE=eshop_db      # Créer cette base avant

# Développement
APP_URL=http://localhost:8000
JWT_SECRET=dev-secret-key-not-secure
```

---

## 2️⃣ Installation des dépendances

### PHP Composer

```bash
# Installer les dépendances PHP
composer install

# Ou si vous avez déjà vendor/:
composer update
```

### Node/NPM (si assets frontend)

```bash
npm install
```

---

## 3️⃣ Configuration de la base de données

### Créer la base de données

```sql
CREATE DATABASE eshop_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Importer les migrations/SQL

```bash
# Si vous avez un fichier SQL initial
mysql -u root eshop_db < eshop_vendor.sql
```

### Exécuter les migrations CodeIgniter

```bash
# À la racine du projet
php index.php migrate
```

---

## 4️⃣ Structure des répertoires

Assurez-vous que la nouvelle structure existe :

```
c:\Users\Antoine\Music\boutique\admin\Code v3.2.0\
├── public/               # ← Racine web publique
│   ├── index.php
│   ├── .htaccess/.
│   ├── assets/
│   │   ├── css/
│   │   ├── js/
│   │   └── uploads/
│   └── ...
│
├── src/                  # ← Code applicatif (privé)
│   ├── Config/
│   │   ├── database.php
│   │   ├── app.php
│   │   └── ...
│   ├── Application/
│   │   ├── Controllers/
│   │   ├── Models/
│   │   └── Views/
│   └── ...
│
├── storage/              # ← Fichiers générés
│   ├── logs/
│   ├── cache/
│   └── sessions/
│
├── .env                  # ← Config locale (git-ignored)
├── .env.example          # ← Template
└── RAILWAY.md            # ← Guide Railway
```

---

## 5️⃣ Lancer l'application en local

### Option 1: Serveur PHP intégré (Recommandé pour dev)

```bash
# À la racine du projet
php -S localhost:8000 -t public/

# Puis accédez à: http://localhost:8000
```

### Option 2: XAMPP/WAMP

1. Placer le projet dans `htdocs/` ou `www/`
2. Accéder via: `http://localhost/Code%20v3.2.0/public/`
3. Configurer le `DocumentRoot` du serveur Apache vers `/public/`

### Option 3: Docker (Optionnel)

```bash
docker-compose up
# http://localhost:8000
```

---

## 🔍 Vérification

### Test de connexion à la base de données

Créez un fichier temporaire `/public/test-db.php` :

```php
<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../src/Config/database.php';

try {
    $conn = new mysqli(
        $db['default']['hostname'],
        $db['default']['username'],
        $db['default']['password'],
        $db['default']['database'],
        $db['default']['port']
    );

    if ($conn->connect_error) {
        die('Erreur de connexion: ' . $conn->connect_error);
    }

    echo '✅ Connexion à la base de données réussie !';
    echo '<br>Base: ' . $db['default']['database'];
    echo '<br>Host: ' . $db['default']['hostname'];

    $conn->close();
} catch (Exception $e) {
    echo '❌ Erreur: ' . $e->getMessage();
}
```

Accédez via: `http://localhost:8000/test-db.php`

---

## 📝 Variables d'environnement supportées

| Variable | Défaut | Description |
|----------|--------|-------------|
| `APP_ENV` | `development` | development \| production |
| `APP_DEBUG` | `true` | Afficher les erreurs détaillées |
| `DATABASE_URL` | — | DSN complet (priorité) |
| `MYSQLHOST` | `localhost` | Serveur MySQL (fallback) |
| `MYSQLPORT` | `3306` | Port MySQL |
| `MYSQLUSER` | `root` | Utilisateur MySQL |
| `MYSQLPASSWORD` | — | Mot de passe MySQL |
| `MYSQLDATABASE` | `eshop_db` | Nom de la base |
| `APP_URL` | `http://localhost` | URL de l'app |

---

## 🛠️ Commandes utiles

### Vider le cache

```bash
# Cache CodeIgniter
rm -rf storage/cache/*

# Cache navigateur (header)
# Voir dans le contrôleur: header('Cache-Control: no-cache');
```

### Lire les logs

```bash
# Logs d'erreurs
tail -f storage/logs/*

# Ou sur Windows:
type storage\logs\*.php
```

### Débogage

Activer le débogage en local **seulement** :

```ini
# .env
APP_DEBUG=true
ENVIRONMENT=development
```

Utilisez `error_log()` pour les logs :

```php
error_log('Mon message de débogage');
// Voir dans: storage/logs/php-error.log
```

---

## ⚠️ Attention

### PAS pour la production

- ❌ `APP_DEBUG=true`
- ❌ `ENVIRONMENT=development`
- ❌ `.env` commité (use Variables Railway)
- ❌ Erreurs visibles au client

### Avant tout déploiement

```bash
# Vérifier .gitignore
git status

# Doit afficher ".env" comme ignoré
# Ne pas voir: vendor/, node_modules/, storage/logs/

# Lancer les tests
npm test
composer test
```

---

## 🚀 Prêt pour le développement !

Vous pouvez maintenant :

✅ Modifier les contrôleurs dans `src/Application/Controllers/`  
✅ Ajouter des modèles dans `src/Application/Models/`  
✅ Éditer les vues dans `src/Application/Views/`  
✅ Ajouter des assets dans `public/assets/`  

Tous les fichiers sensibles (`config/`, `.env`, etc.) sont **protégés** du webroot public.

---

**Besoin d'aide ?** Consultez [RAILWAY.md](RAILWAY.md) pour le déploiement.
