# WordPress Plugin - Automatisiertes Deployment

Vollständiger Leitfaden für automatisiertes Plugin-Deployment.

## 📋 Übersicht

Wir bieten **4 Deployment-Methoden**:

1. **GitHub Actions** (CI/CD) - Automatisch bei Git Push
2. **Shell Script** (deploy.sh) - Manuell via Terminal
3. **Makefile** - Schnelle Commands
4. **Manual ZIP** - Klassisches WordPress Upload

---

## 🚀 Methode 1: GitHub Actions (Empfohlen)

### Setup (Einmalig)

#### Schritt 1: Repository auf GitHub erstellen

```bash
cd wordpress-plugin-wallet
git init
git add .
git commit -m "Initial commit"
git branch -M main
git remote add origin https://github.com/YOUR_USERNAME/sui-user-wallets.git
git push -u origin main
```

#### Schritt 2: GitHub Secrets konfigurieren

Gehe zu: **GitHub Repository → Settings → Secrets and variables → Actions → New repository secret**

Füge hinzu:

| Secret Name | Wert | Beschreibung |
|-------------|------|--------------|
| `FTP_SERVER` | `ftp.your-server.com` | Staging FTP Server |
| `FTP_USERNAME` | `your-username` | FTP Username |
| `FTP_PASSWORD` | `your-password` | FTP Password |
| `PROD_FTP_SERVER` | `ftp.production.com` | Production FTP Server |
| `PROD_FTP_USERNAME` | `prod-username` | Production Username |
| `PROD_FTP_PASSWORD` | `prod-password` | Production Password |
| `AUTO_DEPLOY_PRODUCTION` | `false` | Auto-deploy on release? |

#### Schritt 3: Deployment testen

```bash
# Push code to main branch
git add .
git commit -m "Update plugin"
git push

# GitHub Actions deployt automatisch auf Staging!
```

**Prüfe Deployment:**
- GitHub Repository → Actions Tab
- Sieh Log des laufenden Deployments

### Auto-Deployment Workflows

#### A) Automatisches Staging-Deployment

**Trigger:** Push zu `main` Branch

```bash
git add .
git commit -m "Fix bug"
git push
# → Deployed automatisch auf Staging
```

#### B) Manuelles Deployment

**Trigger:** Manuell in GitHub Actions

1. GitHub Repository → Actions → "Deploy WordPress Plugin"
2. Klicke **"Run workflow"**
3. Wähle Environment: `staging` oder `production`
4. Klicke **"Run workflow"**

#### C) Release-Deployment

**Trigger:** Git Tag

```bash
# Create release
git tag -a v1.0.1 -m "Release version 1.0.1"
git push origin v1.0.1

# → Erstellt automatisch:
#    - GitHub Release
#    - ZIP Download
#    - (Optional) Production Deployment
```

---

## 🛠️ Methode 2: Shell Script (deploy.sh)

### Setup (Einmalig)

```bash
# 1. Konfiguration erstellen
cp .env.deploy.example .env.deploy

# 2. Credentials eintragen
nano .env.deploy

# 3. Script ausführbar machen
chmod +x deploy.sh
```

### Deployment ausführen

#### Interaktives Menu

```bash
./deploy.sh
```

Zeigt Menu:
```
1) SFTP Deployment
2) SSH Deployment
3) FTP Deployment
4) Create ZIP (Manual Upload)
5) Local Deployment
6) Exit
```

#### Direkt-Commands

```bash
# SFTP Deployment
./deploy.sh --sftp

# SSH Deployment
./deploy.sh --ssh

# FTP Deployment
./deploy.sh --ftp

# Local Deployment
./deploy.sh --local

# ZIP erstellen
./deploy.sh --zip
```

### Deployment-Methoden Details

#### SFTP Deployment

**Vorteile:**
- Sicher (SSH-verschlüsselt)
- Schnell
- Löscht alte Dateien

**Requirements:**
- SFTP-Zugang zum Server
- `lftp` oder `rsync` installiert

**Installation:**
```bash
# macOS
brew install lftp

# Ubuntu/Debian
sudo apt-get install lftp

# CentOS
sudo yum install lftp
```

**.env.deploy:**
```bash
SFTP_HOST=your-server.com
SFTP_USER=your-username
SFTP_PASSWORD=your-password
SFTP_PORT=22
SFTP_REMOTE_PATH=/home/user/public_html/wp-content/plugins/sui-user-wallets
```

**Deployment:**
```bash
./deploy.sh --sftp
```

#### SSH Deployment (mit rsync)

**Vorteile:**
- Sehr schnell (nur geänderte Dateien)
- Kann WP-CLI commands ausführen
- Keine Passwort-Eingabe nötig (mit SSH Key)

