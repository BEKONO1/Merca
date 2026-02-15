# 🚀 LOGGING QUICKSTART - Railway Integration

Configuration rapide du logging pour Railway.

---

## ⚡ Démarrage rapide (30 secondes)

### 1. Le système détecte automatiquement l'environnement

**Sur Railway:**
```
ENVIRONMENT=production  →  Logs vers php://stderr ✅
```

**En développement local:**
```
ENVIRONMENT=development  →  Logs vers application/logs/ ✅
```

**Aucune configuration supplémentaire requise !**

---

## 📁 Fichiers créés

| Fichier | Fonction |
|---------|----------|
| `src/Config/logging.php` | Configuration des logs |
| `src/Core/Logger.php` | Classe Logger personnalisée |
| `src/Core/Bootstrap.php` | Initialisation du système |
| `test-logging.php` | Tester la config |

---

## ✅ Vérifier que ça marche

### Test rapide

```bash
# Vérifier la configuration
php test-logging.php

# Output:
# ✅ Bootstrap loaded
# ✅ Configuration loaded
# ✅ USE_STDERR_LOGGING: YES (pour production)
# ✅ All tests passed!
```

### Sur Railway

1. Déploy votre code
2. Allez dans **Logs**
3. Cherchez les entrées de log:
   ```
   [2026-02-15 10:30:45] ERROR   - Message d'erreur
   [2026-02-15 10:30:46] INFO    - Message info
   ```

### En développement local

```bash
# Voir les logs
tail -f application/logs/log-*.php

# Ou avec Docker:
docker-compose logs -f app
```

---

## 🔧 Configuration personnalisée (Optionnel)

### Forcer un mode spécifique

**Forcer stderr (même en dev):**
```env
LOG_TO_STDERR=true
```

**Forcer fichiers (même en prod):**
```env
LOG_TO_FILE=true
```

### Changer le niveau de logs

Dans `src/Config/logging.php`:

```php
// Production: Erreurs uniquement (recommandé)
$config['log_threshold'] = 1;

// Développement: Erreurs + Infos
$config['log_threshold'] = 3;

// Debug: Tout
$config['log_threshold'] = 4;

// Désactivé
$config['log_threshold'] = 0;
```

---

## 🎯 Architecture auto-détectée

```
┌─────────────────────────────────────┐
│    ENVIRONMENT variable             │
│  (détectée automatiquement)         │
└────────┬────────────────────────────┘
         │
    ┌────▼─────┐
    │           │
    ▼           ▼
PRODUCTION  DEVELOPMENT
    │           │
    ▼           ▼
  stderr      Fichiers
(Railway)    (Local)
```

---

## 📝 Utilisation dans le code

### Importer le loader

C'est déjà intégré dans le Bootstrap !

### Loguer des messages

```php
// Dans vos contrôleurs/modèles
log_message('error', 'Une erreur grave');
log_message('info', 'Événement normal');
log_message('debug', 'Infos de débogage');
```

### Exemple complet

```php
<?php
class OrderController {
    public function place_order() {
        try {
            $order_id = $this->Order_model->create();
            log_message('info', "Commande créée: #$order_id");
            return true;
        } catch (Exception $e) {
            log_message('error', "Erreur commande: " . $e->getMessage());
            return false;
        }
    }
}
```

---

## 🐳 Docker

### Voir les logs

```bash
# All containers
docker-compose logs -f

# App only
docker-compose logs -f app

# With filtering
docker-compose logs -f app | grep ERROR
```

---

## 🚨 Troubleshooting

### Les logs n'apparaissent pas sur Railway

1. Vérifier que `ENVIRONMENT=production`
2. Vérifier que `log_threshold > 0`
3. Attendre 30 secondes pour les logs

### Une erreur "Cannot write to file"

1. Vérifier que `application/logs/` existe
2. Vérifier les permissions: `chmod 755 application/logs/`
3. Forcer stderr: `LOG_TO_STDERR=true`

### Too many log files

Limiter le seuil:
```php
$config['log_threshold'] = 1;  // Erreurs only
```

Ou nettoyer:
```bash
# Supprimer logs > 7 jours
find application/logs/ -mtime +7 -delete
```

---

## ✨ Avantages

✅ **Production (Railway)**
- Logs dans le dashboard Railway
- Pas de fichiers disk
- Performant (~1ms par log)
- Intégration monitoring facile

✅ **Développement**
- Logs dans les fichiers locaux
- Inspection facile avec tail/grep
- Pas de surcharge réseau

---

## 📚 Documentation complète

- [LOGGING.md](LOGGING.md) - Guide complet
- [DEPLOYMENT.md](DEPLOYMENT.md) - Architecture
- [RAILWAY.md](RAILWAY.md) - Railway spécifique

---

**Ready to deploy!** 🚀
