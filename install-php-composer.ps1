# PowerShell Script: Configuration de PHP et Composer pour le projet eShop
# Exécuter en tant qu'administrateur

# Afficher un joli titre
Write-Host "`n" -ForegroundColor White
Write-Host "╔════════════════════════════════════════════════════════════════════╗" -ForegroundColor Cyan
Write-Host "║                                                                    ║" -ForegroundColor Cyan
Write-Host "║          Configuration PHP + Composer pour eShop                  ║" -ForegroundColor Cyan
Write-Host "║                                                                    ║" -ForegroundColor Cyan
Write-Host "╚════════════════════════════════════════════════════════════════════╝" -ForegroundColor Cyan
Write-Host "`n"

# Vérifier si administrateur
$isAdmin = ([Security.Principal.WindowsPrincipal] [Security.Principal.WindowsIdentity]::GetCurrent()).IsInRole([Security.Principal.WindowsBuiltInRole] "Administrator")

if (-not $isAdmin) {
    Write-Host "❌ ERREUR: Ce script doit être exécuté EN TANT QU'ADMINISTRATEUR" -ForegroundColor Red
    Write-Host "`nSolution:" -ForegroundColor Yellow
    Write-Host "  1. Ouvrir PowerShell" -ForegroundColor Yellow
    Write-Host "  2. Clic droit → 'Exécuter en tant qu'administrateur'" -ForegroundColor Yellow
    Write-Host "  3. Entrer: .\install-php-composer.ps1" -ForegroundColor Yellow
    Write-Host "`n"
    exit 1
}

Write-Host "✅ Exécution en tant qu'administrateur`n" -ForegroundColor Green

# Variables
$phpPath = ""
$foundPHP = $false

# Chercher PHP dans les emplacements courants
$commonPaths = @(
  "C:\xampp\php",
  "C:\wamp64\bin\php\php8.3.0",
  "C:\wamp64\bin\php\php8.2.0", 
  "C:\wamp\bin\php\php8.3.0",
  "C:\wamp\bin\php\php8.2.0",
  "C:\laragon\bin\php\php-8.3.0-Win32-x64",
  "C:\laragon\bin\php\php-8.2.0-Win32-x64",
  "C:\php"
)

Write-Host "🔍 Recherche des installations PHP..." -ForegroundColor Yellow
foreach ($path in $commonPaths) {
    if (Test-Path "$path\php.exe") {
        Write-Host "  ✅ Trouvé: $path" -ForegroundColor Green
        $phpPath = $path
        $foundPHP = $true
        break
    }
}

if (-not $foundPHP) {
    Write-Host "  ❌ Aucune installation PHP trouvée`n" -ForegroundColor Red
    Write-Host "Avez-vous XAMPP, WAMP ou Laragon installé?" -ForegroundColor Yellow
    Write-Host "  • XAMPP:   https://www.apachefriends.org" -ForegroundColor Cyan
    Write-Host "  • WAMP:    https://www.wampserver.com" -ForegroundColor Cyan
    Write-Host "  • Laragon: https://laragon.org" -ForegroundColor Cyan
    Write-Host "`nOu installer avec Chocolatey:" -ForegroundColor Yellow
    Write-Host "  choco install php composer`n" -ForegroundColor Cyan
    exit 1
}

# Ajouter PHP au PATH
Write-Host "`n🔧 Configuration du PATH..." -ForegroundColor Yellow

$currentPath = [Environment]::GetEnvironmentVariable("PATH", "User")

if ($currentPath -like "*$phpPath*") {
    Write-Host "  ℹ️  PHP est déjà dans le PATH`n" -ForegroundColor Cyan
} else {
    Write-Host "  Ajout de: $phpPath`n" -ForegroundColor White
    
    $newPath = "$phpPath;$currentPath"
    [Environment]::SetEnvironmentVariable("PATH", $newPath, "User")
    
    Write-Host "  ✅ PATH mise à jour`n" -ForegroundColor Green
}

# Vérifier PHP
Write-Host "✅ Vérification de PHP..." -ForegroundColor Yellow
$phpVersion = & "$phpPath\php.exe" -v 2>$null | Select-Object -First 1

if ($phpVersion) {
    Write-Host "  $phpVersion`n" -ForegroundColor Green
} else {
    Write-Host "  ⚠️  Impossible de vérifier PHP`n" -ForegroundColor Yellow
}

# Chercher ou télécharger Composer
Write-Host "✅ Vérification de Composer..." -ForegroundColor Yellow

$composerPath = Get-Command composer -ErrorAction SilentlyContinue
if ($composerPath) {
    Write-Host "  ✅ Composer trouvé: $($composerPath.Source)`n" -ForegroundColor Green
} else {
    # Chercher composer.phar dans le dossier courant
    if (Test-Path ".\composer.phar") {
        Write-Host "  ✅ composer.phar trouvé dans le dossier courant`n" -ForegroundColor Green
        Write-Host "  Tip: Vous pouvez utiliser: php composer.phar install`n" -ForegroundColor Cyan
    } else {
        Write-Host "  ℹ️  Téléchargement de composer.phar..." -ForegroundColor Yellow
        try {
            $ProgressPreference = 'SilentlyContinue'
            Invoke-WebRequest -Uri "https://getcomposer.org/composer.phar" -OutFile "composer.phar" -UseBasicParsing
            Write-Host "  ✅ composer.phar téléchargé`n" -ForegroundColor Green
        } catch {
            Write-Host "  ⚠️  Erreur téléchargement: $_`n" -ForegroundColor Yellow
            Write-Host "  Alternative: choco install composer`n" -ForegroundColor Cyan
        }
    }
}

# Résumé et prochaines étapes
Write-Host "═══════════════════════════════════════════════════════════════════" -ForegroundColor Cyan
Write-Host "✅ CONFIGURATION TERMINÉE!" -ForegroundColor Green
Write-Host "═══════════════════════════════════════════════════════════════════`n" -ForegroundColor Cyan

Write-Host "📋 Prochaines étapes:`n" -ForegroundColor Yellow

Write-Host "1️⃣  Redémarrer PowerShell ou CMD (fermez et relancez)`n" -ForegroundColor White

Write-Host "2️⃣  Exécuter dans ce dossier:`n" -ForegroundColor White
Write-Host "    php -v                    # Vérifier PHP`n" -ForegroundColor Cyan
Write-Host "    composer --version        # Vérifier Composer`n" -ForegroundColor Cyan

Write-Host "3️⃣  Installer les dépendances:`n" -ForegroundColor White
Write-Host "    composer install`n" -ForegroundColor Cyan

Write-Host "4️⃣  Valider la configuration:`n" -ForegroundColor White
Write-Host "    php test-dependencies.php`n" -ForegroundColor Cyan

Write-Host "5️⃣  Configurer l'environnement local:`n" -ForegroundColor White
Write-Host "    copy .env.example .env`n" -ForegroundColor Cyan
Write-Host "    notepad .env`n" -ForegroundColor Cyan

Write-Host "═══════════════════════════════════════════════════════════════════`n" -ForegroundColor Cyan

Write-Host "Appuyez sur une touche pour continuer..." -ForegroundColor Yellow
$null = Read-Host
