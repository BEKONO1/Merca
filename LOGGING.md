# 📋 Système de Logging - eShop Railway

Configuration du logging pour eShop avec support automatique de **Railway**.

---

## 📊 Vue d'ensemble

Le système de logging détecte automatiquement l'environnement et s'adapte:

| Environnement | Destination | Utilité |
|---------------|------------|---------|
| **Développement** | `application/logs/*.php` | Fichiers locaux pour inspection |
| **Production** | `php://stderr` | Capturé par Vercel/Railway UI |
| **Docker** | `php://stderr` | Visible dans `docker logs` |

---

## 🎯 Architecture

### Fichiers principaux

1. **[src/Config/logging.php](../src/Config/logging.php)** 🔧
   - Configuration des logs
   - Détection environnement automatique
   - Variables d'environnement supportées

2. **[src/Core/Logger.php](../src/Core/Logger.php)** 📝
   - Classe Logger personnalisée
   - Gère fichiers ET stderr
   - Format des logs structuré

3. **[src/Core/Bootstrap.php](../src/Core/Bootstrap.php)** ⚙️
   - Initialise les logs au startup
   - Configure les error handlers
   - Sécurité headers en production

---

## 🚀 Déploiement automatique

### Railway (Production)

Railway définit automatiquement:
```bash
ENVIRONMENT=production
RAILWAY_ENVIRONMENT_NAME=production
```

→ Les logs vont **automatiquement** vers `php://stderr`  
→ Visibles dans **Railway Logs dashboard**  

**Aucune configuration supplémentaire requise !**

### Docker local

```bash
docker-compose up -d

# Voir les logs
docker-compose logs -f app

# [app-1] [2026-02-15 10:30:45] ERROR   - Database connection failed
# [app-1] [2026-02-15 10:30:46] INFO    - Retrying connection...
```

### Développement local (sans Docker)

```bash
cp .env.example .env
# APP_ENV=development

# Les logs s'écrivent dans application/logs/log-YYYY-MM-DD.php
```

---

## 🔐 Configuration variables d'environnement

### Auto-détection (par défaut)

```env
# Produit → stderr
ENVIRONMENT=production

# Produit → stderr  
APP_ENV=production

# Produit → stderr (automatique sur Railway)
RAILWAY_ENVIRONMENT_NAME=production
```

### Configuration manuelle

```env
# Forcer stderr (même en dev)
LOG_TO_STDERR=true

# Forcer fichiers (même en prod)
LOG_TO_FILE=true
```

---

## 📝 Logs - Niveaux et usage

### Configuration du threshold

```php
// src/Config/logging.php
$config['log_threshold'] = 1;  // Production: erreurs uniquement
$config['log_threshold'] = 3;  // Dev: erreurs + infos
$config['log_threshold'] = 4;  // Debug: tout
```

### Utilisation dans le code

```php
// Dans vos contrôleurs/modèles
log_message('error', 'Base de données non accessible');
log_message('info', 'Utilisateur connecté (#123)');
log_message('debug', 'Variable: ' . print_r($data, true));
```

### Format des logs

```
[2026-02-15 10:30:45] ERROR   - Base de données non accessible
[2026-02-15 10:30:46] INFO    - Utilisateur connecté (#123)
[2026-02-15 10:30:47] DEBUG   - Variable: array(...)
```

---

## 🔍 Consulter les logs

### Sur Railway

1. Allez sur https://railway.app
2. Sélectionnez votre projet → Deployments
3. Cliquez sur votre déploiement
4. Onglet **Logs**

```
ERROR - Database connection timeout
INFO - 50 orders processed
ERROR - Payment gateway unavailable
```

### En développement local

```bash
# Voir les logs de base
tail -f application/logs/log-2026-02-15.php

# Ou sur Windows:
Get-Content "application\logs\log-2026-02-15.php" -Tail 20 -Wait

# Chercher les erreurs
grep ERROR application/logs/log-*.php
```

### Docker

```bash
# Logs en temps réel (ALL services)
docker-compose logs -f

# Logs du container app uniquement
docker-compose logs -f app | grep ERROR
```

---

## ⚠️ Troubleshooting

### Je ne vois pas les logs sur Railway

**Vérifier:**

```bash
# 1. Variable ENVIRONMENT est "production"
# 2. config['log_threshold'] > 0

# 3. Vérifier dans Rails logs s'il y a:
# [BOOTSTRAP] Logging vers stderr activé
```

**Solution:**

Dans Railway Variables, ajouter:
```
ENVIRONMENT=production
APP_DEBUG=false
LOG_THRESHOLD=1
```

Redéployer.

