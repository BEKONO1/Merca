# 🚂 Configuration eShop pour Railway

Ce guide explique comment déployer votre application eShop sur [Railway.app](https://railway.app/).

## 📋 Prérequis

- Compte Railway ([Inscription gratuite](https://railway.app/))
- Git installé
- Accès aux credentials de votre repository (GitHub, GitLab, Bitbucket)

---

## 🚀 Déploiement sur Railway

### 1. Préparation du repository

```bash
# Assurez-vous que votre code est dans Git
git add .
git commit -m "feat: préparation pour Railway"
git push origin main
```

### 2. Créer un projet Railway

1. Accédez à [Railway.app](https://railway.app/)
2. Cliquez sur **"New Project"**
3. Sélectionnez **"GitHub"** (ou votre provider Git)
4. Autorisez Railway à accéder à vos repositories
5. Sélectionnez le repository `boutique`
6. Rails va détecter automatiquement PHP - configurez le déploiement

### 3. Ajouter une Base de Données MySQL

Dans votre projet Railway :

1. Cliquez sur **"+ Add Service"**
2. Cherchez **"MySQL"**
3. Sélectionnez la version (8.0 recommandé)
4. Railway créera automatiquement les identifiants

---

## 🔐 Variables d'environnement Railway

Railway fournit automatiquement une variable `DATABASE_URL` au format :
```
mysql://username:password@host:port/database
```

### Configuration automatique

Notre fichier `src/Config/database.php` détecte automatiquement `DATABASE_URL` et l'utilise.

**Aucune configuration supplémentaire nécessaire pour la base de données !**

### Variables optionnelles à ajouter

Dans Railway, accédez à **Variables**:

```
APP_ENV=production
APP_DEBUG=false
ENVIRONMENT=production
JWT_SECRET=your-super-secret-key-change-me
MAIL_DRIVER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
```

---

## 📂 Structure de répertoires

La nouvelle structure requiert :

```
public/
  ├── index.php           ← Point d'entrée (Railway pointe ici)
  ├── assets/
  │   ├── css/
  │   ├── js/
  │   └── uploads/
  └── .htaccess

src/
  ├── Config/
  │   └── database.php    ← Configuration Railway-ready
  ├── Application/
  │   ├── Controllers/
  │   ├── Models/
  │   └── Views/
  └── ...

.env                      ← Secrets (git-ignored)
.env.example              ← Template
```

---

## 🔧 Configuration du serveur

### Nginx (Railway utilise Nginx par défaut)

Créez un fichier `nginx.conf` à la racine :

```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

location ~ \.php$ {
    fastcgi_pass php:9000;
    fastcgi_index index.php;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    fastcgi_param PATH_INFO $fastcgi_path_info;
}
```

### .htaccess (Apache)

Si vous utilisez Apache, assurez-vous que ce fichier existe dans `/public/.htaccess` :

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /
    
    # Bloquer l'accès aux fichiers sensibles
    RewriteRule ^(\.env|composer\.|vendor|storage) - [F,L]
    
    # Rediriger toward index.php
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php?/$1 [QSA,L]
</IfModule>
```

---

## 🔑 Secrets sensibles

### Ne JAMAIS committer ces fichiers :

- `.env` (Configuration locale)
- `vendor/` (Dépendances PHP)
- `node_modules/` (Dépendances Node)
- `storage/logs/` (Logs applicatifs)
- `public/uploads/` (Fichiers utilisateurs)

Tous ces chemins sont dans `.gitignore`.

### Gérer les secrets sur Railway

1. Accédez à **Variables** dans votre projet Railway
2. Ajoutez chaque secret individuellement
3. Railway les injecte comme variables d'environnement

---

## 📊 Hierarchie des variables d'environnement

Notre configuration utilise cette priorité :

### 1️⃣ **DATABASE_URL** (Railway Priority)
```php
DATABASE_URL=mysql://user:password@host:port/db
```
✅ Utilisée automatiquement par Railway

### 2️⃣ **Variables individuelles** (Fallback local)
```ini
MYSQLHOST=localhost
MYSQLPORT=3306
MYSQLUSER=root
MYSQLPASSWORD=
MYSQLDATABASE=eshop_db
```
✅ Utilisées en développement local

---

## 🛡️ Sécurité

### Checklist avant déploiement

- [ ] `.env` ne contient **PAS** les secrets réels
- [ ] Tous les secrets sont dans **Variables Railway**
- [ ] `.gitignore` est à jour
- [ ] `display_errors` est `OFF` en production
- [ ] HTTPS est activé (Railway le fait automatiquement)
- [ ] Les headers de sécurité sont configurés

### Headers recommandés

Dans votre app, ajoutez :

```php
// En production uniquement
if (ENVIRONMENT === 'production') {
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: SAMEORIGIN');
    header('X-XSS-Protection: 1; mode=block');
    header('Referrer-Policy: strict-origin-when-cross-origin');
}
```

---

## 🔍 Vérifier les logs

Dans Railway :

1. Allez dans **Deployments**
2. Cliquez sur votre déploiement
3. Ouvrez l'onglet **Logs**

Recherchez les erreurs de base de données :
```
[DB] DATABASE_URL utilisée - Mode Railway/Cloud
```

---

## 🚨 Troubleshooting

### Erreur: "Cannot connect to database"

**Vérifier :**
```bash
# Dans Railway Logs:
- DATABASE_URL est présente
- Format: mysql://user:pass@host:port/db
- Pas d'espaces ou caractères spéciaux mal échappés
```

### Erreur: "Permission denied for application/config"

**Solution :** Déplacez le fichier vers `src/Config/database.php` (hors du webroot).

### Logs non visibles

Vérifiez que le répertoire `storage/logs/` existe et est accessible en écriture.

---

## 📞 Support

- [Documentation Railway](https://docs.railway.app/)
- [Railway Support Community](https://discord.gg/railway)
- [CodeIgniter 3 Docs](https://codeigniter.com/user_guide/)

---

**Dernière mise à jour:** février 2026
