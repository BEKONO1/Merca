# 📦 Dépendances Composer - eShop Railway

Documentation des dépendances PHP requises et comment les gérer.

---

## 🎯 Vue d'ensemble

Le `composer.json` a été mis à jour avec **toutes les extensions PHP nécessaires** pour garantir la compatibilité avec Railway et environnements modernes.

---

## 📋 Extensions PHP requises

### Dépendances principales

```json
{
  "require": {
    "php": "^8.2",                    // PHP 8.2 ou supérieur
    "jaysparmar/codeigniter3": "1.0.9",
    "vlucas/phpdotenv": "^5.6"        // Charger .env
  }
}
```

### Extensions système (ext-*)

```json
{
  "ext-curl": "*",           // Requêtes HTTP
  "ext-pdo": "*",            // Abstraction Base de données
  "ext-pdo_mysql": "*",      // Connecteur MySQL spécifique
  "ext-mbstring": "*",       // Strings multibyte (UTF-8, emoji)
  "ext-gd": "*",             // Manipulation images (catalogues)
  "ext-json": "*",           // JSON encode/decode (APIs)
  "ext-openssl": "*",        // SSL/TLS pour HTTPS
  "ext-zip": "*"             // Compression fichiers (imports)
}
```

---

## 🧩 Détail des extensions

### PDO MySQL

```json
"ext-pdo": "*",
"ext-pdo_mysql": "*"
```

**Usage**: Connexion à la base de données MySQL  
**CodeIgniter**: Utilise `mysqli` driver  
**Railway**: Automatiquement disponible  

### Multibyte String (mbstring)

```json
"ext-mbstring": "*"
```

**Pourquoi**: Manipulation strings UTF-8 (accents, émojis, caractères spéciaux)  
**Utilité**: 
- Noms produits en plusieurs langues
- Descriptions avec caractères spéciaux
- Support emojis 🎁

### GD Library (gd)

```json
"ext-gd": "*"
```

**Pourquoi**: Manipulation images  
**Utilité**:
- Génération thumbnails produits
- Watermarks
- Redimensionnement images
- Compression

### cURL

```json
"ext-curl": "*"
```

**Pourquoi**: Requêtes HTTP externes  
**Utilité**:
- Intégration PayPal
- Intégration Flutterwave
- APIs externes
- Webhooks

### JSON

```json
"ext-json": "*"
```

**Pourquoi**: Encoding/decoding JSON  
**Utilisé pour**:
- APIs REST
- Configuration (language_example.json)
- Réponses JSON

### OpenSSL

```json
"ext-openssl": "*"
```

**Pourquoi**: Sécurité HTTPS  
**Utilisé pour**:
- Certificats SSL/TLS
- Paiements sécurisés
- Tokens JWT

### ZIP

```json
"ext-zip": "*"
```

**Pourquoi**: Compression/décompression  
**Utilisé pour**:
- Import fichiers bulk (CSV → ZIP)
- Export catalogues
- Téléchargements en masse

---

## 🆕 Nouvelle dépendance: phpdotenv

### Qu'est-ce que c'est?

```json
"vlucas/phpdotenv": "^5.6"
```

**Package**: `vlucas/phpdotenv` par Vance Lucas  
**Fonction**: Charger des variables d'environnement depuis le fichier `.env`  
**Lien**: https://github.com/vlucas/phpdotenv

### Installation automatique

```bash
# Déjà dans composer.json, donc:
composer install

# phpdotenv sera téléchargé automatiquement
```

### Comment ça marche

**Dans [src/Core/Bootstrap.php](../src/Core/Bootstrap.php):**

```php
// Chargement automatique de .env
if (class_exists('Dotenv\Dotenv')) {
    $dotenv = \Dotenv\Dotenv::createImmutable(BASEPATH);
    $dotenv->safeLoad();  // ← Charge les variables .env
    error_log('[BOOTSTRAP] .env loaded via phpdotenv');
}
```

**Résultat**:
- Toutes les variables du `.env` sont disponibles via
  - `getenv('VAR_NAME')`
  - `$_ENV['VAR_NAME']`
  - `$_SERVER['VAR_NAME']` (certaines)

### Exemple d'utilisation

**.env:**
```ini
DATABASE_URL=mysql://user:pass@localhost:3306/db
APP_DEBUG=true
JWT_SECRET=my-secret-key
```

**Dans le code:**
```php
$db_url = getenv('DATABASE_URL');     // ← Variables chargées
$app_debug = getenv('APP_DEBUG');

if ($app_debug === 'true') {
    // Mode débogage
}
```

---

## 🚀 Installation des dépendances

### 1. Installation initiale

```bash
composer install

# Télécharge:
# - jaysparmar/codeigniter3 v1.0.9
# - vlucas/phpdotenv v5.6
# - Toutes les dépendances transitives
```

### 2. Mise à jour

```bash
# Mettre à jour les packages
composer update

# Ou spécifiquement phpdotenv
composer update vlucas/phpdotenv
```

### 3. Vérifier l'installation

```bash
composer show

# Affiche tous les packages installés:
# jaysparmar/codeigniter3  1.0.9  CodeIgniter 3 PSR-4
# vlucas/phpdotenv        5.6.1  Loads environment variables
# symfony/polyfill-ctype  v1.28  Symfony polyfills...
# ... etc
```

