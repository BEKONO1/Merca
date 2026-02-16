# PHP 8.3 Apache optimisé pour Railway
# DocumentRoot: /var/www/html/public
# Support .htaccess avec mod_rewrite

FROM php:8.3-apache

# Définir le répertoire de travail
WORKDIR /var/www/html

# ============================================================================
# ÉTAPE 1: Installer les dépendances système requises
# ============================================================================

RUN apt-get update && apt-get install -y --no-install-recommends \
    curl \
    git \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libicu-dev \
    libssl-dev \
    zlib1g-dev \
    libzip-dev \
    && rm -rf /var/lib/apt/lists/*

# ============================================================================
# ÉTAPE 2: Installer les extensions PHP requises
# ============================================================================

RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
    pdo_mysql \
    gd \
    zip \
    intl \
    && docker-php-ext-enable pdo_mysql gd zip intl

# ============================================================================
# ÉTAPE 3: Configurer PHP pour le logging
# ============================================================================

RUN echo 'log_errors = On' >> /usr/local/etc/php/conf.d/docker-php-ext-error-logging.ini \
    && echo 'error_log = /dev/stderr' >> /usr/local/etc/php/conf.d/docker-php-ext-error-logging.ini

# ============================================================================
# ÉTAPE 4: Installer Composer
# ============================================================================

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# ============================================================================
# ÉTAPE 5: Configurer Apache
# ============================================================================

# 5a. Définir DocumentRoot sur /var/www/html/public
RUN sed -i 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
    /etc/apache2/sites-available/000-default.conf

# 5b. Activer les modules Apache nécessaires
RUN a2enmod rewrite headers

# 5c. Créer configuration Apache pour l'application
RUN echo '<Directory /var/www/html/public>\n\
    Options -MultiViews\n\
    AllowOverride All\n\
    Require all granted\n\
\n\
    <IfModule mod_rewrite.c>\n\
        RewriteEngine On\n\
        RewriteCond %{REQUEST_FILENAME} !-f\n\
        RewriteCond %{REQUEST_FILENAME} !-d\n\
        RewriteRule ^ index.php [QSA,L]\n\
    </IfModule>\n\
</Directory>' > /etc/apache2/conf-available/app.conf \
    && a2enconf app

# 5d. Optimiser Apache pour performance (reduce keepalive timeout)
RUN echo 'KeepAliveTimeout 5' >> /etc/apache2/apache2.conf

# ============================================================================
# ÉTAPE 6: Copier les fichiers du projet
# ============================================================================

COPY . /var/www/html

# ============================================================================
# ÉTAPE 7: Installer les dépendances Composer (mode production)
# ============================================================================

RUN composer install \
    --no-dev \
    --optimize-autoloader \
    --no-interaction \
    --prefer-stable

# ============================================================================
# ÉTAPE 8: Exécuter les migrations de base de données
# ============================================================================

RUN php src/Database/migrate.php || echo "ℹ️  Migrations non disponibles (peut être normal en first build)"

# ============================================================================
# ÉTAPE 9: Créer les répertoires de stockage
# ============================================================================

RUN mkdir -p storage/logs \
    && mkdir -p storage/cache \
    && mkdir -p storage/sessions \
    && mkdir -p storage/temp \
    && mkdir -p public/uploads \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 storage public/uploads public

# ============================================================================
# ÉTAPE 10: Port HTTP standard
# ============================================================================

# Railway définira le port via la variable d'environnement PORT
# Apache écoute par défaut sur 80
EXPOSE 80

# ============================================================================
# Apache démarre automatiquement
# Pas besoin de CMD ou ENTRYPOINT car l'image de base les définit
# ============================================================================
