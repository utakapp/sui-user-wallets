# WordPress Debug-Modus aktivieren - Schritt-für-Schritt

## 🎯 Zwei Methoden:

1. **Via wp-config.php** (Empfohlen) - Braucht FTP/SSH Zugriff
2. **Via Debug-Helper Plugin** - Keine FTP nötig, nur WordPress Admin

---

## Methode 1: Via wp-config.php (Empfohlen)

### Schritt 1: Verbinde zu deinem Server

**Via FTP (z.B. FileZilla):**
```
Host: ftp.your-server.com
Username: your-username
Password: your-password
Port: 21
```

**Via SFTP/SSH:**
```bash
ssh your-user@your-server.com
cd /path/to/wordpress
```

### Schritt 2: Öffne wp-config.php

**Via FTP:**
- Rechtsklick auf `wp-config.php` im Root-Verzeichnis
- "Ansicht/Bearbeiten" oder Download & Öffnen mit Texteditor

**Via SSH:**
```bash
nano wp-config.php
# oder
vim wp-config.php
```

### Schritt 3: Finde diese Zeile

Suche nach (meist bei Zeile 80-90):
```php
define( 'WP_DEBUG', false );
```

### Schritt 4: Ersetze mit diesem Code

Ich habe eine fertige Code-Snippet-Datei erstellt:
```
wordpress-plugin-wallet/wp-config-debug-snippet.txt
```

**Kopiere den kompletten Code aus dieser Datei und ersetze:**

```php
define( 'WP_DEBUG', false );
```

**Mit:**

```php
// Debug aktivieren
define( 'WP_DEBUG', true );

// Fehler in Datei loggen (nicht auf Bildschirm)
define( 'WP_DEBUG_LOG', true );

// Fehler NICHT auf Bildschirm anzeigen
define( 'WP_DEBUG_DISPLAY', false );
@ini_set( 'display_errors', 0 );

// Script Debug (optional)
define( 'SCRIPT_DEBUG', true );
```

### Schritt 5: Speichern

**Via FTP:**
- Speichern und Upload bestätigen

**Via SSH:**
- `Ctrl+O` (Speichern in nano)
- `Enter`
- `Ctrl+X` (Beenden)

### Schritt 6: Debug-Log prüfen

**Via FTP:**
```
Gehe zu: /wp-content/debug.log
Download die Datei
Öffne mit Texteditor
```

**Via SSH:**
```bash
cd wp-content
tail -f debug.log
# Zeigt Log in Echtzeit!

# Oder letzte 50 Zeilen:
tail -50 debug.log
```

**Via Browser (mit Debug-Helper Plugin):**
```
WordPress Admin → Tools → Debug Log
```

---

## Methode 2: Via Debug-Helper Plugin

**Falls kein FTP/SSH Zugriff vorhanden!**

### Schritt 1: Plugin installieren

Ich habe ein Debug-Helper Plugin erstellt:
```
wordpress-plugin-wallet/debug-helper/debug-helper.php
```

**Installation:**

1. **Via ZIP:**
   ```bash
   cd debug-helper
   zip -r debug-helper.zip debug-helper.php
   ```
   → WordPress Admin → Plugins → Add New → Upload

2. **Via FTP:**
   - Lade `debug-helper/` Ordner hoch nach:
   - `/wp-content/plugins/debug-helper/`

3. **Aktiviere das Plugin:**
   - WordPress Admin → Plugins → "Debug Helper" aktivieren

### Schritt 2: Debug Log ansehen

```
WordPress Admin → Tools → Debug Log
```

**Features:**
- ✅ Zeigt letzten 500 Log-Zeilen
- ✅ Syntax-Highlighting für Fehler
- ✅ "Log leeren" Button
- ✅ Auto-Refresh
- ✅ Wallet Plugin Debug-Info
- ✅ Test-Buttons

### Schritt 3: wp-config.php trotzdem anpassen

**Das Plugin zeigt Anleitung, wenn Debug noch nicht aktiv ist.**

Falls möglich, aktiviere WP_DEBUG trotzdem via wp-config.php (siehe Methode 1).

---

## 🧪 Debug testen

### Test 1: Wallet-Erstellung debuggen

1. **Debug-Modus aktiviert?**
   ```bash
   # Via SSH
   ls -la wp-content/debug.log
   # Sollte existieren
   ```