---

## 📋 Checklist - Composer.json

- [x] PHP 8.2+ requis (codeigniter3 1.0.9 le supporte)
- [x] `ext-pdo_mysql` pour la base de données
- [x] `ext-mbstring` pour UTF-8 et emojis
- [x] `ext-gd` pour les images
- [x] `ext-curl` pour les APIs externes
- [x] `ext-json` pour les APIs REST
- [x] `ext-openssl` pour HTTPS/SSL
- [x] `ext-zip` pour compression
- [x] `vlucas/phpdotenv` pour charger .env
- [x] Section `scripts` pour commands

---

## 🔧 Docker & Railway

### Dockerfile

Le Dockerfile inclut **toutes ces extensions**:

```dockerfile
RUN docker-php-ext-install \
    mysqli \
    pdo \
    pdo_mysql \
    gd \
    mbstring \
    zip \
    && docker-php-ext-enable ...
```

### Railway

Railway détecte automatiquement via `composer.json`:
- Quelles extensions PHP installer
- Quelle version de PHP utiliser

**Aucune configuration supplémentaire requise !** ✅

---

## 🧪 Vérifier que tout fonctionne

### List des extensions disponibles

```bash
php -m

# Affiche:
# [PHP Modules]
# Core
# curl          ✅
# gd            ✅
# json          ✅
# mbstring      ✅
# openssl       ✅
# pdo           ✅
# pdo_mysql     ✅
# zip           ✅
# [Zend Modules]
```

### Test des dépendances

```bash
# Vérifier si phpdotenv fonctionne
php -r "require 'vendor/autoload.php'; var_dump(class_exists('Dotenv\Dotenv'));"
# Output: bool(true) ✅

# Vérifier GD
php -r "var_dump(extension_loaded('gd'));"
# Output: bool(true) ✅
```

### Test complet

```php
<?php
// test-dependencies.php
require 'vendor/autoload.php';

echo "✅ Composer autoload OK\n";
echo "✅ Dotenv: " . (class_exists('Dotenv\Dotenv') ? 'YES' : 'NO') . "\n";

$extensions = ['curl', 'pdo', 'pdo_mysql', 'mbstring', 'gd', 'json', 'openssl', 'zip'];
foreach ($extensions as $ext) {
    echo "✅ ext-$ext: " . (extension_loaded($ext) ? 'YES' : 'NO') . "\n";
}
```

---

## 📋 Pour Railway

### Variables dans compose.production.yml

Aucune config nécessaire! Railway:

1. Lit le `composer.json`
2. Détecte les extensions requises
3. Les installe automatiquement dans le Dockerfile
4. Tout est prêt ✅

### Vérifier sur Railway

Dans les **Logs de déploiement**:

```
Step 1/20 : FROM php:8.3-cli
Step 5/20 : RUN docker-php-ext-install mysqli pdo pdo_mysql ...
 ---> Running in abc123...
  ✅ Extension mysqli enabled
  ✅ Extension pdo enabled
  ✅ Extension pdo_mysql enabled
  ...
Successfully built abc123def456
```

---

## 🎯 À faire après Composer update

1. ✅ Exécuter `composer install`
2. ✅ Vérifier `vendor/autoload.php` existe
3. ✅ Vérifier extensions dans `php -m`
4. ✅ Tester `.env` chargé: `php test-dependencies.php`
5. ✅ Committer `composer.lock`

### Committer le lock file?

**OUI !** 😍

```bash
git add composer.json composer.lock
git commit -m "update: composer dependencies with phpdotenv"
git push
```

`composer.lock` assure que **tout le monde** utilise les **mêmes versions** précises.

---

## 🆘 Troubleshooting

### Error: "Class Dotenv\Dotenv not found"

**Cause**: phpdotenv non installé

**Solution**:
```bash
composer install
# Ou:
composer update vlucas/phpdotenv
```

### Error: "ext-gd not found"

**Cause**: Extension GD non installée sur le système

**Solution (local)**:
```bash
# Ubuntu/Debian:
sudo apt-get install php8.3-gd

# macOS (Homebrew):
brew install php@8.3
brew install php8.3-gd

# Windows: Voir php.ini ou réinstaller PHP
```

### Error: "Cannot require non-existent file vendor/autoload.php"

**Cause**: Composer pas exécuté

**Solution**:
```bash
composer install --no-dev  # Sans dev dependencies
# Ou:
composer install           # Avec dev dependencies
```

---

## 📚 Ressources

- [Composer Documentation](https://getcomposer.org/doc/)
- [vlucas/phpdotenv](https://github.com/vlucas/phpdotenv)
- [PHP Extensions](https://www.php.net/manual/en/extensions.php)
- [Railway PHP Guide](https://docs.railway.app/languages/php)

---

## ✅ Résumé

✅ **composer.json** mis à jour avec toutes les extensions  
✅ **phpdotenv** ajouté pour charger `.env`  
✅ **Bootstrap** intégré pour auto-charger `.env`  
✅ **Railway** détectera automatiquement les extensions  
✅ **Tout fonctionnera out-of-the-box** 🚀

---

**Prêt pour le déploiement !**

Commandes finales:
```bash
composer install
git add composer.json composer.lock
git commit -m "chore: add phpdotenv and php extensions"
git push
```

Railway dépliera avec les bonnes extensions ✅
