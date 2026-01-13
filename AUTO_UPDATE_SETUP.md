# Auto-Update Setup - WordPress Plugin

**Plugin einmal installieren → Automatisch Updates von GitHub erhalten!**

## 🎯 Was wird erreicht?

1. Plugin **einmal** manuell in WordPress installieren
2. Code-Änderung machen und zu GitHub pushen
3. WordPress **erkennt automatisch** neue Version
4. Update mit **einem Klick** in WordPress installieren

**Kein FTP, kein SSH, kein manuelles Upload mehr!** 🎉

---

## ⚡ Quick Setup (5 Minuten)

### Schritt 1: GitHub Repository Setup

```bash
cd wordpress-plugin-wallet

# Git init (falls noch nicht geschehen)
git init
git add .
git commit -m "Initial commit with auto-updater"

# Erstelle GitHub Repository
# Gehe zu: https://github.com/new
# Repository Name: sui-user-wallets
# Public oder Private (beides funktioniert)

# Push
git remote add origin https://github.com/utakapp/sui-user-wallets.git
git branch -M main
git push -u origin main
```

### Schritt 2: Plugin in WordPress installieren

#### Option A: ZIP Upload

```bash
# ZIP erstellen
cd wordpress-plugin-wallet
zip -r ../sui-user-wallets.zip . \
  -x "*.git*" \
  -x "*.DS_Store" \
  -x "*debug-test.php"

# In WordPress hochladen
# WordPress Admin → Plugins → Add New → Upload Plugin
# ZIP auswählen → Install Now → Activate
```

#### Option B: FTP Upload (einmalig)

```bash
# Via FTP oder SFTP
# Lade wordpress-plugin-wallet/ hoch nach:
# /wp-content/plugins/sui-user-wallets/

# Dann in WordPress:
# Plugins → Aktivieren: Sui User Wallets
```

### Schritt 3: Konfiguriere GitHub Repository Info

**Wichtig:** Passe die Werte in `includes/class-auto-updater.php` an:

```php
// Zeile 19-20
$this->github_username = 'utakapp';  // Dein GitHub Username
$this->github_repo = 'sui-user-wallets';  // Dein Repo Name
```

**Oder global in der Haupt-Datei `sui-user-wallets.php`:**

```php
// Zeile 11
* GitHub Plugin URI: utakapp/sui-user-wallets
```

→ Ändere `utakapp` zu deinem GitHub Username!

### Schritt 4: Ersten Release erstellen

```bash
# Commit (falls noch Änderungen)
git add .
git commit -m "Configure auto-updater"
git push

# Erstelle ersten Release
git tag -a v1.0.0 -m "Initial release v1.0.0"
git push origin v1.0.0

# GitHub Actions erstellt automatisch Release mit ZIP!
```

**Prüfe auf GitHub:**
```
https://github.com/utakapp/sui-user-wallets/releases
→ Du solltest sehen: "v1.0.0" mit ZIP Download
```

### Schritt 5: Test - Update prüfen

**In WordPress:**

1. Gehe zu: **Plugins**
2. Unter "Sui User Wallets" siehst du jetzt:
   - **"Check for Updates"** Link
   - **"View Releases"** Link
3. Klicke **"Check for Updates"**
4. WordPress prüft GitHub → Zeigt "Plugin ist aktuell"

---

## 🔄 Workflow: Update veröffentlichen

### Schritt 1: Code ändern

```bash
nano includes/class-wallet-manager.php
# ... Änderungen machen ...
```

### Schritt 2: Version bumpen

**Datei: `sui-user-wallets.php`**

```php
// Zeile 6: Version erhöhen
* Version: 1.0.1  // War: 1.0.0

// Zeile 21: Konstante aktualisieren
define('SUW_VERSION', '1.0.1');  // War: 1.0.0
```

### Schritt 3: Commit & Push

```bash
git add .
git commit -m "Version 1.0.1 - Fix wallet creation bug"
git push
```

### Schritt 4: Release erstellen

```bash
git tag -a v1.0.1 -m "Release v1.0.1 - Bug fixes"
git push origin v1.0.1
```

**GitHub Actions macht automatisch:**
- ✅ Erstellt GitHub Release
- ✅ Generiert ZIP
- ✅ Fügt Changelog hinzu

### Schritt 5: Update in WordPress

**WordPress Admin → Dashboard → Updates**

Du siehst:
```
Sui User Wallets
You have version 1.0.0 installed. Update to 1.0.1.
[Update Now]
```

Klicke **"Update Now"** → Plugin wird von GitHub heruntergeladen und installiert!

---

## 🎨 Zwei Update-Methoden

### Methode 1: Eingebauter Auto-Updater (Standard)