**Requirements:**
- SSH-Zugang
- `rsync` installiert (meist vorinstalliert)

**Setup SSH Key (empfohlen):**
```bash
# Generiere SSH Key (falls noch nicht vorhanden)
ssh-keygen -t rsa -b 4096

# Kopiere Public Key auf Server
ssh-copy-id your-username@your-server.com

# Teste Connection
ssh your-username@your-server.com
```

**.env.deploy:**
```bash
SSH_HOST=your-server.com
SSH_USER=your-username
SSH_PORT=22
SSH_REMOTE_PATH=/var/www/html/wp-content/plugins/sui-user-wallets
RUN_WP_CLI=true  # Optional: führt wp plugin activate aus
```

**Deployment:**
```bash
./deploy.sh --ssh
```

#### FTP Deployment

**Vorteile:**
- Funktioniert überall (fast jeder Hoster hat FTP)
- Einfach

**Nachteile:**
- Langsamer als SFTP/SSH
- Weniger sicher

**.env.deploy:**
```bash
FTP_HOST=ftp.your-server.com
FTP_USER=your-ftp-username
FTP_PASSWORD=your-ftp-password
FTP_REMOTE_PATH=/public_html/wp-content/plugins/sui-user-wallets
```

**Deployment:**
```bash
./deploy.sh --ftp
```

#### Local Deployment

**Perfekt für:**
- Lokale WordPress-Installation (MAMP, XAMPP, etc.)
- Testing vor Production-Deployment

**.env.deploy:**
```bash
LOCAL_WP_PATH=/Users/username/Sites/wordpress
LOCAL_WP_URL=http://localhost:8888
```

**Deployment:**
```bash
./deploy.sh --local
```

---

## ⚙️ Methode 3: Makefile Commands

**Noch einfacher als Shell Script!**

### Setup

```bash
make setup
# → Erstellt .env.deploy und macht deploy.sh executable
```

### Commands

```bash
# SFTP Deployment
make deploy-sftp

# SSH Deployment
make deploy-ssh

# FTP Deployment
make deploy-ftp

# Local Deployment
make deploy-local

# ZIP erstellen
make zip

# Release erstellen
make release VERSION=1.0.1
```

### Workflow-Beispiel

```bash
# 1. Code ändern
nano sui-user-wallets.php

# 2. Auf Staging deployen
make deploy-ssh

# 3. Testen auf Staging
# ...

# 4. Release erstellen (triggert GitHub Actions)
make release VERSION=1.0.2

# 5. Auf Production deployen (manuell)
make deploy-sftp  # oder via GitHub Actions
```

---

## 📦 Methode 4: Manual ZIP Upload

### ZIP erstellen

#### Via Shell Script
```bash
./deploy.sh --zip
# Erstellt: sui-user-wallets-YYYYMMDD-HHMMSS.zip
```

#### Via Makefile
```bash
make zip
```

#### Via CLI
```bash
cd wordpress-plugin-wallet
zip -r ../sui-user-wallets.zip . \
  -x "*.git*" \
  -x "*node_modules*" \
  -x "*.DS_Store" \
  -x "*debug-test.php" \
  -x "*.github*"
```

### ZIP Upload in WordPress

1. **WordPress Admin → Plugins → Add New → Upload Plugin**
2. Wähle die ZIP-Datei aus
3. Klicke **"Install Now"**
4. Klicke **"Activate Plugin"**

---

## 🔄 Version Management

### Version aktualisieren

Vor einem Release müssen 2 Stellen aktualisiert werden:

#### Manuell

**Datei: `sui-user-wallets.php`**
```php
/**
 * Version: 1.0.2
 */

define('SUW_VERSION', '1.0.2');
```

#### Automatisch via Script

```bash
# Update Version
VERSION=1.0.2
sed -i '' "s/Version: .*/Version: $VERSION/" sui-user-wallets.php
sed -i '' "s/SUW_VERSION', '.*'/SUW_VERSION', '$VERSION'/" sui-user-wallets.php

# Commit
git add sui-user-wallets.php
git commit -m "Bump version to $VERSION"
git push

# Release
git tag -a v$VERSION -m "Release version $VERSION"
git push origin v$VERSION
```

### Semantic Versioning

