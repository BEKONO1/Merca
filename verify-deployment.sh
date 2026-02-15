#!/bin/bash
# DEPLOYMENT VERIFICATION CHECKLIST
# Run this before pushing to Railway

echo "═════════════════════════════════════════════════════════════════"
echo "  APACHE DOCKERFILE - PRE-DEPLOYMENT VERIFICATION"
echo "═════════════════════════════════════════════════════════════════"
echo ""

# Color codes
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

ERRORS=0

# Check 1: Dockerfile exists and contains Apache
echo "1️⃣  Checking Dockerfile..."
if [ -f "Dockerfile" ]; then
    if grep -q "php:8.3-apache" Dockerfile; then
        echo -e "${GREEN}✓${NC} Dockerfile found with PHP 8.3 Apache"
    else
        echo -e "${RED}✗${NC} Dockerfile doesn't use php:8.3-apache"
        ERRORS=$((ERRORS + 1))
    fi
    
    # Check Apache modules
    if grep -q "a2enmod rewrite" Dockerfile; then
        echo -e "${GREEN}✓${NC} Apache mod_rewrite enabled"
    else
        echo -e "${RED}✗${NC} mod_rewrite not enabled"
        ERRORS=$((ERRORS + 1))
    fi
    
    if grep -q "a2enmod headers" Dockerfile; then
        echo -e "${GREEN}✓${NC} Apache mod_headers enabled"
    else
        echo -e "${RED}✗${NC} mod_headers not enabled"
        ERRORS=$((ERRORS + 1))
    fi
    
    # Check extensions
    if grep -q "pdo_mysql" Dockerfile && grep -q "gd" Dockerfile && grep -q "zip" Dockerfile && grep -q "intl" Dockerfile; then
        echo -e "${GREEN}✓${NC} All required extensions found (pdo_mysql, gd, zip, intl)"
    else
        echo -e "${RED}✗${NC} Some extensions missing"
        ERRORS=$((ERRORS + 1))
    fi
else
    echo -e "${RED}✗${NC} Dockerfile not found"
    ERRORS=$((ERRORS + 1))
fi
echo ""

# Check 2: public/.htaccess exists
echo "2️⃣  Checking public/.htaccess..."
if [ -f "public/.htaccess" ]; then
    if grep -q "RewriteEngine On" public/.htaccess; then
        echo -e "${GREEN}✓${NC} .htaccess found with rewrite engine"
    else
        echo -e "${RED}✗${NC} .htaccess missing RewriteEngine"
        ERRORS=$((ERRORS + 1))
    fi
    
    if grep -q "index.php" public/.htaccess; then
        echo -e "${GREEN}✓${NC} CodeIgniter routing configured"
    else
        echo -e "${YELLOW}⚠${NC}  CodeIgniter routing might not be configured"
    fi
else
    echo -e "${RED}✗${NC} public/.htaccess not found"
    ERRORS=$((ERRORS + 1))
fi
echo ""

# Check 3: railway.json (if exists)
echo "3️⃣  Checking railway.json..."
if [ -f "railway.json" ]; then
    if grep -q "startCommand" railway.json; then
        if grep -q "php -S" railway.json; then
            echo -e "${YELLOW}⚠${NC}  startCommand still has 'php -S' - should be removed for Apache"
            ERRORS=$((ERRORS + 1))
        else
            echo -e "${GREEN}✓${NC} startCommand configured properly"
        fi
    else
        echo -e "${GREEN}✓${NC} No custom startCommand (Apache auto-starts)"
    fi
    
    if grep -q "healthcheckPath" railway.json; then
        echo -e "${GREEN}✓${NC} Health check configured"
    else
        echo -e "${YELLOW}⚠${NC}  No health check path defined"
    fi
else
    echo -e "${YELLOW}⚠${NC}  railway.json not found (optional)"
fi
echo ""

# Check 4: composer.json
echo "4️⃣  Checking composer.json..."
if [ -f "composer.json" ]; then
    if grep -q "vlucas/phpdotenv" composer.json; then
        echo -e "${GREEN}✓${NC} phpdotenv package found"
    else
        echo -e "${YELLOW}⚠${NC}  phpdotenv not found (optional)"
    fi
    
    if grep -q "ext-pdo_mysql" composer.json; then
        echo -e "${GREEN}✓${NC} PHP extensions declared in composer.json"
    else
        echo -e "${YELLOW}⚠${NC}  PHP extensions might not be declared"
    fi
else
    echo -e "${YELLOW}⚠${NC}  composer.json not found"
fi
echo ""

# Check 5: Migration system
echo "5️⃣  Checking migration system..."
if [ -f "src/Database/migrate.php" ]; then
    echo -e "${GREEN}✓${NC} Migration runner found"
else
    echo -e "${YELLOW}⚠${NC}  src/Database/migrate.php not found (migrations optional)"
fi

if [ -d "application/migrations" ]; then
    COUNT=$(find application/migrations -name "*.sql" 2>/dev/null | wc -l)
    echo -e "${GREEN}✓${NC} Migrations folder found ($COUNT SQL files)"
else
    echo -e "${YELLOW}⚠${NC}  application/migrations folder not found"
fi
echo ""

# Check 6: Environment files
echo "6️⃣  Checking environment configuration..."
if [ -f ".env.example" ]; then
    echo -e "${GREEN}✓${NC} .env.example found"
else
    echo -e "${YELLOW}⚠${NC}  .env.example not found"
fi

if [ -f ".env" ]; then
    echo -e "${GREEN}✓${NC} .env file exists"
else
    echo -e "${YELLOW}⚠${NC}  .env file not found (create from .env.example)"
fi
echo ""

# Check 7: Source code structure
echo "7️⃣  Checking project structure..."
if [ -d "application" ]; then
    echo -e "${GREEN}✓${NC} application/ folder found"
else
    echo -e "${RED}✗${NC} application/ folder missing"
    ERRORS=$((ERRORS + 1))
fi

if [ -d "public" ]; then
    echo -e "${GREEN}✓${NC} public/ folder found"
else
    echo -e "${RED}✗${NC} public/ folder missing"
    ERRORS=$((ERRORS + 1))
fi

if [ -d "system" ]; then
    echo -e "${GREEN}✓${NC} system/ folder found"
else
    echo -e "${YELLOW}⚠${NC}  system/ folder not found"
fi
echo ""

# Summary
echo "═════════════════════════════════════════════════════════════════"
if [ $ERRORS -eq 0 ]; then
    echo -e "${GREEN}✅ ALL CHECKS PASSED - READY FOR DEPLOYMENT!${NC}"
    echo ""
    echo "Next steps:"
    echo "  1. docker-compose up        (test locally)"
    echo "  2. Verify application loads"
    echo "  3. git push origin main     (deploy to Railway)"
    echo ""
else
    echo -e "${RED}❌ $ERRORS ISSUE(S) FOUND - PLEASE FIX BEFORE DEPLOYING${NC}"
    echo ""
    echo "Review the checks above and fix any errors marked with ✗"
    echo ""
fi
echo "═════════════════════════════════════════════════════════════════"