**Wie es funktioniert:**
- Plugin prüft GitHub API auf neue Releases
- Zeigt Update-Benachrichtigung in WordPress
- Download direkt von GitHub
- Keine zusätzlichen Plugins nötig

**Aktiviert:** ✅ Bereits integriert!

**Nutzung:**
```
WordPress → Plugins → "Check for Updates" Link klicken
Oder: WordPress → Dashboard → Updates
```

### Methode 2: GitHub Updater Plugin (Optional)

**Zusätzliches Plugin für mehr Features**

**Features:**
- Auto-Check alle 12 Stunden
- Support für Private Repositories (mit Token)
- Update-Benachrichtigungen
- Branch-Switching

**Installation:**

1. **Installiere GitHub Updater:**
   ```
   WordPress → Plugins → Add New
   → Suche: "GitHub Updater"
   → Install & Activate
   ```

2. **Plugin ist bereits vorbereitet:**
   ```php
   // sui-user-wallets.php Zeile 11-12
   * GitHub Plugin URI: utakapp/sui-user-wallets
   * GitHub Branch: main
   ```

3. **Fertig!** GitHub Updater erkennt automatisch das Plugin

**Für Private Repos:**
```
Settings → GitHub Updater → Settings
→ GitHub Access Token: ghp_xxx...
```

---

## 🔐 Private vs Public Repository

### Public Repository (Einfacher)

**Setup:**
- Repository auf GitHub: Public
- Keine zusätzliche Konfiguration nötig
- Jeder kann Updates herunterladen

**Geeignet für:**
- Open-Source Plugins
- Freie Distribution

### Private Repository (Sicherer)

**Setup benötigt GitHub Token:**

1. **Erstelle GitHub Personal Access Token:**
   ```
   GitHub → Settings → Developer Settings → Personal Access Tokens
   → Generate New Token (classic)
   → Scopes: repo (full control)
   → Generate → Kopiere Token
   ```

2. **In Auto-Updater einbauen:**

   **Datei: `includes/class-auto-updater.php`**

   ```php
   // Zeile 63: Headers hinzufügen
   $response = wp_remote_get($api_url, array(
       'timeout' => 10,
       'headers' => array(
           'Accept' => 'application/vnd.github.v3+json',
           'Authorization' => 'token YOUR_GITHUB_TOKEN_HERE',  // Neu
       )
   ));
   ```

   **ODER: Token als WordPress Konstante:**

   **wp-config.php:**
   ```php
   define('GITHUB_ACCESS_TOKEN', 'ghp_xxxYourTokenHere');
   ```

   **class-auto-updater.php:**
   ```php
   $headers = array('Accept' => 'application/vnd.github.v3+json');
   if (defined('GITHUB_ACCESS_TOKEN')) {
       $headers['Authorization'] = 'token ' . GITHUB_ACCESS_TOKEN;
   }
   ```

---

## 📊 Update-Benachrichtigungen

### WordPress Dashboard

**Nach Update-Check siehst du:**

```
┌─────────────────────────────────────┐
│ Updates Available                    │
├─────────────────────────────────────┤
│ Sui User Wallets                     │
│ You have version 1.0.0 installed.   │
│ Update to 1.0.1.                     │
│ [Update Now] [View Details]          │
└─────────────────────────────────────┘
```

### Plugin-Seite

```
Sui User Wallets
Version 1.0.0 | There is a new version available. View version 1.0.1 details.
Check for Updates | View Releases | ...
```

### Admin Notice (Optional)

Füge in `sui-user-wallets.php` hinzu:

```php
// Im Constructor nach Zeile 56
add_action('admin_notices', array($this, 'update_notice'));

// Neue Methode
public function update_notice() {
    $update_cache = get_site_transient('update_plugins');
    if (isset($update_cache->response[$this->plugin_basename])) {
        $new_version = $update_cache->response[$this->plugin_basename]->new_version;
        echo '<div class="notice notice-warning is-dismissible">';
        echo '<p><strong>Sui User Wallets:</strong> Version ' . $new_version . ' ist verfügbar! ';
        echo '<a href="' . admin_url('plugins.php') . '">Jetzt aktualisieren</a></p>';
        echo '</div>';
    }
}
```

---

## 🔧 Konfiguration & Anpassung

### Auto-Check Interval ändern

**Standard: 12 Stunden**

**Datei: `includes/class-auto-updater.php`**

```php
// Zeile 90
set_transient($this->cache_key, $info, 12 * HOUR_IN_SECONDS);

// Ändern zu:
set_transient($this->cache_key, $info, 6 * HOUR_IN_SECONDS);  // 6 Stunden
set_transient($this->cache_key, $info, 24 * HOUR_IN_SECONDS); // 24 Stunden
```

