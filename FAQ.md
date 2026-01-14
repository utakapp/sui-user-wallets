# Frequently Asked Questions (FAQ)

Häufig gestellte Fragen zu Sui User Wallets Plugin.

---

## 📋 Inhaltsverzeichnis

1. [Allgemein](#allgemein)
2. [Installation & Setup](#installation--setup)
3. [Wallets](#wallets)
4. [Sicherheit](#sicherheit)
5. [Auto-Update](#auto-update)
6. [Troubleshooting](#troubleshooting)
7. [Vercel API](#vercel-api)
8. [Development](#development)

---

## Allgemein

### Was macht dieses Plugin?

Das Plugin erstellt automatisch Sui Blockchain Wallets für WordPress-User. Es ist ein **Custodial Wallet System** - WordPress verwaltet die Private Keys sicher verschlüsselt in der Datenbank.

### Für wen ist dieses Plugin?

- **WordPress-Admins**: Die ihren Usern Blockchain-Funktionen bieten wollen ohne technische Komplexität
- **Kurs-Anbieter**: Die Badge NFTs für Teilnehmer ausstellen möchten
- **Communities**: Die Loyalty-Programme mit Blockchain umsetzen wollen

### Was ist ein "Custodial Wallet"?

Ein Custodial Wallet bedeutet, dass WordPress die Private Keys verwaltet - nicht der User selbst.

**Vorteile:**
- User brauchen keine Blockchain-Kenntnisse
- Keine MetaMask oder andere Wallets nötig
- Automatische Wallet-Erstellung

**Nachteile:**
- User haben nicht die volle Kontrolle
- WordPress muss die Keys sicher speichern

### Welche Blockchain wird verwendet?

**Sui Blockchain (Testnet)**

Das Plugin ist aktuell für Sui Testnet konfiguriert. Für Mainnet müssten die Environment Variables angepasst werden.

---

## Installation & Setup

### Wie installiere ich das Plugin?

**Schnellste Methode:**

1. Download: https://github.com/utakapp/sui-user-wallets/releases/latest
2. WordPress Admin → Plugins → Add New → Upload Plugin
3. ZIP hochladen → Install Now → Activate
4. Falls Warnung erscheint: "Jetzt reparieren" klicken

**Ausführlich:** Siehe QUICK_START.md

### Was brauche ich für die Installation?

**Minimal:**
- WordPress 5.0+
- PHP 7.4+
- MySQL/MariaDB

**Empfohlen:**
- Vercel Account (für API)
- Sui Testnet Wallet mit etwas SUI (für Badge-Erstellung)
- FTP/SFTP Zugang (für manuelle Updates)

### Muss ich die Vercel API deployen?

**Für volle Funktionalität: Ja**

Ohne Vercel API kann das Plugin:
- ❌ Keine Wallets erstellen
- ❌ Keine Balances prüfen
- ❌ Keine Badges erstellen

Mit Vercel API deployed:
- ✅ Automatische Wallet-Erstellung
- ✅ Balance-Checks
- ✅ Badge NFT Erstellung

**Setup:** Siehe DEVELOPER_NOTES.md "Vercel API Deployment"

### Wie konfiguriere ich die Vercel API URL?

```
WordPress Admin → User Wallets → Einstellungen
→ Vercel API URL: https://your-project.vercel.app
→ Save Changes
```

---

## Wallets

### Werden Wallets automatisch erstellt?

**Ja!** Standardmäßig wird bei jeder User-Registrierung automatisch ein Wallet erstellt.

**Deaktivieren:**
```
WordPress Admin → User Wallets → Einstellungen
→ "Auto-create wallet on registration" → Deaktivieren
```

### Wo sehe ich die Wallet-Adresse eines Users?

**Als Admin:**
```
WordPress Admin → Users → [User auswählen]
→ Scrolle zu "Sui Wallet" Sektion
→ Wallet Address wird angezeigt
```

**Oder:**
```
WordPress Admin → User Wallets → All Wallets
→ Liste aller Wallets mit User-Info
```

### Kann ein User mehrere Wallets haben?

**Aktuell: Nein**

Jeder User kann nur ein Wallet haben (1:1 Beziehung). Multi-Wallet Support ist geplant für zukünftige Versionen.

### Wie exportiere ich einen Private Key?

**Nur für Admins:**

```
WordPress Admin → Users → [User auswählen]
→ Sui Wallet Sektion
→ Klick "Export Private Key" Button
→ Key wird angezeigt (suiprivkey1... Format)
```

**⚠️ Sicherheitswarnung:**
- Private Keys niemals teilen
- Nur auf sicheren Geräten anzeigen
- Nach Export sofort löschen/nicht speichern

### Kann ich existierende Wallets importieren?

**Aktuell: Nein**

Das Plugin erstellt nur neue Wallets. Wallet-Import ist nicht implementiert, aber kann als Feature hinzugefügt werden.

**Workaround:**
Manuell in Datenbank eintragen (nicht empfohlen ohne Encryption).

### Was passiert wenn ich einen User lösche?

Das zugehörige Wallet bleibt in der Datenbank. Es wird **nicht** automatisch gelöscht.

**Manuell löschen:**
```sql
DELETE FROM wp_sui_user_wallets WHERE user_id = [ID];
```

**Tipp:** Exportiere den Private Key vorher falls Backup benötigt.

### Wie prüfe ich die Balance eines Wallets?

**Im User-Profil:**
```
WordPress Admin → Users → [User]
→ Sui Wallet Sektion
→ "Check Balance" Button
→ Balance wird angezeigt
```

**Oder via Sui Explorer:**
```
https://suiexplorer.com/?network=testnet
→ Wallet-Adresse eingeben
```

---

## Sicherheit

### Wie werden Private Keys gespeichert?

**AES-256-CBC verschlüsselt** in der WordPress-Datenbank.

**Encryption Key:**
- Generiert aus `AUTH_KEY` + `SECURE_AUTH_KEY` (wp-config.php)
- 16-Byte Random IV pro Key
- Format: `base64(iv) :: base64(ciphertext)`

**Siehe:** DEVELOPER_NOTES.md "Security" Abschnitt

### Sind die Private Keys sicher?

**Ja, wenn:**
- ✅ WordPress-Datenbank ist sicher (starkes Passwort, kein Public Access)
- ✅ HTTPS ist aktiviert
- ✅ WordPress ist aktuell (keine bekannten Vulnerabilities)
- ✅ AUTH_KEY und SECURE_AUTH_KEY sind stark und geheim

**Zusätzliche Sicherheit:**
- Regelmäßige Backups
- 2FA für WordPress Admin
- Firewall aktiviert
- PHP updates

### Was passiert wenn jemand Zugriff zur Datenbank hat?

**Mit Datenbank-Zugriff:**
- Angreifer sieht verschlüsselte Private Keys
- **Ohne** AUTH_KEY + SECURE_AUTH_KEY: Keys bleiben sicher

**Mit Datenbank + wp-config.php Zugriff:**
- Angreifer kann Keys entschlüsseln
- **Daher:** wp-config.php besonders schützen!

### Sollte ich Backups der Private Keys machen?

**Ja, unbedingt!**

**Backup-Methoden:**

1. **Datenbank-Dump:**
   ```bash
   mysqldump -u user -p database wp_sui_user_wallets > wallets_backup.sql
   ```

2. **Export alle Keys (als Admin):**
   Gehe zu jedem User → Export Private Key → Speichere sicher

3. **Automatisches Backup:**
   Nutze WordPress Backup-Plugins (UpdraftPlus, BackWPup, etc.)

**Speichere Backups:**
- Verschlüsselt (z.B. GPG)
- Offline (nicht auf Server)
- Mehrere Standorte

### Kann ich die Verschlüsselung upgraden?

**Ja**, aber erfordert Code-Änderungen in `class-wallet-crypto.php`.

**Möglich:**
- AES-256-GCM (statt CBC)
- Argon2 für Key Derivation (statt PBKDF2)
- HSM Integration

**Achtung:** Bestehende Keys müssen re-encrypted werden!

---

## Auto-Update

### Wie funktioniert das Auto-Update?

Das Plugin prüft alle **12 Stunden** auf GitHub Releases:

1. Plugin checkt: `https://api.github.com/repos/utakapp/sui-user-wallets/releases/latest`
2. Vergleicht Version mit installierter Version
3. Zeigt "Update verfügbar" in WordPress
4. Du klickst "Update Now"
5. WordPress downloaded ZIP von GitHub
6. Plugin wird aktualisiert
7. Grüne Erfolgsmeldung erscheint

### Kann ich automatische Updates aktivieren?

**Ja!**

```
WordPress Admin → Plugins
→ Bei "Sui User Wallets": Klick "Enable auto-updates"
```

Dann passieren Updates vollautomatisch ohne "Update Now" Button.

### Wie erzwinge ich eine Update-Prüfung?

**Via force-update-check.php:**

1. Upload `force-update-check.php` ins WordPress-Root
2. Rufe auf: `https://deine-domain.de/force-update-check.php`
3. Cache wird gelöscht
4. Neue Prüfung sofort
5. Lösche die Datei danach!

**Siehe:** CLAUDE_ONBOARDING.md für Details

### Woher kommen die Updates?

Von **GitHub Releases:**

https://github.com/utakapp/sui-user-wallets/releases

Jedes Release hat ein ZIP-Asset das WordPress herunterlädt.

### Kann ich Updates deaktivieren?

**Ja**, aber nicht empfohlen.

**Deaktivieren:**
Entferne diese Zeile in `sui-user-wallets.php`:
```php
require_once SUW_PLUGIN_DIR . 'includes/class-auto-updater.php';
```

**Besser:** Installiere manuell wenn Updates verfügbar sind.

---

## Troubleshooting

### "Table doesn't exist" Fehler

**Problem:** Datenbanktabelle `wp_sui_user_wallets` wurde nicht erstellt.

**Lösung 1 (Schnell):**
```
WordPress Admin → Du siehst rote Warnung
→ Klick "Jetzt reparieren" Button
→ Fertig!
```

**Lösung 2 (Plugin reaktivieren):**
```
WordPress Admin → Plugins
→ "Sui User Wallets" → Deactivate
→ "Sui User Wallets" → Activate
```

**Lösung 3 (Manuell):**
Upload `fix-table.php`, rufe auf, lösche danach.

**Siehe:** TABELLE_ERSTELLEN.md für Details

### Wallet-Erstellung schlägt fehl

**Check 1: Vercel API erreichbar?**
```
WordPress Admin → User Wallets → Einstellungen
→ Prüfe "Vercel API URL"
→ Teste: curl https://your-api.vercel.app/api/test
```

**Check 2: Debug Log prüfen:**
```
WordPress Admin → Tools → Debug Log
→ Suche nach "[Sui User Wallets]" Einträgen
```

**Check 3: PHP Extensions:**
```bash
php -m | grep openssl  # Muss installiert sein
```

**Siehe:** DEVELOPER_NOTES.md "Troubleshooting"

### "Invalid mnemonic" Error in Vercel API

**Problem:** Vercel nutzt falsches Key-Format.

**Lösung:**
In `vercel-api/lib/sui-client.ts` sicherstellen:
```typescript
import { decodeSuiPrivateKey } from '@mysten/sui.js/cryptography';

if (config.privateKey.startsWith('suiprivkey')) {
    this.keypair = Ed25519Keypair.fromSecretKey(
        decodeSuiPrivateKey(config.privateKey).secretKey
    );
}
```

**Siehe:** DEVELOPER_NOTES.md "Troubleshooting #4"

### Auto-Update funktioniert nicht

**Mögliche Ursachen:**

1. **Plugin-Ordner falsch:**
   - Muss sein: `/wp-content/plugins/sui-user-wallets/`
   - Nicht: `/wp-content/plugins/sui-user-wallets-main/`

2. **Cache nicht abgelaufen:**
   - Warte 12 Stunden oder
   - Nutze `force-update-check.php`

3. **GitHub API nicht erreichbar:**
   ```bash
   curl https://api.github.com/repos/utakapp/sui-user-wallets/releases/latest
   ```

**Siehe:** INSTALL_WICHTIG.md

### Private Key Export zeigt nichts

**Problem:** Entschlüsselung schlägt fehl.

**Mögliche Ursachen:**
- AUTH_KEY oder SECURE_AUTH_KEY wurde geändert
- Key wurde mit anderer Encryption erstellt
- Datenbank-Eintrag korrupt

**Lösung:**
Wenn Keys geändert wurden: Alte Keys wiederherstellen oder Wallets neu erstellen.

---

## Vercel API

### Muss ich Vercel nutzen?

**Nein**, aber empfohlen.

Du kannst auch:
- AWS Lambda
- Google Cloud Functions
- Eigenen Node.js Server
- Anderen Serverless Provider

**Aber:** Code ist für Vercel optimiert. Anpassungen nötig für andere Plattformen.

### Kostet Vercel etwas?

**Free Tier:**
- 100 GB Bandwidth/Monat
- 100 Stunden Serverless Execution/Monat
- Für kleine Projekte ausreichend!

**Bezahlt ab:**
- $20/Monat für Pro
- Nur bei hohem Traffic nötig

**Siehe:** https://vercel.com/pricing

### Wie deploye ich die Vercel API?

**Via CLI:**
```bash
cd vercel-api
vercel login
vercel --prod
```

**Via GitHub:**
Push zu GitHub → Vercel auto-deploys

**Siehe:** DEVELOPER_NOTES.md "Vercel API Deployment"

### Wo setze ich Environment Variables für Vercel?

**Via CLI:**
```bash
vercel env add ADMIN_PRIVATE_KEY production
vercel env add SUI_NETWORK production
```

**Via Dashboard:**
```
vercel.com/dashboard → Project → Settings → Environment Variables
```

### Kann ich die API lokal testen?

**Ja!**

```bash
cd vercel-api
npm install
vercel dev
# Läuft auf: http://localhost:3000
```

**Teste:**
```bash
curl -X POST http://localhost:3000/api/generate-wallet
```

**Siehe:** DEVELOPER_NOTES.md "Lokale Entwicklung"

---

## Development

### Wie starte ich lokale Entwicklung?

**Siehe:** DEVELOPER_NOTES.md "Lokale Entwicklung" Abschnitt

**Quick:**
1. Vercel API lokal: `cd vercel-api && vercel dev`
2. WordPress lokal: Docker oder XAMPP/MAMP
3. Plugin installieren: Symlink in `/wp-content/plugins/`

### Wo finde ich die technische Dokumentation?

**Hauptdokumentationen:**
- **DEVELOPER_NOTES.md** - Vollständige technische Docs
- **CLAUDE_ONBOARDING.md** - Für neue Entwickler
- **API_REFERENCE.md** - API Endpoints (in DEVELOPER_NOTES)

### Wie kann ich zum Projekt beitragen?

**Siehe:** CONTRIBUTING.md (neu erstellt!)

**Quick:**
1. Fork Repository
2. Branch erstellen
3. Änderungen machen
4. Tests schreiben
5. PR erstellen

### Wie erstelle ich ein neues Release?

```bash
# 1. Version in sui-user-wallets.php ändern
# 2. CHANGELOG.md updaten
# 3. Commit & Tag
git add .
git commit -m "v1.0.X: Description"
git tag -a v1.0.X -m "Version 1.0.X"
git push origin main
git push origin v1.0.X

# 4. GitHub Actions erstellt automatisch Release
```

**Siehe:** DEVELOPER_NOTES.md "Development Workflow"

### Wie führe ich Tests aus?

**WordPress Plugin Tests:**
```bash
cd wordpress-plugin-wallet
composer install
./vendor/bin/phpunit
```

**Vercel API Tests:**
```bash
cd vercel-api
npm test
```

**Siehe:** Testing-Sektion in dieser FAQ

---

## Weitere Hilfe

### Wo finde ich mehr Dokumentation?

**Alle Dokumentations-Dateien:**
- README.md - Projekt-Übersicht
- DEVELOPER_NOTES.md - Technische Architektur
- CLAUDE_ONBOARDING.md - Für neue Team-Mitglieder
- QUICK_START.md - Schnellstart
- DEPLOYMENT.md - Deployment
- AUTO_UPDATE_SETUP.md - Auto-Update System
- DEBUG_ANLEITUNG.md - Debug-Modus
- TABELLE_ERSTELLEN.md - DB Repair
- INSTALL_WICHTIG.md - Korrekte Installation
- CHANGELOG.md - Version History
- FAQ.md - Diese Datei
- CONTRIBUTING.md - Contribution Guide

### Wo kann ich Fragen stellen?

**GitHub Issues:**
https://github.com/utakapp/sui-user-wallets/issues

**Oder:**
Nutze Claude Code mit diesem Projekt:
```
claude
"Lies FAQ.md und hilf mir mit meinem Problem: [beschreibung]"
```

### Wie melde ich einen Bug?

**GitHub Issue erstellen:**

1. Gehe zu: https://github.com/utakapp/sui-user-wallets/issues
2. Klick "New Issue"
3. Beschreibe:
   - Was du gemacht hast
   - Was erwartet wurde
   - Was tatsächlich passiert ist
   - WordPress Version
   - PHP Version
   - Plugin Version
   - Debug Log (letzten 20 Zeilen)

**Template wird automatisch geladen.**

### Wie schlage ich ein neues Feature vor?

**GitHub Discussion:**
https://github.com/utakapp/sui-user-wallets/discussions

Oder GitHub Issue mit Label "enhancement".

---

**Frage nicht beantwortet?**

Erstelle ein Issue: https://github.com/utakapp/sui-user-wallets/issues

Oder lies: DEVELOPER_NOTES.md für technische Details
