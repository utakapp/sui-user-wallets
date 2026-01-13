# Deployment Quick Start

**In 5 Minuten automatisiertes Deployment einrichten!**

## 🚀 Option 1: Shell Script (Einfachste Methode)

### Setup (1 Minute)

```bash
cd wordpress-plugin-wallet

# 1. Konfiguration erstellen
cp .env.deploy.example .env.deploy

# 2. Credentials eintragen
nano .env.deploy
```

**Fülle aus:**
```bash
SFTP_HOST=your-server.com
SFTP_USER=your-username
SFTP_PASSWORD=your-password
SFTP_REMOTE_PATH=/home/user/public_html/wp-content/plugins/sui-user-wallets
```

### Deployment (30 Sekunden)

```bash
# Interactive Menu
./deploy.sh

# Oder direkt:
./deploy.sh --sftp
```

**Fertig!** Plugin ist deployed! ✅

---

## 🔄 Option 2: GitHub Actions (Automatisch)

### Setup (2 Minuten)

#### 1. Repository auf GitHub erstellen

```bash
cd wordpress-plugin-wallet
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/YOUR_USERNAME/sui-user-wallets.git
git push -u origin main
```

#### 2. GitHub Secrets setzen

Gehe zu: **Repository → Settings → Secrets → Actions**

Klicke **"New repository secret"** und füge hinzu:

```
Name: FTP_SERVER
Value: your-server.com

Name: FTP_USERNAME
Value: your-username

Name: FTP_PASSWORD
Value: your-password
```

### Deployment (Automatisch)

```bash
# Ändere Code
nano sui-user-wallets.php

# Push
git add .
git commit -m "Update feature"
git push

# GitHub Actions deployt automatisch! 🎉
```

**Prüfe Status:**
- GitHub Repository → Actions Tab

---

## ⚙️ Option 3: Makefile (Power User)

### Setup

```bash
make setup
nano .env.deploy
```

### Commands

```bash
# SFTP Deployment
make deploy-sftp

# ZIP erstellen
make zip

# Release
make release VERSION=1.0.1
```

---

## 📦 Option 4: Manual ZIP (Kein Setup nötig)

### ZIP erstellen

```bash
cd wordpress-plugin-wallet
zip -r ../sui-user-wallets.zip . \
  -x "*.git*" \
  -x "*.DS_Store" \
  -x "*debug-test.php"
```

### In WordPress hochladen

1. **WordPress Admin → Plugins → Add New → Upload**
2. Wähle ZIP aus
3. Klicke "Install Now"
4. Aktivieren

---

## 🎯 Empfohlener Workflow

### Tägliche Entwicklung

```bash
# 1. Code ändern
nano includes/class-wallet-manager.php

# 2. Lokal testen
make deploy-local
# Öffne: http://localhost:8888

# 3. Auf Staging deployen
make deploy-ssh
# Teste: https://staging.your-site.com

# 4. Commit & Push
git add .
git commit -m "Fix wallet creation bug"
git push
# → Auto-Deploy auf Staging via GitHub Actions
```

### Release auf Production

```bash
# 1. Version bumpen
make release VERSION=1.0.2

# 2. Warten auf GitHub Actions
# → Erstellt automatisch ZIP

# 3. Production Deployment
make deploy-sftp
# Oder: Manual ZIP Upload
```

---

## 🔥 Pro-Tips

### Schnelles Staging-Deployment

```bash
# Direkt deployen ohne Menu
./deploy.sh --ssh
```

### Paralleles Testen

```bash
# Terminal 1: Local
make deploy-local && open http://localhost:8888

# Terminal 2: Staging
make deploy-ssh && open https://staging.your-site.com
```

### Emergency Hotfix

```bash
# 1. Hotfix machen
nano sui-user-wallets.php

# 2. Sofort deployen (skip Git)
./deploy.sh --sftp

# 3. Später committen
git add .
git commit -m "Hotfix: Critical bug"
git push
```

---

## 🐛 Troubleshooting

### "Permission denied"

```bash
chmod +x deploy.sh
```

### "lftp not found"

```bash
# macOS
brew install lftp

# Ubuntu
sudo apt-get install lftp
```

### "Connection refused"

```bash
# Teste Connection
ssh your-user@your-server.com
# Wenn das funktioniert, dann:
nano .env.deploy
# Prüfe Credentials
```

---

## 📁 Was wurde erstellt?

```
wordpress-plugin-wallet/
├── .github/workflows/
│   ├── deploy.yml          ← Auto-Deployment
│   └── release.yml         ← Auto-Release
├── deploy.sh               ← Deployment Script
├── Makefile                ← Quick Commands
├── .env.deploy.example     ← Config Template
└── DEPLOYMENT.md           ← Vollständige Doku
```

---

## 🎓 Nächste Schritte

1. **Wähle eine Deployment-Methode** (Shell Script empfohlen)
2. **Konfiguriere Credentials** (.env.deploy)
3. **Teste Deployment** auf Staging
4. **Richte GitHub Actions ein** (optional, aber empfohlen)
5. **Dokumentiere deinen Workflow** (für dein Team)

---

## 📚 Weitere Infos

- **Vollständige Dokumentation:** `DEPLOYMENT.md`
- **Plugin Setup:** `QUICK_START.md`
- **Plugin Doku:** `README.md`

---

**Happy Deploying!** 🚀

Fragen? Siehe `DEPLOYMENT.md` für Details!