### Cache manuell leeren

```php
// In WordPress
delete_transient('suw_github_update');
delete_site_transient('update_plugins');
```

### Beta-Releases testen

**Erstelle Pre-Release auf GitHub:**

```bash
git tag -a v1.1.0-beta.1 -m "Beta release"
git push origin v1.1.0-beta.1
```

**Auf GitHub:** Markiere als "Pre-release"

**Auto-Updater anpassen um Beta zu zeigen:**

```php
// class-auto-updater.php Zeile 61
$api_url = "https://api.github.com/repos/{$this->github_username}/{$this->github_repo}/releases";
// Statt /releases/latest
```

---

## 🐛 Troubleshooting

### "No updates available" obwohl neue Version auf GitHub

**Ursachen:**

1. **Cache nicht geleert**
   ```
   Plugins → Check for Updates (Link unter Plugin)
   ```

2. **Version nicht erhöht**
   ```php
   // sui-user-wallets.php
   * Version: 1.0.1  // Muss höher sein als installiert
   define('SUW_VERSION', '1.0.1');
   ```

3. **GitHub Release fehlt**
   ```bash
   git tag -a v1.0.1 -m "Release"
   git push origin v1.0.1
   ```

4. **Kein ZIP in Release**
   → GitHub Actions muss laufen und ZIP erstellen
   → Prüfe: Repository → Actions

### "Download failed"

**Ursachen:**

1. **Private Repo ohne Token**
   → Füge GitHub Token hinzu (siehe oben)

2. **ZIP-Asset fehlt in Release**
   → GitHub Actions muss ZIP hochladen
   → Prüfe `.github/workflows/release.yml`

3. **WordPress kann GitHub nicht erreichen**
   ```bash
   # Teste vom Server
   curl https://api.github.com/repos/utakapp/sui-user-wallets/releases/latest
   ```

### "Installation failed"

**Ursachen:**

1. **Permissions**
   ```bash
   chmod 755 wp-content/plugins/sui-user-wallets
   ```

2. **Plugin aktiv während Update**
   → WordPress deaktiviert automatisch → OK

3. **ZIP-Struktur falsch**
   → Prüfe dass ZIP Plugin-Root enthält (nicht Unterordner)

---

## 📈 Monitoring & Logs

### Update-Statistik sehen

```php
// In class-auto-updater.php hinzufügen
private function log_update_check($version) {
    error_log('[SUW Auto-Updater] Checked for updates. Current: ' . $this->version . ', Latest: ' . $version);
}
```

### WordPress Debug-Log

```php
// wp-config.php
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);

// Log bei Update-Check
// wp-content/debug.log
```

---

## 🚀 Best Practices

### 1. Semantic Versioning

```
v1.0.0 → Initial Release
v1.0.1 → Bug Fix
v1.1.0 → New Feature
v2.0.0 → Breaking Change
```

### 2. Changelog pflegen

**GitHub Release Body:**

```markdown
## What's New

### Added
- Bulk wallet creation feature
- Export wallet history

### Fixed
- Encryption error on some servers
- Balance refresh issue

### Changed
- Improved performance for large user bases
```

→ Wird in WordPress Update-Details angezeigt!

### 3. Testing vor Release

```bash
# Lokale Testversion
git tag v1.1.0-rc.1
git push origin v1.1.0-rc.1

# Teste auf Staging
# Wenn OK:
git tag v1.1.0
git push origin v1.1.0
```

### 4. Rollback-Plan

```bash
# Falls neues Update Probleme macht:
# Erstelle Hotfix-Release mit alter Version

git tag -a v1.0.2 -m "Rollback to stable"
git push origin v1.0.2

# User können zurück-updaten
```

---

## ✅ Checklist

Setup fertig wenn:

- [ ] GitHub Repository erstellt (Public oder Private)
- [ ] GitHub Username in class-auto-updater.php angepasst
- [ ] Plugin in WordPress installiert & aktiviert
- [ ] Erster Release (v1.0.0) auf GitHub erstellt
- [ ] GitHub Actions erstellt ZIP
- [ ] "Check for Updates" Link funktioniert in WordPress
- [ ] Test-Update durchgeführt

---

## 🎯 Zusammenfassung

**Einmalig:**
1. GitHub Repository erstellen
2. Plugin in WordPress installieren

**Bei jedem Update:**
1. Code ändern
2. Version bumpen
3. Push + Tag erstellen
4. WordPress zeigt Update an
5. Ein Klick → Update installiert

**Fertig! Kein FTP, kein SSH mehr nötig!** 🎉

---

Bei Fragen: Siehe `DEPLOYMENT.md` oder GitHub Issues!
