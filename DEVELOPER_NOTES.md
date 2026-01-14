# Developer Notes - Sui User Wallets

Vollständige technische Dokumentation des Projekts.

---

## 📋 Inhaltsverzeichnis

1. [Projekt-Übersicht](#projekt-übersicht)
2. [Architektur](#architektur)
3. [Komponenten](#komponenten)
4. [Environment Variables](#environment-variables)
5. [Contract-Adressen (Testnet)](#contract-adressen-testnet)
6. [Lokale Entwicklung](#lokale-entwicklung)
7. [Deployment](#deployment)
8. [API Endpoints](#api-endpoints)
9. [WordPress Plugin Struktur](#wordpress-plugin-struktur)
10. [Datenbank Schema](#datenbank-schema)
11. [Security](#security)
12. [Troubleshooting](#troubleshooting)

---

## Projekt-Übersicht

Ein WordPress-basiertes Custodial Wallet System für die Sui Blockchain. Ermöglicht automatische Wallet-Erstellung für WordPress-User ohne Blockchain-Kenntnisse.

### Kernfeatures:
- ✅ Automatische Sui Wallet-Erstellung bei User-Registrierung
- ✅ Custodial Wallet Management (WordPress verwaltet Private Keys)
- ✅ AES-256-CBC Verschlüsselung für Private Keys
- ✅ Vercel Serverless API für Blockchain-Operationen
- ✅ Auto-Update System via GitHub Releases
- ✅ Integration mit Sui Course Loyalty Plugin

### Tech Stack:
- **Backend:** WordPress Plugin (PHP 7.4+)
- **API Layer:** Vercel Serverless Functions (TypeScript/Node.js)
- **Blockchain:** Sui Testnet
- **Database:** WordPress MySQL/MariaDB
- **Version Control:** GitHub mit Auto-Update
- **Deployment:** FTP/SFTP, GitHub Actions

---

## Architektur

```
┌─────────────────────────────────────────────────────────────┐
│                        USER                                 │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                  WORDPRESS FRONTEND                         │
│  - User Registration                                        │
│  - User Profile                                             │
│  - Course/Badge Requests                                    │
└────────────────────────┬────────────────────────────────────┘
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│              WORDPRESS PLUGIN (PHP)                         │
│  ┌──────────────────────────────────────────────┐          │
│  │  Sui User Wallets Plugin                     │          │
│  │  - SUW_Wallet_Manager (Wallet CRUD)          │          │
│  │  - SUW_Wallet_Crypto (Encryption)            │          │
│  │  - SUW_Auto_Updater (GitHub Updates)         │          │
│  │  - SUW_Loyalty_Integration                   │          │
│  └──────────────────────────────────────────────┘          │
│                         │                                   │
│  ┌──────────────────────▼───────────────────────┐          │
│  │  WordPress Database (MySQL)                  │          │
│  │  - wp_sui_user_wallets                       │          │
│  │    - user_id                                 │          │
│  │    - wallet_address                          │          │
│  │    - encrypted_private_key                   │          │
│  └──────────────────────────────────────────────┘          │
└────────────────────────┬────────────────────────────────────┘
                         │
                         │ HTTPS POST
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│              VERCEL SERVERLESS API                          │
│  https://your-project.vercel.app                            │
│                                                              │
│  ┌────────────────┐  ┌────────────────┐                    │
│  │ /api/generate- │  │ /api/get-      │                    │
│  │ wallet         │  │ balance        │                    │
│  └────────────────┘  └────────────────┘                    │
│                                                              │
│  ┌────────────────┐  ┌────────────────┐                    │
│  │ /api/create-   │  │ /api/update-   │                    │
│  │ badge          │  │ progress       │                    │
│  └────────────────┘  └────────────────┘                    │
│                                                              │
│  ┌──────────────────────────────────────┐                  │
│  │  lib/sui-client.ts                   │                  │
│  │  - SuiClient Wrapper                 │                  │
│  │  - Ed25519Keypair Management         │                  │
│  │  - Transaction Building              │                  │
│  └──────────────────────────────────────┘                  │
└────────────────────────┬────────────────────────────────────┘
                         │
                         │ RPC Calls
                         │
                         ▼
┌─────────────────────────────────────────────────────────────┐
│                  SUI BLOCKCHAIN (TESTNET)                   │
│  https://fullnode.testnet.sui.io:443                        │
│                                                              │
│  ┌──────────────────────────────────────┐                  │
│  │  Smart Contracts (Move)              │                  │
│  │  - Loyalty Program Package           │                  │
│  │  - Badge NFT System                  │                  │
│  │  - User Progress Tracking            │                  │
│  └──────────────────────────────────────┘                  │
└─────────────────────────────────────────────────────────────┘
```

### Datenfluss:

**Wallet-Erstellung:**
1. User registriert sich in WordPress
2. `user_register` Hook triggert `SUW_Wallet_Manager->create_wallet_for_user()`
3. WordPress ruft Vercel API: `POST /api/generate-wallet`
4. Vercel generiert Ed25519 Keypair
5. Vercel returned Wallet Address + Private Key
6. WordPress verschlüsselt Private Key (AES-256-CBC)
7. WordPress speichert in DB: `wp_sui_user_wallets`

**Badge-Erstellung:**
1. User oder Admin erstellt Badge-Request
2. WordPress Plugin ruft Vercel API: `POST /api/create-badge`
3. Vercel entschlüsselt Admin Private Key
4. Vercel erstellt Sui Transaction
5. Vercel signiert und submitted Transaction
6. Badge NFT wird auf User's Wallet geminted

---

## Komponenten

### 1. WordPress Plugin (`wordpress-plugin-wallet/`)

```
sui-user-wallets/
├── sui-user-wallets.php           # Main Plugin File
├── includes/
│   ├── class-wallet-manager.php    # Wallet CRUD Operations
│   ├── class-wallet-crypto.php     # AES-256-CBC Encryption
│   ├── class-auto-updater.php      # GitHub Auto-Update
│   └── class-loyalty-integration.php # Badge Integration
├── debug-helper/
│   └── debug-helper.php            # Debug Log Viewer Plugin
├── fix-table.php                   # DB Table Creation Helper
├── force-update-check.php          # Update Cache Clear Helper
└── test-notice.php                 # Notice Test Helper
```

### 2. Vercel API (`vercel-api/`)

```
vercel-api/
├── api/
│   ├── generate-wallet.ts          # Generate Ed25519 Keypair
│   ├── get-balance.ts              # Query SUI Balance
│   ├── create-badge.ts             # Mint Badge NFT
│   ├── update-progress.ts          # Update Badge Progress
│   └── test.ts                     # Health Check Endpoint
├── lib/
│   └── sui-client.ts               # Sui Client Wrapper
├── package.json
└── tsconfig.json
```

---

## Environment Variables

### Vercel API (.env)

```bash
# Sui Network Configuration
SUI_NETWORK=testnet
# Options: mainnet, testnet, devnet, localnet

# Admin Wallet Private Key (suiprivkey format)
ADMIN_PRIVATE_KEY=suiprivkey1...
# WICHTIG: Muss mit 'suiprivkey1' beginnen
# Generiert via: sui keytool generate ed25519

# Smart Contract Adressen (Testnet)
PACKAGE_ID=0x... # Loyalty Program Package ID
BADGE_TYPE=0x...::badge::Badge # Badge Object Type

# Optional: API Authentication
API_SECRET_KEY=your-secret-key
# Falls gesetzt, muss WordPress diesen Key im Header senden
```

### WordPress Plugin (wp-config.php oder Admin UI)

**Via Admin UI:**
```
WordPress Admin → User Wallets → Einstellungen
```

**Via wp-config.php:**
```php
// Vercel API Configuration
define('SUW_VERCEL_API_URL', 'https://your-project.vercel.app');
define('SUW_VERCEL_API_KEY', 'your-secret-key'); // Optional

// Debug Mode (für Entwicklung)
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', false);
```

**Encryption Keys:**
WordPress nutzt automatisch:
```php
// Automatisch aus wp-config.php
AUTH_KEY + SECURE_AUTH_KEY = Encryption Base
```

---

## Contract-Adressen (Testnet)

### Sui Course Loyalty Program

Diese Adressen stammen aus dem ursprünglichen Loyalty Program Deployment:

```bash
# Package ID
PACKAGE_ID=0x[YOUR_PACKAGE_ID]

# Module: badge
MODULE_NAME=badge

# Badge Type (Object Type)
BADGE_TYPE=0x[PACKAGE_ID]::badge::Badge

# Admin Cap (für privilegierte Operationen)
ADMIN_CAP=0x[YOUR_ADMIN_CAP_OBJECT_ID]
```

### Wie bekomme ich die Adressen?

**Nach Smart Contract Deployment:**

```bash
# 1. Deploy Contract
cd sui-course-contracts
sui move build
sui client publish --gas-budget 100000000

# Output zeigt:
# ┌──────────────────────────────────────────────────────────┐
# │ Object Changes                                           │
# ├──────────────────────────────────────────────────────────┤
# │ Published Objects:                                       │
# │  ┌────────────────────────────────────────────────────┐ │
# │  │ PackageID: 0xABCD1234...                           │ │ ← PACKAGE_ID
# │  └────────────────────────────────────────────────────┘ │
# └──────────────────────────────────────────────────────────┘

# 2. Created Objects:
# AdminCap: 0xDEADBEEF... ← ADMIN_CAP

# 3. Badge Type ist:
# 0x[PACKAGE_ID]::badge::Badge
```

**Testnet Explorer:**
```
https://suiexplorer.com/?network=testnet
```

---

## Lokale Entwicklung

### Prerequisites

```bash
# System Requirements
- PHP 7.4+
- Node.js 18+
- Composer
- WordPress 5.0+
- MySQL/MariaDB

# Tools
- Sui CLI
- Vercel CLI
- Git
```

### 1. Vercel API lokal starten

```bash
# Clone Repository
git clone https://github.com/utakapp/sui-user-wallets.git
cd sui-user-wallets

# Vercel API Setup
cd ../vercel-api  # Falls separate Repository
npm install

# Environment Variables
cp .env.example .env
# Bearbeite .env und füge deine Keys ein

# Starte Dev Server
vercel dev
# Oder:
npm run dev

# API läuft auf: http://localhost:3000
```

### 2. WordPress Plugin lokal entwickeln

**Option A: Lokales WordPress (XAMPP, MAMP, Local by Flywheel)**

```bash
# Plugin installieren
cd /path/to/wordpress/wp-content/plugins
git clone https://github.com/utakapp/sui-user-wallets.git sui-user-wallets

# WordPress aktivieren
# → WP Admin → Plugins → Sui User Wallets → Activate

# Einstellungen konfigurieren
# → WP Admin → User Wallets → Einstellungen
# Vercel API URL: http://localhost:3000
```

**Option B: Docker WordPress**

```bash
# docker-compose.yml
version: '3'
services:
  wordpress:
    image: wordpress:latest
    ports:
      - "8080:80"
    volumes:
      - ./sui-user-wallets:/var/www/html/wp-content/plugins/sui-user-wallets
    environment:
      WORDPRESS_DB_HOST: db
      WORDPRESS_DB_USER: wordpress
      WORDPRESS_DB_PASSWORD: wordpress
      WORDPRESS_DB_NAME: wordpress

  db:
    image: mysql:5.7
    environment:
      MYSQL_ROOT_PASSWORD: somewordpress
      MYSQL_DATABASE: wordpress
      MYSQL_USER: wordpress
      MYSQL_PASSWORD: wordpress

# Starten
docker-compose up -d

# WordPress: http://localhost:8080
```

### 3. Testing

**WordPress Plugin Tests:**

```bash
# Unit Tests (falls vorhanden)
cd wordpress-plugin-wallet
composer install
./vendor/bin/phpunit

# Manuelles Testing
# 1. User erstellen → Wallet sollte generiert werden
# 2. Debug Log prüfen: wp-content/debug.log
# 3. Datenbank prüfen: wp_sui_user_wallets Tabelle
```

**Vercel API Tests:**

```bash
cd vercel-api

# Test Generate Wallet
curl -X POST http://localhost:3000/api/generate-wallet

# Expected Response:
# {
#   "address": "0x...",
#   "privateKey": "suiprivkey1...",
#   "publicKey": "..."
# }

# Test Get Balance
curl -X POST http://localhost:3000/api/get-balance \
  -H "Content-Type: application/json" \
  -d '{"address": "0x..."}'

# Expected Response:
# {
#   "address": "0x...",
#   "balance": "1.5",
#   "balanceInMist": "1500000000"
# }
```

### 4. Sui Contract Testing (Optional)

```bash
# Falls du Smart Contracts entwickelst
cd sui-course-contracts

# Build Contract
sui move build

# Run Tests
sui move test

# Deploy to Testnet
sui client publish --gas-budget 100000000

# Interact with Contract
sui client call --package 0x... --module badge --function create_badge
```

---

## Deployment

### Vercel API Deployment

```bash
# Via Vercel CLI
vercel login
vercel --prod

# Oder via GitHub Integration
# 1. Push zu GitHub
git push origin main

# 2. Vercel auto-deploys
# URL: https://your-project.vercel.app

# Environment Variables setzen
vercel env add ADMIN_PRIVATE_KEY production
vercel env add SUI_NETWORK production
```

### WordPress Plugin Deployment

**Option 1: Manuell via FTP/SFTP**

```bash
# 1. ZIP erstellen
cd wordpress-plugin-wallet
zip -r sui-user-wallets.zip . -x "*.git*" -x "node_modules/*"

# 2. Via FTP hochladen
# Upload nach: /wp-content/plugins/sui-user-wallets/

# 3. WordPress aktivieren
# WP Admin → Plugins → Activate
```

**Option 2: Via deploy.sh Script**

```bash
cd wordpress-plugin-wallet
./deploy.sh

# Wähle Deployment-Methode:
# 1) SFTP
# 2) SSH
# 3) FTP
# 4) Local Copy
# 5) ZIP only
```

**Option 3: Via GitHub Actions (Automatisch)**

```yaml
# .github/workflows/deploy.yml
# Bereits konfiguriert - triggered on push to main

# Secrets hinzufügen:
# GitHub → Settings → Secrets
# - FTP_SERVER
# - FTP_USERNAME
# - FTP_PASSWORD
```

### GitHub Release erstellen (für Auto-Update)

```bash
# Version bump
# Bearbeite sui-user-wallets.php:
# Version: 1.0.6
# define('SUW_VERSION', '1.0.6');

# Commit & Tag
git add .
git commit -m "v1.0.6: New features"
git tag -a v1.0.6 -m "Version 1.0.6"
git push origin main
git push origin v1.0.6

# GitHub Actions erstellt automatisch:
# - Release auf GitHub
# - sui-user-wallets-1.0.6.zip Asset

# WordPress Installationen checken automatisch alle 12h auf Updates
```

---

## API Endpoints

### POST /api/generate-wallet

Generiert neues Ed25519 Keypair für Sui.

**Request:**
```bash
curl -X POST https://your-api.vercel.app/api/generate-wallet
```

**Response:**
```json
{
  "address": "0x9a64353a6b193501407e4715119d4e439769c773d06c82eca1140e12566d94cb",
  "privateKey": "suiprivkey1qz...",
  "publicKey": "AaB9..."
}
```

**Fehler:**
```json
{
  "error": "Failed to generate wallet",
  "details": "..."
}
```

---

### POST /api/get-balance

Prüft SUI Balance einer Wallet-Adresse.

**Request:**
```bash
curl -X POST https://your-api.vercel.app/api/get-balance \
  -H "Content-Type: application/json" \
  -d '{
    "address": "0x9a64353a6b193501407e4715119d4e439769c773d06c82eca1140e12566d94cb"
  }'
```

**Response:**
```json
{
  "address": "0x9a64...",
  "balance": "1.5",
  "balanceInMist": "1500000000"
}
```

---

### POST /api/create-badge

Erstellt Badge NFT für User (erfordert Admin Private Key).

**Request:**
```bash
curl -X POST https://your-api.vercel.app/api/create-badge \
  -H "Content-Type: application/json" \
  -d '{
    "recipientAddress": "0x9a64...",
    "badgeName": "Sui Course Complete",
    "badgeDescription": "Completed Sui Development Course"
  }'
```

**Response:**
```json
{
  "success": true,
  "transactionDigest": "ABC123...",
  "badgeObjectId": "0xDEF456..."
}
```

---

### POST /api/update-progress

Updated Progress für existierendes Badge.

**Request:**
```bash
curl -X POST https://your-api.vercel.app/api/update-progress \
  -H "Content-Type: application/json" \
  -d '{
    "badgeObjectId": "0xDEF456...",
    "newProgress": 75
  }'
```

**Response:**
```json
{
  "success": true,
  "transactionDigest": "GHI789...",
  "newProgress": 75
}
```

---

### GET /api/test

Health Check & Connection Test.

**Request:**
```bash
curl https://your-api.vercel.app/api/test
```

**Response:**
```json
{
  "status": "OK",
  "network": "testnet",
  "adminAddress": "0x1234...",
  "adminBalance": "5.2 SUI",
  "rpcEndpoint": "https://fullnode.testnet.sui.io:443"
}
```

---

## WordPress Plugin Struktur

### Class: SUW_Wallet_Manager

**Methoden:**

```php
// Wallet für User erstellen
create_wallet_for_user($user_id)

// Wallet für User abrufen
get_user_wallet($user_id)

// Alle Wallets abrufen
get_all_wallets()

// Private Key exportieren (decrypted)
export_private_key($user_id)

// Wallet löschen
delete_wallet($user_id)

// Balance prüfen (via Vercel API)
get_wallet_balance($wallet_address)
```

### Class: SUW_Wallet_Crypto

**Methoden:**

```php
// Daten verschlüsseln (AES-256-CBC)
encrypt($data)

// Daten entschlüsseln
decrypt($encrypted_data)

// Encryption Key generieren
get_encryption_key()
```

**Encryption Details:**
- **Algorithm:** AES-256-CBC
- **Key Derivation:** PBKDF2 (AUTH_KEY + SECURE_AUTH_KEY)
- **IV:** Random 16 bytes (stored with ciphertext)
- **Format:** `base64(iv) . '::' . base64(ciphertext)`

### Class: SUW_Auto_Updater

**Methoden:**

```php
// GitHub Release Info abrufen
get_repository_info()

// Update Check
check_for_update($transient)

// Plugin Info anzeigen
plugin_info($res, $action, $args)

// Download URL bereitstellen
download_package($reply, $package, $updater)
```

**Update-Mechanismus:**
- Prüft alle 12 Stunden via GitHub API
- Cache: `suw_github_update_check` Transient
- ZIP Asset von GitHub Releases
- WordPress installiert automatisch

---

## Datenbank Schema

### Table: `wp_sui_user_wallets`

```sql
CREATE TABLE wp_sui_user_wallets (
    id bigint(20) NOT NULL AUTO_INCREMENT,
    user_id bigint(20) NOT NULL,
    wallet_address varchar(66) NOT NULL,
    encrypted_private_key text NOT NULL,
    created_at datetime DEFAULT CURRENT_TIMESTAMP,
    last_balance_check datetime DEFAULT NULL,
    cached_balance varchar(50) DEFAULT '0',
    PRIMARY KEY (id),
    UNIQUE KEY user_id (user_id),
    UNIQUE KEY wallet_address (wallet_address)
) DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

**Felder:**
- `id` - Auto-increment Primary Key
- `user_id` - WordPress User ID (Unique, Foreign Key zu wp_users)
- `wallet_address` - Sui Wallet Address (0x... format)
- `encrypted_private_key` - AES-256 encrypted Private Key
- `created_at` - Wallet Creation Timestamp
- `last_balance_check` - Last Balance Check Timestamp
- `cached_balance` - Cached SUI Balance (String, z.B. "1.5")

**Indizes:**
- Primary Key auf `id`
- Unique Index auf `user_id` (1:1 Beziehung User:Wallet)
- Unique Index auf `wallet_address`

**Queries:**

```php
// User's Wallet abrufen
$wpdb->get_row("SELECT * FROM {$wpdb->prefix}sui_user_wallets WHERE user_id = %d", $user_id);

// Wallet erstellen
$wpdb->insert($table, array(
    'user_id' => $user_id,
    'wallet_address' => $address,
    'encrypted_private_key' => $encrypted_key
));

// Alle Wallets mit User Info
$wpdb->get_results("
    SELECT w.*, u.user_login, u.user_email
    FROM {$wpdb->prefix}sui_user_wallets w
    LEFT JOIN {$wpdb->prefix}users u ON w.user_id = u.ID
    ORDER BY w.created_at DESC
");
```

---

## Security

### Private Key Management

**❌ NIEMALS:**
- Private Keys unverschlüsselt in Datenbank speichern
- Private Keys in Logs ausgeben
- Private Keys in GET-Parametern senden
- Admin Private Key in Frontend-Code

**✅ IMMER:**
- AES-256-CBC Encryption für alle Private Keys
- HTTPS für alle API Calls
- Environment Variables für Admin Key
- Private Key Export nur für Admins
- WordPress Nonces für AJAX Calls

### WordPress Security Best Practices

```php
// 1. Nonce Verification
check_ajax_referer('suw_create_wallet');

// 2. Capability Check
if (!current_user_can('manage_options')) {
    wp_die('Unauthorized');
}

// 3. Input Sanitization
$user_id = absint($_POST['user_id']);
$address = sanitize_text_field($_POST['address']);

// 4. Output Escaping
echo esc_html($wallet_address);
echo esc_url($vercel_api_url);

// 5. SQL Prepared Statements
$wpdb->prepare("SELECT * FROM table WHERE id = %d", $id);
```

### Vercel API Security

```typescript
// 1. Environment Variables
const adminKey = process.env.ADMIN_PRIVATE_KEY;
if (!adminKey) {
    throw new Error('Missing ADMIN_PRIVATE_KEY');
}

// 2. API Authentication (optional)
const apiKey = req.headers['x-api-key'];
if (apiKey !== process.env.API_SECRET_KEY) {
    return res.status(401).json({ error: 'Unauthorized' });
}

// 3. Input Validation
if (!isValidSuiAddress(recipientAddress)) {
    return res.status(400).json({ error: 'Invalid address' });
}

// 4. Error Handling (keine sensiblen Infos leaken)
try {
    // ...
} catch (error) {
    console.error('[Vercel API] Error:', error);
    return res.status(500).json({
        error: 'Internal server error'
    });
}
```

### Sui Blockchain Security

```typescript
// 1. Gas Budget Limits
const tx = new Transaction();
tx.setGasBudget(10000000); // 0.01 SUI

// 2. Transaction Validation
const dryRunResult = await client.dryRunTransactionBlock({
    transactionBlock: await tx.build({ client })
});

if (dryRunResult.effects.status.status !== 'success') {
    throw new Error('Transaction would fail');
}

// 3. Signature Verification
// Sui SDK handled automatisch
```

---

## Troubleshooting

### Häufige Probleme

#### 1. "Table doesn't exist"

**Problem:** Datenbanktabelle `wp_sui_user_wallets` fehlt

**Lösung:**
```bash
# Option A: Via Admin UI
# Rote Warnung im Admin → "Jetzt reparieren" klicken

# Option B: Via fix-table.php
# Upload fix-table.php → Aufrufen → Löschen

# Option C: Plugin reaktivieren
# WP Admin → Plugins → Deactivate → Activate
```

#### 2. "Undefined array key" Warnings im Auto-Updater

**Problem:** GitHub API returnt unexpected Format

**Lösung:**
- Update auf v1.0.3+ (enthält defensive Checks)
- Oder manuell: `delete_transient('suw_github_update_check');`

#### 3. Wallet-Erstellung schlägt fehl

**Debug Steps:**

```php
// 1. Check debug.log
tail -f wp-content/debug.log

// 2. Test Vercel API direkt
curl -X POST https://your-api.vercel.app/api/generate-wallet

// 3. Check WordPress Settings
// WP Admin → User Wallets → Einstellungen
// Vercel API URL korrekt?

// 4. Check Private Key Encryption
// wp-config.php: AUTH_KEY und SECURE_AUTH_KEY gesetzt?
```

#### 4. "Invalid mnemonic" Error

**Problem:** Vercel nutzt falsches Key-Format

**Lösung:**
```typescript
// In sui-client.ts sicherstellen:
import { decodeSuiPrivateKey } from '@mysten/sui.js/cryptography';

if (config.privateKey.startsWith('suiprivkey')) {
    this.keypair = Ed25519Keypair.fromSecretKey(
        decodeSuiPrivateKey(config.privateKey).secretKey
    );
}
```

#### 5. Badge-Erstellung schlägt fehl

**Check:**
```bash
# 1. Admin Wallet hat genug SUI?
curl https://your-api.vercel.app/api/test

# 2. Contract Addresses korrekt?
echo $PACKAGE_ID
echo $BADGE_TYPE

# 3. Network korrekt?
echo $SUI_NETWORK  # sollte "testnet" sein
```

#### 6. Auto-Update funktioniert nicht

**Debug:**
```php
// 1. Cache löschen
delete_transient('suw_github_update_check');

// 2. GitHub API manuell testen
$url = "https://api.github.com/repos/utakapp/sui-user-wallets/releases/latest";
$response = wp_remote_get($url);
print_r(json_decode(wp_remote_retrieve_body($response)));

// 3. Check Plugin Ordner Name
// Muss sein: /wp-content/plugins/sui-user-wallets/
// NICHT:     /wp-content/plugins/sui-user-wallets-main/
```

---

## Development Workflow

### Feature hinzufügen

```bash
# 1. Branch erstellen
git checkout -b feature/neue-funktion

# 2. Code schreiben
# - WordPress: edit sui-user-wallets.php oder includes/*.php
# - Vercel: edit api/*.ts oder lib/*.ts

# 3. Testen
# Lokal testen wie oben beschrieben

# 4. Commit
git add .
git commit -m "feat: Neue Funktion hinzugefügt"

# 5. Push & PR
git push origin feature/neue-funktion
# GitHub PR erstellen

# 6. Merge to main
# Nach Review: Merge PR

# 7. Release
# main → auto-deploys to staging (via GitHub Actions)
# Tag erstellen → auto-deploys to production & creates release
```

### Version Bump

```bash
# 1. Version in sui-user-wallets.php ändern
# * Version: 1.0.6
# define('SUW_VERSION', '1.0.6');

# 2. CHANGELOG.md updaten (optional)

# 3. Commit
git add .
git commit -m "chore: Bump version to 1.0.6"

# 4. Tag & Push
git tag -a v1.0.6 -m "Version 1.0.6"
git push origin main
git push origin v1.0.6

# 5. GitHub Actions erstellt automatisch Release
# WordPress Installationen erhalten Update innerhalb 12h
```

---

## Useful Commands

### WordPress

```bash
# Plugin aktivieren via WP-CLI
wp plugin activate sui-user-wallets

# Datenbank-Tabellen erstellen
wp eval 'do_action("after_switch_theme");'

# User erstellen (zum Testen)
wp user create testuser test@example.com --role=subscriber

# Transients löschen
wp transient delete suw_github_update_check

# Debug-Log live anzeigen
tail -f wp-content/debug.log
```

### Sui CLI

```bash
# Neues Wallet generieren
sui keytool generate ed25519

# Active Address anzeigen
sui client active-address

# Balance prüfen
sui client gas

# Contract deployen
sui client publish --gas-budget 100000000

# Object Details
sui client object 0x...

# Transaction Details
sui client transaction 0x...
```

### Git

```bash
# Status
git status

# Log
git log --oneline

# Tags
git tag -l

# Remote info
git remote -v

# Letzten Commit rückgängig (lokal)
git reset --soft HEAD~1
```

---

## Resources

### Documentation

- **WordPress Plugin Handbook:** https://developer.wordpress.org/plugins/
- **Sui Documentation:** https://docs.sui.io/
- **Sui TypeScript SDK:** https://github.com/MystenLabs/sui/tree/main/sdk/typescript
- **Vercel Documentation:** https://vercel.com/docs

### Tools

- **Sui Explorer (Testnet):** https://suiexplorer.com/?network=testnet
- **GitHub Repository:** https://github.com/utakapp/sui-user-wallets
- **Vercel Dashboard:** https://vercel.com/dashboard

### Support

- **Issues:** https://github.com/utakapp/sui-user-wallets/issues
- **Sui Discord:** https://discord.gg/sui

---

## Contributors

- **utakapp** - Initial development
- **Claude Sonnet 4.5** - AI Assistant (Code review, documentation)

---

## License

GPL v2 or later

---

**Last Updated:** 2026-01-14
**Current Version:** v1.0.5
**Status:** Production Ready ✅
