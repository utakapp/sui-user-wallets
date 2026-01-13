# Auto-Update Quick Start

**Plugin einmal installieren → Updates automatisch von GitHub!**

## ⚡ In 3 Schritten

### 1. GitHub Repository erstellen (2 Min)

```bash
cd wordpress-plugin-wallet

# Repository erstellen auf: https://github.com/new
# Name: sui-user-wallets

# Push
git init
git add .
git commit -m "Initial commit"
git remote add origin https://github.com/YOUR_USERNAME/sui-user-wallets.git
git push -u origin main
```

**Wichtig:** Ändere in `includes/class-auto-updater.php`:
```php
// Zeile 19-20
$this->github_username = 'YOUR_USERNAME';  // Dein GitHub Username!
$this->github_repo = 'sui-user-wallets';
```

### 2. Plugin in WordPress installieren (1 Min)

```bash
# ZIP erstellen
zip -r sui-user-wallets.zip . -x "*.git*" -x "*.DS_Store"

# In WordPress hochladen
# Admin → Plugins → Add New → Upload Plugin
# ZIP auswählen → Install → Activate
```

**Oder via FTP einmalig hochladen nach:**
```
/wp-content/plugins/sui-user-wallets/
```

### 3. Ersten Release erstellen (30 Sek)

```bash
git tag -a v1.0.0 -m "Initial release"
git push origin v1.0.0
```

**GitHub Actions erstellt automatisch:**
- ✅ GitHub Release
- ✅ ZIP Download
- ✅ WordPress kann Updates erkennen

---

## 🔄 Update veröffentlichen

### Bei jedem Update:

```bash
# 1. Version erhöhen in sui-user-wallets.php
#    Zeile 6:  Version: 1.0.1
#    Zeile 21: define('SUW_VERSION', '1.0.1');

# 2. Code ändern
nano includes/class-wallet-manager.php

# 3. Commit & Release
git add .
git commit -m "Version 1.0.1 - Bug fix"
git push

git tag -a v1.0.1 -m "Release v1.0.1"
git push origin v1.0.1
```

**WordPress zeigt automatisch Update an!**

---

## 📱 In WordPress updaten

**Dashboard → Updates**

```
Sui User Wallets
You have version 1.0.0 installed. Update to 1.0.1.
[Update Now]
```

**Oder: Plugins → Check for Updates Link unter Plugin**

---

## ✅ Das war's!

**Vorteile:**
- ✅ Kein FTP mehr nötig
- ✅ Kein manuelles ZIP-Upload
- ✅ Updates mit einem Klick
- ✅ Automatische Benachrichtigungen

**Workflow:**
```
Code ändern → Push → Tag → WordPress zeigt Update → Ein Klick → Fertig!
```

---

## 🐛 Probleme?

### "No updates available"

1. Cache leeren:
   ```
   Plugins → Check for Updates (Link)
   ```

2. Version erhöht?
   ```php
   // sui-user-wallets.php
   Version: 1.0.1  // Muss höher sein!
   ```

3. GitHub Release erstellt?
   ```bash
   git tag v1.0.1
   git push origin v1.0.1
   ```

### "Download failed"

1. GitHub Username korrekt?
   ```php
   // includes/class-auto-updater.php Zeile 19
   $this->github_username = 'YOUR_USERNAME';
   ```

2. Repository Public?
   - GitHub → Repository → Settings
   - Visibility: Public

3. ZIP vorhanden?
   - GitHub → Releases → v1.0.1
   - Sollte ZIP-Datei zeigen

---

## 📚 Mehr Infos

- **Ausführlich:** `AUTO_UPDATE_SETUP.md`
- **Private Repos:** Siehe AUTO_UPDATE_SETUP.md → "Private Repository"
- **GitHub Updater Plugin:** Siehe AUTO_UPDATE_SETUP.md → "Methode 2"

---

**Happy Auto-Updating!** 🚀
