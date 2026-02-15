@echo off
REM Script pour installer PHP et ajouter Composer au PATH sur Windows
REM Exécutez ce script EN ADMINISTRATEUR

setlocal enabledelayedexpansion

echo ╔═══════════════════════════════════════════════════════════════╗
echo ║                                                               ║
echo ║  Configuration Rapide: PHP + Composer pour Windows           ║
echo ║                                                               ║
echo ╚═══════════════════════════════════════════════════════════════╝
echo.

REM Check if running as administrator
net session >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ ERREUR: Ce script doit être exécuté EN ADMINISTRATEUR
    echo.
    echo Solution:
    echo 1. Ouvrir Invite de Commandes (cmd) EN ADMINISTRATEUR
    echo 2. Naviguer vers ce dossier
    echo 3. Exécuter: install-php-win.bat
    echo.
    pause
    exit /b 1
)

echo ✅ ScriptExecuté en tant qu'administrateur
echo.

REM Option 1: Check if Chocolatey is installed
echo Vérification de Chocolatey...
choco --version >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ Chocolatey trouvé!
    echo.
    echo Voulez-vous installer PHP avec Chocolatey?
    echo Appuyez sur Y pour OUI, N pour NON
    choice /C YN /M "Installer PHP maintenant"
    if !errorlevel! equ 1 (
        echo.
        echo Installation de PHP...
        choco install php -y
        echo.
        echo Installation de Composer...
        choco install composer -y
        echo.
        echo ✅ PHP et Composer installés!
        echo.
        echo Redémarrez votre PowerShell/CMD pour appliquer les changements
        echo Ensuite, testez avec:
        echo   php -v
        echo   composer --version
        echo.
        pause
        exit /b 0
    ) else (
        echo Non installé avec Chocolatey. Continuez avec Option 2...
        echo.
    )
) else (
    echo ⚠️  Chocolatey non trouvé.
    echo.
)

REM Option 2: Check if XAMPP is installed
echo Cherche installations PHP courantes...
echo.

set "php_found="
set "php_path="

if exist "C:\xampp\php\php.exe" (
    set "php_found=yes"
    set "php_path=C:\xampp\php"
    echo ✅ XAMPP encontré: !php_path!
)

if exist "C:\wamp64\bin\php\php*.exe" (
    for /D %%D in (C:\wamp64\bin\php\php*) do (
        if exist "%%D\php.exe" (
            set "php_found=yes"
            set "php_path=%%D"
            echo ✅ WAMP64 encontré: !php_path!
        )
    )
)

if exist "C:\laragon\bin\php\php-*.* \php.exe" (
    for /R "C:\laragon\bin\php" %%F in (php.exe) do (
        if "!php_found!"=="" (
            set "php_found=yes"
            set "php_path=%%~dpF"
            echo ✅ Laragon encontré: !php_path!
        )
    )
)

if "!php_found!"=="yes" (
    echo.
    echo Ajout de PHP au PATH: !php_path!
    setx PATH "!php_path!;!PATH!"
    echo ✅ PHP añadido al PATH
    echo.
    echo Redémarrez votre terminal (PowerShell/CMD)
    echo Ensuite testez: php -v
    echo.
    pause
    exit /b 0
)

REM Option 3: If nothing found, download PHP
echo ⚠️  Aucune installation PHP trouvée
echo.
echo Options:
echo 1. Installer XAMPP (recommandé pour débutants)
echo 2. Installer Laragon (plus léger)
echo 3. Télécharger PHP standalone
echo.
echo Ouvrez: https://www.apachefriends.org/download.html
echo Téléchargez XAMPP pour PHP 8.2+
echo Installez dans C:\xampp
echo Relancez ce script après
echo.
echo Ou, si vous savez où PHP est installé, éditez ce script
echo et changez set "php_path=..." au bon chemin
echo.
pause
exit /b 1