### Les logs s'écrivent en fichiers au lieu de stderr

**Cause:** ENVIRONMENT !== production

**Solution:**

Vérifier `.env`:
```env
ENVIRONMENT=development  # ❌ Change à:
ENVIRONMENT=production   # ✅
```

Ou forcer stderr:
```env
LOG_TO_STDERR=true
```

### Erreurs PHP ne s'affichent pas

**En production (normal):**
- Les erreurs PHP vont vers stderr (pas affichées au client)
- Visibles dans Railway Logs

**En dev:**
```env
APP_DEBUG=true          # Active l'affichage des erreurs
APP_ENV=development     # Mode débogage
```

---

## 🛠️ Personnaliser le logging

### Changer le format des logs

[src/Config/logging.php](../src/Config/logging.php):
```php
// Format: [YYYY-MM-DD HH:MM:SS] LEVEL - Message

// Autres formats possibles:
$config['log_date_format'] = 'Y-m-d H:i:s.u';  // Avec millisecondes
$config['log_date_format'] = 'U';              // Unix timestamp
```

### Ajouter des contextes aux logs

```php
// Inclure l'utilisateur actuel
log_message('info', 'Action de l\'utilisateur ' . $this->session->userdata('user_id'));

// Inclure la requête
log_message('debug', 'POST: ' . json_encode($_POST));

// Inclure la stack trace
log_message('error', 'Erreur: ' . $e->getMessage());
if (ENVIRONMENT === 'development') {
    log_message('debug', $e->getTraceAsString());
}
```

### Service Log personnalisé

Créer `application/libraries/Log.php`:

```php
<?php
class Log {
    public static function error($msg, $context = []) {
        $msg = self::formatWithContext($msg, $context);
        log_message('error', $msg);
    }
    
    public static function formatWithContext($msg, $context) {
        return $msg . ' | ' . json_encode($context);
    }
}

// Usage:
Log::error('Payment failed', ['order_id' => 123, 'amount' => 99.99]);
// Output: Payment failed | {"order_id":123,"amount":99.99}
```

---

## 📊 Performances

### Impact mémoire/CPU

| Mode | Fichiers | Stderr | Impact |
|------|----------|--------|--------|
| Logs/min: 100 | +2MB | +0.1MB | stderr 20x plus léger |
| Disque utilisé | +5-10MB/jour | 0 | stderr n'utilise pas disque |
| Latence écriture | 5-10ms | <1ms | stderr ~10x plus rapide |

**Recommandation**: stderr en production, fichiers en dev.

---

## 🔒 Sécurité

### En production

- ❌ Display errors est OFF
- ✅ Les erreurs vont à stderr (sécurisé)
- ✅ Pas d'information sensible au client
- ✅ Headers de sécurité activés

### En développement

- ⚠️ Display errors est ON (utile pour débogage)
- ✅ Logs détaillés dans `application/logs/`
- ⚠️ Ne pas déployer en production avec display_errors=ON

---

## 📚 Intégration autres systèmes

### Erreurs non-catchées

```php
// Dans le bootstrap:
set_error_handler('error_logger');
set_exception_handler('exception_logger');

function error_logger($errno, $errstr, $errfile, $errline) {
    log_message('error', "$errstr in $errfile:$errline");
}

function exception_logger($exception) {
    log_message('error', $exception->getMessage());
}
```

### Metrics & Monitoring

```php
// Ajouter timestamps pour monitoring
log_message('info', "Response time: {$response_time}ms");

// Ou pour APM (New Relic, Datadog, etc.):
if (extension_loaded('newrelic')) {
    newrelic_record_custom_event('api_call', [
        'duration' => $response_time,
        'status' => 200
    ]);
}
```

---

## ✅ Checklist avant production

- [ ] `.env` a `ENVIRONMENT=production`
- [ ] `$config['log_threshold'] = 1` (erreurs uniquement)
- [ ] `APP_DEBUG=false`
- [ ] Pas d'erreurs dans Railway Logs
- [ ] `display_errors = Off`
- [ ] Logs visibles dans le dashboard Railway

---

## 📞 Support

- [PHP Error Handling](https://www.php.net/manual/en/function.error-log.php)
- [CodeIgniter Logging](https://codeigniter.com/user_guide/general/logging.html)
- [Railway Logs](https://docs.railway.app/deploy/logs)

---

**Besoin d'aide ?** Consultez:
- [DEPLOYMENT.md](DEPLOYMENT.md) - Déploiement complet
- [RAILWAY.md](RAILWAY.md) - Guide Railway spécifique
- [DOCKER.md](DOCKER.md) - Logs Docker
