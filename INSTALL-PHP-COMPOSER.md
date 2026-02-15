# ⚙️ Installation de PHP et Composer

## 🔴 Erreur actuelle
```
composer : Le terme «composer» n'est pas reconnu
php : Le terme «php» n'est pas reconnu
```

**Cause**: PHP et Composer ne sont pas dans le PATH Windows

---

## ✅ Solution : 3 Options

### **Option 1: Installation RAPIDE avec Installer Composer (Recommandé)**

#### Étape 1 : Télécharger Composer Installer pour Windows
1. Ouvrez https://getcomposer.org/download/
2. Cliquez sur "**Composer-Setup.exe**" (le dernier)
3. Téléchargez et exécutez l'installation

#### Étape 2 : Trouver votre PHP
L'installeur Composer cherchera automatiquement PHP sur votre système.

**Si Composer trouve PHP**: ✅ Installation terminée!
**Si Composer dit "No PHP found"**: → Installez d'abord PHP.php

#### Étape 3 : Vérifier l'installation
```powershell
composer --version
php -v
```

---

### **Option 2: Installation PHP via Chocolatey (Recommandé si pas d'autres stacks)**

#### Prérequis: Chocolatey installé?
```powershell
choco --version
```

Si oui, continuer. Sinon, ouvrir PowerShell **EN ADMINISTRATEUR** et:
```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
```

#### Installer PHP avec Chocolatey
```powershell
choco install php
```

#### Installer Composer avec Chocolatey
```powershell
choco install composer
```

#### Tester
```powershell
php -v
composer --version
```

---

### **Option 3: Installer XAMPP (Si vous voulez un serveur web aussi)**

#### Étape 1 : Télécharger XAMPP
1. Allez sur https://www.apachefriends.org/download.html
2. Téléchargez XAMPP PHP 8.2+ (ou 8.3)
3. Installez dans `C:\xampp`

#### Étape 2 : Ajouter PHP au PATH Windows
1. Ouvrir Paramètres Windows
2. Cherchez "Variables d'environnement"
3. Cliquez "Variables d'environnement"
4. Sous "Variables utilisateur", créer nouvelle:
   - **Nom**: `PATH`
   - **Valeur**: `C:\xampp\php`

   Ou ajouter à PATH existant:
   - Sélectionner `PATH` existante
   - Cliquer "Modifier"
   - Ajouter `;C:\xampp\php` à la fin

#### Étape 3 : Installer Composer
```powershell
# Télécharger composer.phar
$url = "https://getcomposer.org/composer.phar"
$output = "C:\xampp\php\composer.phar"
(New-Object System.Net.WebClient).DownloadFile($url, $output)

# Créer un script batch pour composer
@"
@php C:\xampp\php\composer.phar %*
"@ | Out-File -FilePath "C:\xampp\php\composer.bat" -Encoding ASCII
```

#### Étape 4 : Ajouter composer au PATH
Ajouter `C:\xampp\php` au PATH (même que ci-dessus)

#### Étape 5 : Tester
```powershell
php -v
composer --version
```

---

## 🚀 Après Installation: Utiliser Composer

### Tester la configuration
```powershell
composer install
php test-dependencies.php
```

### Créer .env localement
```powershell
copy .env.example .env
# Editer .env avec vos valeurs locales
```

---

## 🆘 Si ça ne fonctionne pas

### ❌ "PHP not found" après installation
1. Fermer et rouvrir PowerShell (ou CMD)
2. Tester: `php -v`
3. Si encore erreur, redémarrer Windows

### ❌ "Composer not found" après installation
1. Fermer et rouvrir PowerShell
2. Tester: `composer --version`
3. Vérifier que PATH contient le dossier PHP

### ❌ "Permission denied" sur l'installation de Composer
1. Ouvrir PowerShell **EN ADMINISTRATEUR**
2. Relancer la commande Chocolatey

### ❌ "XAMPP déjà installé?" 
Vérifier:
- Chercher dans `C:\xampp`
- Ou où avez-vous installé XAMPP?
- Ajouter ce chemin au PATH (même procédure que ci-dessus)

---

## 📋 Vérification Finale (Après Installation)

```powershell
# Devrait afficher version PHP (8.2+)
php -v

# Devrait afficher version Composer
composer --version

# Devrait montrer toutes les extensions
php -m

# Cherchez ces extensions dans la liste:
# - cURL
# - mbstring
# - PDO
# - Zlib
# - Openssl
```

---

## 💡 Pour le Projet eShop

Après installation PHP et Composer:

```powershell
# 1. Installer les dépendances
composer install

# 2. Créer configuration locale
copy .env.example .env

# 3. Éditer .env avec votre config
# ENVIRONMENT=development
# MYSQLHOST=localhost
# MYSQLUSER=root
# MYSQLPASSWORD=xxx
# MYSQLDATABASE=eshop_db

# 4. Chercher application/config/database.php
# 5. Vérifier que dataapplication/db.php charge la config

# 6. Tester
php test-dependencies.php
php test-logging.php
```

---

## ⏱️ Temps estimé

| Option | Temps | Complexité |
|--------|-------|-----------|
| Composer Installer | 5-10 min | ⭐ Facile |
| Chocolatey | 10-15 min | ⭐⭐ Moyen |
| XAMPP | 15-20 min | ⭐⭐⭐ Plus complexe |

**Recommandation**: Option 1 (Composer Installer) → Plus simple et rapide

---

## 🎯 Prochaines Étapes Une Fois PHP Installé

```bash
# 1. Composer install
composer install

# 2. Tester le setup
php test-dependencies.php

# 3. Configurer localement
copy .env.example .env

# 4. Éditer .env
notepad .env

# 5. Vérifier le logging
php test-logging.php

# 6. Lancer le serveur local (Option)
composer serve
# Ou: php -S localhost:8000 -t public/
```

Vous êtes prêt! 🚀