Folge [Semantic Versioning](https://semver.org/):

- **MAJOR** (1.x.x): Breaking Changes
- **MINOR** (x.1.x): Neue Features (backwards-compatible)
- **PATCH** (x.x.1): Bug Fixes

Beispiele:
```bash
v1.0.0 → Initial Release
v1.0.1 → Bug Fix (Encryption Error)
v1.1.0 → New Feature (Bulk Wallet Creation)
v2.0.0 → Breaking Change (New DB Schema)
```

---

## 📊 Deployment-Strategien

### Strategie 1: Staging → Production

```
1. Entwicklung lokal
   ↓ make deploy-local
2. Testing auf Staging
   ↓ git push (Auto-Deploy)
3. Staging getestet → OK
   ↓ make release VERSION=1.0.x
4. Production Deployment
   ↓ GitHub Actions oder make deploy-ssh
5. Monitoring
```

### Strategie 2: Feature Branches

```
1. Feature Branch erstellen
   git checkout -b feature/wallet-export

2. Entwicklung
   make deploy-local

3. Testing
   make deploy-staging

4. Merge zu main
   git checkout main
   git merge feature/wallet-export
   git push

5. Auto-Deploy auf Staging
   (via GitHub Actions)

6. Release
   make release VERSION=1.1.0
```

### Strategie 3: Hotfix

```
1. Hotfix Branch
   git checkout -b hotfix/critical-bug

2. Fix
   nano sui-user-wallets.php

3. Schnelles Deployment
   make deploy-ssh

4. Merge & Release
   git checkout main
   git merge hotfix/critical-bug
   make release VERSION=1.0.3
```

---

## 🔐 Sicherheit

### Credentials schützen

**WICHTIG:**

```bash
# .gitignore prüfen
echo ".env.deploy" >> .gitignore
echo "*.zip" >> .gitignore

# Niemals committen:
git status
# Sollte NICHT zeigen:
#   - .env.deploy
#   - Credentials
#   - Passwords
```

### GitHub Secrets Best Practices

- ✅ Verwende GitHub Secrets für CI/CD
- ✅ Rotation: Ändere Passwords regelmäßig
- ✅ Least Privilege: FTP-User nur für Plugin-Ordner
- ✅ Separate Credentials für Staging/Production
- ❌ Niemals Credentials in Code committen

### SSH Key statt Password

```bash
# Generiere Key
ssh-keygen -t ed25519 -C "deployment@your-domain.com"

# Kopiere auf Server
ssh-copy-id your-user@your-server.com

# In .env.deploy: Password NICHT nötig!
```

---

## 🐛 Troubleshooting

### "Permission denied"

**Problem:** Keine Schreibrechte auf Server

**Lösung:**
```bash
# SSH zum Server
ssh your-user@your-server.com

# Prüfe Permissions
ls -la wp-content/plugins/

# Fixe Permissions
chmod 755 wp-content/plugins/sui-user-wallets
chown -R your-user:your-user wp-content/plugins/sui-user-wallets
```

### "lftp: command not found"

**Problem:** lftp nicht installiert

**Lösung:**
```bash
# macOS
brew install lftp

# Ubuntu/Debian
sudo apt-get install lftp

# CentOS
sudo yum install lftp
```

### "Connection refused"

**Problem:** Falsche Credentials oder Server nicht erreichbar

**Lösung:**
```bash
# Teste FTP Connection
ftp your-server.com

# Teste SSH Connection
ssh your-user@your-server.com

# Teste SFTP Connection
sftp your-user@your-server.com

# Prüfe Firewall
ping your-server.com
```

### GitHub Actions Fehler

**Problem:** Deployment schlägt fehl

**Lösung:**
1. Prüfe Logs: Repository → Actions → Failed Run → Log
2. Prüfe Secrets: Settings → Secrets → Sind alle gesetzt?
3. Prüfe Pfade: Server-Pfad korrekt in Secrets?

---

## 📝 Cheat Sheet

```bash
# Setup
make setup
nano .env.deploy

# Development
make deploy-local

# Staging
git push  # Auto-Deploy

# Manual Staging
make deploy-ssh

# Release
make release VERSION=1.0.2

# Production (nach Release)
# Option 1: Automatisch via GitHub Actions
# Option 2: Manuell
make deploy-sftp

# Emergency Hotfix
./deploy.sh --ssh

# ZIP für Manual Upload
make zip
```

---

## 🎯 Best Practices

1. **Immer testen vor Production**
   - Lokal testen
   - Staging testen
   - Dann Production

2. **Versionierung**
   - Semantic Versioning verwenden
   - CHANGELOG.md pflegen
   - Git Tags für Releases

3. **Backups**
   - Vor jedem Production-Deployment: Backup!
   - WordPress Backup Plugin verwenden
   - DB + Files sichern

4. **Monitoring**
   - Nach Deployment: Plugin testen
   - Error Logs prüfen
   - User Feedback monitoren

5. **Rollback-Plan**
   - Alte Version als ZIP aufheben
   - Schneller Rollback via ZIP-Upload
   - Oder: Git revert + redeploy

---

**Happy Deploying!** 🚀

Bei Fragen: GitHub Issues oder README.md
