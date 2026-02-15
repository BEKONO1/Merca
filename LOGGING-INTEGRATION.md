# 🔗 Guide d'Intégration - Système de Logging

Comment intégrer le nouveau système de logging à votre code existant.

---

## 🎯 Vue d'ensemble

Le système de logging a 3 composants:

1. **[src/Config/logging.php](../src/Config/logging.php)** - Configuration
2. **[src/Core/Logger.php](../src/Core/Logger.php)** - Classe Logger
3. **[src/Core/Bootstrap.php](../src/Core/Bootstrap.php)** - Initialisation

---

## 📋 Intégration étape par étape

### Étape 1: Charger le Bootstrap

Dans votre `index.php` (ou point d'entrée principal):

```php
<?php
// Au tout début du fichier

define('BASEPATH', __DIR__ . '/../');
define('APPPATH', BASEPATH . 'application/');
define('SRCPATH', BASEPATH . 'src/');

// Charger le Bootstrap
if (file_exists(SRCPATH . 'Core/Bootstrap.php')) {
    require_once SRCPATH . 'Core/Bootstrap.php';
}

// ... reste de votre code ...
```

### Étape 2: Charger la configuration des logs

Dans `application/config/config.php` ou ailleurs:

```php
<?php
// Charger la configuration des logs
require BASEPATH . 'src/Config/logging.php';

// $config array est maintenant disponible avec:
// - log_threshold
// - log_path
// - log_date_format
// etc.
```

### Étape 3: Initialiser le Logger (Optionnel)

Si vous voulez utiliser la classe Logger directement:

```php
<?php
// Charger la classe
require BASEPATH . 'src/Core/Logger.php';

// Créer une instance
$logger = new Logger();

// Écrire des logs
$logger->write_log('error', 'Erreur: ' . $error_msg);
$logger->write_log('info', 'Event: ' . $event);
```

---

## 🔄 Flux d'initialisation complet

```
1. index.php
   ↓
2. Charger Bootstrap
   ├─ Détecte ENVIRONMENT
   ├─ Charge logging.php
   ├─ Configure error handlers
   └─ Active stderr ou fichiers
   ↓
3. Charger CodeIgniter/Framework
   ↓
4. Framework charge log_message()
   ├─ Si USE_STDERR_LOGGING: vers php://stderr
   └─ Sinon: vers application/logs/
```

---

## 🎨 Code existant - Minimal changes

### Avant (CodeIgniter 3 standard)

```php
<?php
// application/config/config.php
$config['log_threshold'] = 1;
$config['log_path'] = '';
$config['log_file_extension'] = '';
$config['log_date_format'] = 'Y-m-d H:i:s';
```

### Après (avec notre système)

```php
<?php
// Remplacer par:
require __DIR__ . '/../../src/Config/logging.php';

// Tout le reste (log_threshold, log_path, etc.) est maintenant
// configuré automatiquement selon l'environnement!
```

---

## 📝 Exemple d'intégration complète

### Projet structure existante

```
application/
  config/
    config.php          ← Charger logging.php ici
    ...
  controllers/
    User.php            ← Utiliser log_message()
    Product.php
  ...

index.php               ← Charger Bootstrap ici
```

### Code à ajouter

**Dans `public/index.php` (au début):**

```php
<?php
const CI_VERSION = '3.1.13';

// 1. Définir les paths
define('BASEPATH', __DIR__ . '/../');
define('SRCPATH', BASEPATH . 'src/');
define('APPPATH', BASEPATH . 'application/');

// 2. Charger le Bootstrap du logging
if (file_exists(SRCPATH . 'Core/Bootstrap.php')) {
    require_once SRCPATH . 'Core/Bootstrap.php';
}

// 3. ... Rest of your code ...
```

**Dans `application/config/config.php` (remplacer la section log):**

```php
<?php
// Remplacer:
// $config['log_threshold'] = 1;
// $config['log_path'] = '';
// ... etc

// Par:
require_once __DIR__ . '/../../src/Config/logging.php';
// Toutes les configs log sont maintenant chargées et auto-détectées
```

**Dans les contrôleurs (rien à changer!):**

```php
<?php
class Product extends CI_Controller {
    public function create() {
        try {
            $id = $this->Product_model->create($data);
            // Ça marche déjà!
            log_message('info', "Produit créé: #$id");
            return true;
        } catch (Exception $e) {
            // Ceci ira vers stderr en prod, fichiers en dev
            log_message('error', "Erreur création: " . $e->getMessage());
            return false;
        }
    }
}
```

---

## 🔧 Configuration personnalisée (Optionnel)

Si vous avez des configs spéciales:

### Override de logging.php

Créer `application/config/logging.php` local:

```php
<?php
// Local customization
$config['log_threshold'] = 2;  // Override du threshold
$config['log_file_permissions'] = 0755;  // Custom permissions

// Charger ensuite la config principale
require __DIR__ . '/../../src/Config/logging.php';
```

### Charger le Logger manuellement

```php
<?php
// Si vous voulez l'instance Logger directement
require SRCPATH . 'Core/Logger.php';
$logger = new Logger();

// Maintenant utilisable via log_message() aussi
log_message('error', 'Test message');
```

---

## ✅ Checklist d'intégration

- [ ] `src/Config/logging.php` existe
- [ ] `src/Core/Logger.php` existe
- [ ] `src/Core/Bootstrap.php` existe
- [ ] Bootstrap chargé dans `index.php`
- [ ] Logging.php chargé dans `config.php`
- [ ] `TEST:` Exécuter `php test-logging.php`
- [ ] `TEST:` Voir log messages dans les outputs
- [ ] `.env` a `ENVIRONMENT=development` ou `production`

---

## 🚀 Test l'intégration

```bash
# Tester la configuration
php test-logging.php

# Output:
# ✅ Bootstrap loaded
# ✅ Configuration loaded
# ✅ USE_STDERR_LOGGING: NO (for development)
# ✅ All tests passed!

# Vérifier les logs
tail -f application/logs/log-*.php
```

---

## 🐛 Debugging l'intégration

### Les logs ne s'écrivent nulle part?

```bash
# 1. Vérifier que le Bootstrap s'exécute
grep "BOOTSTRAP" application/logs/log-*.php

# 2. Vérifier les permissions
ls -la application/logs/

# 3. Tester directement
php -r "error_log('Test message');"
```

### stderr ne s'affiche pas en prod?

```bash
# En Docker:
docker-compose logs app

# En Railway: Voir le dashboard Logs
```

### Configuration pas chargée

```bash
# Vérifier le chemin
php -r "echo realpath('src/Config/logging.php');"

# Vérifier la définition de SRCPATH
php -r "define('BASEPATH', __DIR__); echo constant('SRCPATH') ?? 'NOT DEFINED';"
```

---

## 📚 Exemple réel: E-commerce

### Avant (sans logs appropriés)

```php
<?php
class OrderController extends CI_Controller {
    public function checkout() {
        // Aucun logging
        $order = $this->Order_model->create($_POST);
        // Erreur = pas de trace
        
        if (!$order) {
            die('Error creating order');  // Mauvais
        }
    }
}
```

### Après (avec le système)

```php
<?php
class OrderController extends CI_Controller {
    public function checkout() {
        log_message('info', 'Checkout started for user: ' . $this->session->userdata('user_id'));
        
        try {
            $order = $this->Order_model->create($_POST);
            log_message('info', "Order créée: #$order->id");
            
            // Stock
            if (!$this->Stock_model->reserve($order->id)) {
                throw new Exception('Stock unavailable');
            }
            
            // Paiement
            $payment = $this->Payment_model->process($order->id);
            if (!$payment->success) {
                throw new Exception('Payment failed: ' . $payment->error);
            }
            
            log_message('info', "Order completed: #$order->id - Amount: " . $order->total);
            
        } catch (Exception $e) {
            log_message('error', "Order checkout failed: " . $e->getMessage());
            // Stderr en prod = visible dans Railway Logs ✅
            
            return $this->output
                ->set_status_header(400)
                ->set_output(json_encode(['error' => $e->getMessage()]));
        }
    }
}
```

---

## ➡️ Prochaines étapes

1. ✅ Intégrer l'intégration (ce fichier)
2. ✅ Tester avec `php test-logging.php`
3. ✅ Vérifier que les logs s'écrivent
4. ✅ Déployer sur Railway
5. ✅ Vérifier que les logs apparaissent dans le dashboard

---

**Besoin d'aide ?**

- [LOGGING.md](LOGGING.md) - Documentation complète
- [LOGGING-QUICKSTART.md](LOGGING-QUICKSTART.md) - Quick reference
- [src/Core/bootstrap.example.php](../src/Core/bootstrap.example.php) - Exemple d'intégration
