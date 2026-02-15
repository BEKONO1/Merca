╔═════════════════════════════════════════════════════════════════════════════╗
║                                                                             ║
║              📄 PROCFILE - Configuration de serveur web                    ║
║                                                                             ║
║              Pour Heroku / Platforms-as-a-Service (PaaS)                   ║
║                                                                             ║
╚═════════════════════════════════════════════════════════════════════════════╝

═════════════════════════════════════════════════════════════════════════════
📋 FICHIER: Procfile
═════════════════════════════════════════════════════════════════════════════

Contenu sélectionné:
┌─────────────────────────────────────────────────────────────────────────┐
│ web: heroku-php-apache2 public/                                         │
└─────────────────────────────────────────────────────────────────────────┘

✅ RECOMMANDÉ POUR VOTRE E-BOUTIQUE


═════════════════════════════════════════════════════════════════════════════
⚖️ COMPARAISON: Apache2 vs PHP Built-in Server
═════════════════════════════════════════════════════════════════════════════

┌──────────────────────────────────────────────────────────────────────────┐
│ APACHE2 (heroku-php-apache2)                                             │
├──────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│ web: heroku-php-apache2 public/                                         │
│                                                                          │
│ ✅ AVANTAGES:                                                           │
│    ✓ Support natif .htaccess (CRITIQUE pour votre boutique!)           │
│    ✓ Réécriture d'URL via mod_rewrite                                  │
│    ✓ Support VirtualHost complet                                        │
│    ✓ Gestion d'erreurs HTTP native                                      │
│    ✓ Compression GZIP automatique                                       │
│    ✓ Performance: serveur production                                    │
│    ✓ Support des modules PHP (php-cli)                                 │
│    ✓ Basé sur Apache2 (standard industriel)                            │
│                                                                          │
│ ❌ INCONVÉNIENTS:                                                        │
│    ✗ Plus lourd (utilise plus de RAM)                                  │
│    ✗ Startup plus lent                                                 │
│                                                                          │
│ 🎯 CAS D'USAGE:                                                         │
│    → E-commerce avec SEO (réécriture d'URL)                            │
│    → CodeIgniter avec .htaccess                                         │
│    → Applications production-ready                                       │
│    → Système avec mod_rewrite complexe                                 │
│                                                                          │
└──────────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────────────┐
│ PHP BUILT-IN SERVER (php -S)                                             │
├──────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│ web: php -S 0.0.0.0:$PORT -t public/                                   │
│                                                                          │
│ ✅ AVANTAGES:                                                           │
│    ✓ TRÈS léger (minimal RAM)                                          │
│    ✓ Startup très rapide (< 1 sec)                                     │
│    ✓ Parfait pour développement                                         │
│    ✓ Pas de dépendances externes                                        │
│    ✓ Facile à configurer                                                │
│                                                                          │
│ ❌ INCONVÉNIENTS (PROBLÉMATIQUES):                                       │
│    ✗ ❌ NE SUPPORTE PAS .htaccess                                       │
│    ✗ ❌ PAS DE mod_rewrite                                              │
│    ✗ ❌ Pas de réécriture d'URL                                         │
│    ✗ Pas thread-safe (une requête à la fois)                           │
│    ✗ Performances faibles en production                                 │
│    ✗ Pas de gestion d'erreurs HTTP avancée                             │
│    ✗ Non recommandé pour la production                                 │
│                                                                          │
│ 🎯 CAS D'USAGE:                                                         │
│    → Développement local uniquement                                      │
│    → Prototypage rapide                                                 │
│    → Tests simples                                                      │
│    → Projets sans réécriture d'URL                                     │
│                                                                          │
└──────────────────────────────────────────────────────────────────────────┘


═════════════════════════════════════════════════════════════════════════════
🔴 POUR VOTRE E-BOUTIQUE: POURQUOI APACHE EST NÉCESSAIRE
═════════════════════════════════════════════════════════════════════════════

Votre application:
  • CodeIgniter 3
  • URL SEO-friendly (rewriting d'URL)
  • Fichiers .htaccess pour configuration

Exemple de .htaccess CodeIgniter:
┌─────────────────────────────────────────────────────────────────────────┐
│ <IfModule mod_rewrite.c>                                                │
│    RewriteEngine On                                                      │
│    RewriteCond %{REQUEST_FILENAME} !-f                                   │
│    RewriteCond %{REQUEST_FILENAME} !-d                                   │
│    RewriteRule ^(.*)$ index.php/$1 [L]                                   │
│ </IfModule>                                                              │
└─────────────────────────────────────────────────────────────────────────┘

🔴 SANS APACHE (.htaccess ignoré):
   └─ http://example.com/products/123
      → 404 Not Found (routes PAS reconnues)

✅ AVEC APACHE2 (.htaccess appliqué):
   └─ http://example.com/products/123
      → Redirigé vers index.php?url=products/123
      → Application fonctionne! ✓


═════════════════════════════════════════════════════════════════════════════
📊 TABLEAU COMPARATIF DÉTAILLÉ
═════════════════════════════════════════════════════════════════════════════

Feature                          Apache2    PHP Server
────────────────────────────────────────────────────────
.htaccess support               ✅ OUI     ❌ NON
URL Rewriting (mod_rewrite)     ✅ OUI     ❌ NON
Production-ready                ✅ OUI     ❌ NON
Static file caching             ✅ OUI     ❌ NON
GZIP compression               ✅ OUI     ❌ NON
HTTP error handling            ✅ COMPLET ❌ BASIQUE
Performance                    ⭐⭐⭐⭐   ⭐⭐
RAM usage (idle)               ~50MB      ~5MB
Startup time                   ~2-3s      <1s
Concurrent requests            ✅ MULTI   ❌ SINGLE
Module ecosystem               ✅ RICHE   ❌ LIMITÉ
SEO-friendly URLs              ✅ OUI     ❌ NON (sans config)
E-commerce ready               ✅ OUI     ❌ NON


═════════════════════════════════════════════════════════════════════════════
🎯 RECOMMANDATION FINALE
═════════════════════════════════════════════════════════════════════════════

POUR VOTRE PROJET:

┌─────────────────────────────────────────────────────────────────────────┐
│ ✅ UTILISER: web: heroku-php-apache2 public/                           │
│                                                                         │
│ RAISONS:                                                                │
│  1. Votre .htaccess CodeIgniter sera appliqué                          │
│  2. URLs SEO-friendly fonctionneront                                    │
│  3. Performance optimisée pour production                              │
│  4. Compatible avec votre infrastructure existante                     │
│  5. Support réécritures d'URL complexes                                │
│                                                                         │
│ ÉVITER: php -S 0.0.0.0:$PORT                                           │
│  - Causera des erreurs 404 sur vos routes                              │
│  - Pas prévu pour production avec .htaccess                            │
│  - Goulot d'étranglement de performance                                │
│                                                                         │
└─────────────────────────────────────────────────────────────────────────┘


═════════════════════════════════════════════════════════════════════════════
🚀 PROCFILE EN CONTEXTE: HEROKU vs RAILWAY
═════════════════════════════════════════════════════════════════════════════

HEROKU (Procfile):
  └─ Procfile dictate le serveur web à utiliser
  └─ heroku-php-apache2 est le buildpack officiel
  └─ Recommandé et maintenu par Heroku

RAILWAY (Dockerfile):
  └─ Vous avez déjà un Dockerfile optimal
  └─ Procfile peut être ignoré sur Railway
  └─ Dockerfile offre plus de contrôle

LOCALHOST (Développement):
  └─ Procfile est ignoré
  └─ Utilisez plutôt: php -S localhost:8000 -t public/
  └─ Ou: docker-compose up -d


═════════════════════════════════════════════════════════════════════════════
📝 CE QUE FAIT VOTRE PROCFILE
═════════════════════════════════════════════════════════════════════════════

Fichier créé: Procfile (sans extension)
Contenu:      web: heroku-php-apache2 public/

Quand il est utilisé (par Heroku):

  1. Heroku détecte le Procfile
  2. Lance le buildpack heroku-php-apache2
  3. Configure Apache2 dans le conteneur
  4. Définir DocumentRoot = public/
  5. Active mod_rewrite pour .htaccess
  6. Écoute sur port $PORT (défini par Heroku)
  7. Démarre Apache en tant que service web


═════════════════════════════════════════════════════════════════════════════
⚙️ CONFIGURATION LIÉE (.htaccess)
═════════════════════════════════════════════════════════════════════════════

Votre application a probablement un .htaccess comme:

public/.htaccess:
┌─────────────────────────────────────────────────────────────────────────┐
│ <IfModule mod_rewrite.c>                                                │
│     RewriteEngine On                                                     │
│                                                                          │
│     # Vers l'index si ce n'est pas un fichier réel                     │
│     RewriteCond %{REQUEST_FILENAME} !-f                                 │
│     RewriteCond %{REQUEST_FILENAME} !-d                                 │
│     RewriteRule ^(.*)$ index.php/$1 [L]                                 │
│ </IfModule>                                                             │
└─────────────────────────────────────────────────────────────────────────┘

💡 Apache (heroku-php-apache2) → ✅ Applique cette règle
   PHP Server (php -S) → ❌ Ignore complètement


Exemple de ce qui se passe:

URL: https://example.com/products/show/123

AVEC APACHE (Procfile correct):
  1. Apache reçoit la requête
  2. Applique .htaccess
  3. RewriteEngine transforme en: index.php?url=products/show/123
  4. CodeIgniter parse la route
  5. Affiche la page produit ✅

SANS APACHE (si php -S était utilisé):
  1. PHP server reçoit /products/show/123
  2. Cherche un fichier public/products/show/123 (N'EXISTE PAS)
  3. Retourne 404 ❌


═════════════════════════════════════════════════════════════════════════════
🔗 PLATEFORME SUPPORTANT PROCFILE
═════════════════════════════════════════════════════════════════════════════

Heroku:
  ✅ Principal utilisateur de Procfile
  ✅ heroku-php-apache2 est le buildpack officiel
  ✅ Détecte automatiquement Procfile

Railway:
  ⚠️  Peut ignorer Procfile en faveur du Dockerfile
  ℹ️  Votre Dockerfile existant prend la priorité

Render:
  ✅ Supporte Procfile pour déploiement
  ✅ Compatible buildpack Heroku

Fly.io:
  ⚠️  Préfère Dockerfile

Autres PaaS:
  ✓ Beaucoup supportent Procfile
  ✓ Cherchez leur documentation


═════════════════════════════════════════════════════════════════════════════
💡 CONSEIL DE DÉPLOIEMENT
═════════════════════════════════════════════════════════════════════════════

POUR HEROKU + VOTRE E-BOUTIQUE:

  1. ✅ Procfile avec Apache2 (créé ✓)
  2. ✅ .htaccess pour réécriture (à verifier)
  3. ✅ Dockerfile optionnel (prend priorité si présent)
  4. ✅ Procfile assure fallback si Dockerfile absent

STRATÉGIE:
  • Procfile = Fallback (si pas de Docker)
  • Dockerfile = Primary (plus de contrôle)
  • Les deux peuvent coexister

RECOMMANDATION:
  → Garder les deux
  → Dockerfile pour Railway (actuel)
  → Procfile pour Heroku compatibility


═════════════════════════════════════════════════════════════════════════════
📋 CHECKLIST FINAL
═════════════════════════════════════════════════════════════════════════════

✅ Procfile créé avec: web: heroku-php-apache2 public/
✅ Apache2 support .htaccess
✅ Réécriture d'URL activée
✅ Compatible avec CodeIgniter 3
✅ Prêt pour Heroku deployment

Si déploiement sur Heroku:
□ Vérifier .htaccess existe dans public/
□ Vérifier mod_rewrite est activé dans Apache
□ Tester les routes de l'application
□ Vérifier logs si 404 errors


═════════════════════════════════════════════════════════════════════════════

RÉSUMÉ: Vous avez la meilleure configuration pour une e-boutique avec
         réécriture d'URL! Apache2 gérera tous vos .htaccess. ✅

═════════════════════════════════════════════════════════════════════════════