2. **Erstelle Test-User:**
   ```
   WordPress Admin → Users → Add New
   Username: debugtest
   Email: debug@test.com
   ```

3. **Prüfe Log:**
   ```bash
   tail -20 wp-content/debug.log
   ```

   **Erwartetes Output:**
   ```
   [13-Jan-2026 10:00:00 UTC] [SUW] Auto-creating wallet for user 123
   [13-Jan-2026 10:00:01 UTC] [SUW] Wallet generated via API: 0x1234...
   [13-Jan-2026 10:00:02 UTC] [SUW] Successfully created wallet: 0x1234...
   ```

   **Bei Fehler:**
   ```
   [13-Jan-2026 10:00:00 UTC] PHP Fatal error: ...
   [13-Jan-2026 10:00:00 UTC] [SUW] Failed to create wallet: API Fehler...
   ```

### Test 2: Live-Debugging

**Terminal 1: Log-Watching**
```bash
ssh your-user@your-server.com
tail -f /path/to/wordpress/wp-content/debug.log
```

**Browser: User erstellen**
```
WordPress → Users → Add New → Speichern
```

**Terminal 1: Siehst du sofort alle Logs!**

---

## 📊 Was wird geloggt?

### Wallet Plugin Logs

**Erfolgreiche Wallet-Erstellung:**
```
[SUW] Auto-creating wallet for user 123
[SUW] Successfully created wallet: 0x...
```

**Wallet-Fehler:**
```
[SUW] Failed to create wallet: API connection failed
[SUW] Error: Invalid Sui address
```

**API Requests:**
```
[SUW Vercel API] Request: POST /api/generate-wallet
[SUW Vercel API] Response: 200 OK
```

**Balance Checks:**
```
[SUW] Checking balance for wallet 0x...
[SUW] Balance: 1.5 SUI
```

### WordPress Core Logs

```
PHP Notice: ...
PHP Warning: ...
PHP Fatal error: ...
```

---

## 🔧 Debug-Modus deaktivieren

**Wenn Debug nicht mehr benötigt wird:**

### Via wp-config.php

Ändere zurück:
```php
define( 'WP_DEBUG', false );
define( 'WP_DEBUG_LOG', false );
define( 'WP_DEBUG_DISPLAY', false );
```

### Via Plugin

Deaktiviere "Debug Helper" Plugin:
```
WordPress → Plugins → Debug Helper → Deactivate
```

### Debug-Log löschen

```bash
# Via SSH
rm wp-content/debug.log

# Via Plugin
Tools → Debug Log → "Log leeren"
```

---

## 🐛 Troubleshooting

### "debug.log wird nicht erstellt"

**Ursache:** WP_DEBUG_LOG nicht aktiviert

**Lösung:**
```php
// wp-config.php
define( 'WP_DEBUG_LOG', true );
```

### "Kann debug.log nicht lesen"

**Ursache:** Permissions

**Lösung:**
```bash
chmod 644 wp-content/debug.log
```

### "Log wird zu groß"

**Ursache:** Viele Fehler

**Lösung:**
```bash
# Log leeren
> wp-content/debug.log

# Oder via Plugin
Tools → Debug Log → "Log leeren"
```

### "Kann wp-config.php nicht bearbeiten"

**Ursache:** Kein FTP/SSH Zugriff

**Lösung:**
- Kontaktiere Hoster für FTP-Zugang
- Oder: Verwende Debug-Helper Plugin (Methode 2)
- Oder: Hoster-Support kann Debug aktivieren

---

## 📁 Dateien die ich erstellt habe:

```
wordpress-plugin-wallet/
├── wp-config-debug-snippet.txt    ← Copy-Paste für wp-config.php
├── debug-helper/
│   └── debug-helper.php           ← WordPress Plugin für Log-Anzeige
└── DEBUG_ANLEITUNG.md             ← Diese Anleitung
```

---

## ✅ Nächste Schritte:

1. **Wähle Methode:**
   - Hast du FTP/SSH? → Methode 1
   - Kein FTP/SSH? → Methode 2

2. **Debug aktivieren:**
   - Folge Anleitung oben

3. **Teste:**
   - User erstellen
   - Log prüfen

4. **Bei Fehlern:**
   - Sende mir debug.log (letzte 50 Zeilen)
   - Oder Screenshot vom Debug-Helper Plugin

---

**Bereit zum Debuggen!** 🐛🔍

Sag Bescheid wenn du Hilfe brauchst!
