# Changelog

Alle wichtigen Änderungen an diesem Projekt werden in dieser Datei dokumentiert.

Das Format basiert auf [Keep a Changelog](https://keepachangelog.com/de/1.0.0/),
und dieses Projekt folgt [Semantic Versioning](https://semver.org/lang/de/).

---

## [Unreleased]

---

## [1.0.8] - 2026-01-15

### Added
- **Admin Dashboard** mit umfassenden Statistiken und Charts
  - Gesamt-Wallets, Balance, Aktivität
  - Chart.js Integration für Timeline-Visualisierung
  - Recent Activity Feed
- **Bulk Operations** für effizientes Wallet-Management
  - CSV Export aller Wallets
  - Bulk Balance Check mit AJAX
  - Private Key Export (admin only, mit Warnung)
- **Email Notifications System**
  - Automatische Email bei Wallet-Erstellung
  - HTML Template mit Wallet-Informationen
  - Sicherheitshinweise in Email
- **Transaction History** Anzeige
  - Integration mit Vercel API /api/get-transactions
  - Tabelle mit Datum, Typ, Amount, Status, Tx Hash
  - Links zu Sui Explorer
- **Multi-Wallet Support** (Beta)
  - Grundstruktur für mehrere Wallets pro User
  - Schema-Update SQL bereitgestellt
  - Noch nicht vollständig implementiert
- **Backup & Restore System**
  - Verschlüsselte Backups aller Wallets
  - Upload in wp-content/uploads/sui-wallet-backups/
  - Restore-Funktion mit Konflikterkennung
- **PHPUnit Testing Infrastructure**
  - phpunit.xml.dist Konfiguration
  - composer.json mit Test-Dependencies
  - Test Bootstrap für WordPress
  - Tests für Wallet Crypto und Manager
- **Dokumentation**
  - FAQ.md mit 50+ Fragen und Antworten
  - CONTRIBUTING.md für externe Contributor
  - CHANGELOG.md (diese Datei)
- **Neue Admin Menu Items**
  - Dashboard (erste Submenu-Seite)
  - Bulk Operations
  - Backup & Restore

### Changed
- Success Notice updated zu v1.0.8
- AJAX handler `ajax_dismiss_v108_notice`
- load_classes() erweitert mit 6 neuen Klassen
- auto_create_wallet_on_registration() sendet jetzt Email

### Technical
- 6 neue PHP Klassen (Dashboard, Bulk Ops, Email, Transactions, Multi-Wallet, Backup)
- 4 neue PHPUnit Test-Dateien
- Admin Post Handlers für Bulk-Exports
- AJAX Handler für Bulk Balance Check

---

## [1.0.7] - 2026-01-15

### Added
- **CLAUDE_ONBOARDING.md**: Umfassender Guide für neue Team-Mitglieder
  - 5-Minuten Schnellstart
  - 4-Tage Lernpfad
  - Best Practices für Claude Code
  - Typische Workflows (Feature-Entwicklung, Debugging, Deployment)
  - Cheat Sheet mit häufigen Befehlen
- Success Notice für v1.0.7 Update

### Changed
- Updated AJAX handler to `ajax_dismiss_v107_notice`

---

## [1.0.6] - 2026-01-14

### Added
- **DEVELOPER_NOTES.md**: Vollständige technische Dokumentation (1.100+ Zeilen)
  - Architektur-Diagramm (WordPress → Vercel → Sui)
  - Environment Variables Referenz
  - Contract-Adressen Guide für Testnet
  - Lokale Entwicklung Setup
  - API Endpoints Dokumentation
  - Datenbank Schema
  - Security Best Practices
  - Troubleshooting Guide
  - Development Workflow
- Success Notice für v1.0.6 Update

### Changed
- Updated version to 1.0.6
- Updated AJAX handler to `ajax_dismiss_v106_notice`

---

## [1.0.5] - 2026-01-14

### Added
- Improved success notice for auto-updates
- Test helper scripts included in release:
  - `force-update-check.php` - Force update check
  - `test-notice.php` - Reset notice status

### Changed
- Updated version to 1.0.5
- Updated success notice to v1.0.5 with improved messaging

### Fixed
- Success notice now displays correctly after auto-update

---

## [1.0.4] - 2026-01-13

### Added
- Success notice after auto-update to verify update system works
- AJAX handler for dismissing v1.0.4 notice
- Test release to verify auto-update mechanism

### Changed
- Updated version to 1.0.4
- Success notice shows "Auto-Update erfolgreich!"

---

## [1.0.3] - 2026-01-13

### Added
- **INSTALL_WICHTIG.md**: Guide for correct installation
  - Explains difference between Release ZIP vs main branch ZIP
  - Step-by-step correct installation process

### Fixed
- **Auto-Updater PHP Warnings**: Fixed undefined array key warnings
  - Added defensive checks for GitHub API response fields
  - Fixed: `Undefined array key "zipball_url"`
  - Fixed: `Undefined array key "tag_name"`
  - Fixed: `Undefined array key "body"`
  - Fixed: `Undefined array key "published_at"`
  - Fixed: `Undefined array key "html_url"`
- Auto-updater now handles missing GitHub API fields gracefully

### Changed
- Updated version to 1.0.3
- Improved error handling in `get_repository_info()` method

---

## [1.0.2] - 2026-01-13

### Added
- **Auto-Repair Database Table**: Automatic detection and one-click repair
  - Admin notice when database table is missing
  - "Jetzt reparieren" button creates table via AJAX
  - `ajax_fix_database_table()` method
  - `table_exists()` helper method
  - `check_database_table()` admin notice display
- **Debug Helper Tools**:
  - `debug-helper/debug-helper.php` - WordPress plugin to view debug.log in admin
  - `fix-table.php` - Helper script for manual table creation
  - `test-notice.php` - Notice testing helper
  - **DEBUG_ANLEITUNG.md** - Comprehensive debug guide (German)
  - **TABELLE_ERSTELLEN.md** - Database table creation guide (German)
  - `wp-config-debug-snippet.txt` - Ready-to-copy debug config
  - `create-table.sql` - SQL file for manual table creation

### Fixed
- Database table creation now works reliably
- Activation hook failure no longer breaks plugin
- Plugin works even when uploaded via FTP without activation hook

### Changed
- Updated version to 1.0.2

---

## [1.0.1] - 2026-01-13

### Changed
- Updated author name and metadata in plugin header
- Improved plugin description

---

## [1.0.0] - 2026-01-12

### Added
- **Initial Release** of Sui User Wallets WordPress Plugin
- **Automatic Wallet Creation**: Wallets created automatically on user registration
- **Custodial Wallet Management**: WordPress manages private keys securely
- **AES-256-CBC Encryption**: Private keys encrypted in database
- **Vercel API Integration**: Serverless functions for blockchain operations
- **Auto-Update System**: GitHub-based automatic updates
- **Wallet Manager Class** (`class-wallet-manager.php`):
  - `create_wallet_for_user()` - Create wallet via Vercel API
  - `get_user_wallet()` - Retrieve user's wallet
  - `export_private_key()` - Decrypt and export private key
  - `get_wallet_balance()` - Check SUI balance
  - `get_all_wallets()` - Admin view of all wallets
- **Wallet Crypto Class** (`class-wallet-crypto.php`):
  - AES-256-CBC encryption/decryption
  - Uses WordPress AUTH_KEY + SECURE_AUTH_KEY for encryption
- **Auto-Updater Class** (`class-auto-updater.php`):
  - Checks GitHub releases every 12 hours
  - Automatic update notifications
  - Downloads and installs from GitHub releases
- **Loyalty Integration** (`class-loyalty-integration.php`):
  - Integration with Sui Course Loyalty Plugin
  - Auto-populate wallet addresses for badge requests
- **Admin Interface**:
  - Settings page for Vercel API configuration
  - User profile wallet display
  - "All Wallets" overview page
  - Manual wallet creation button
  - Private key export (admin only)
- **Database Schema**:
  - `wp_sui_user_wallets` table
  - Fields: id, user_id, wallet_address, encrypted_private_key, created_at, last_balance_check, cached_balance
- **Vercel API Endpoints**:
  - `/api/generate-wallet` - Generate new Ed25519 keypair
  - `/api/get-balance` - Query SUI balance
  - `/api/create-badge` - Mint badge NFT
  - `/api/update-progress` - Update badge progress
  - `/api/test` - Health check
- **Documentation**:
  - README.md - Project overview
  - QUICK_START.md - Quick installation guide
  - DEPLOYMENT.md - Deployment instructions
  - DEPLOYMENT_QUICKSTART.md - Quick deployment guide
  - AUTO_UPDATE_SETUP.md - Auto-update system setup
  - AUTO_UPDATE_QUICKSTART.md - Quick auto-update guide
- **Deployment Tools**:
  - `deploy.sh` - Interactive deployment script (SFTP, SSH, FTP, Local, ZIP)
  - `Makefile` - Quick deployment commands
  - `.github/workflows/deploy.yml` - Auto-deploy to staging
  - `.github/workflows/release.yml` - Auto-create releases on tags

### Security
- Private keys encrypted with AES-256-CBC
- HTTPS required for API calls
- WordPress nonce verification for AJAX
- Capability checks for admin operations
- Input sanitization and output escaping
- SQL prepared statements

---

## Links

- **GitHub Repository**: https://github.com/utakapp/sui-user-wallets
- **Latest Release**: https://github.com/utakapp/sui-user-wallets/releases/latest
- **Issues**: https://github.com/utakapp/sui-user-wallets/issues

---

## Legend

- **Added**: New features
- **Changed**: Changes in existing functionality
- **Deprecated**: Soon-to-be removed features
- **Removed**: Removed features
- **Fixed**: Bug fixes
- **Security**: Vulnerability fixes

---

**Note**: All dates in YYYY-MM-DD format (ISO 8601)
