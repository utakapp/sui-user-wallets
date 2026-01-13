# Datenbanktabelle erstellen - 3 Methoden

## Problem

Die Tabelle `zabl_sui_user_wallets` existiert nicht. Daher kann das Plugin keine Wallets speichern.

**Fehler im Log:**
```
WordPress database error Table 'dbs15160173.zabl_sui_user_wallets' doesn't exist
```

---

## ✅ Methode 1: Via fix-table.php (Empfohlen)

### Schritt 1: Datei hochladen

Lade die Datei `fix-table.php` in dein WordPress-Root-Verzeichnis hoch (gleiche Ebene wie wp-config.php).

**Via FTP/SFTP:**
```
Local:  wordpress-plugin-wallet/fix-table.php
Remote: /public_html/fix-table.php  (oder /htdocs/ oder /)
```

### Schritt 2: Aufrufen im Browser

```
https://deine-domain.de/fix-table.php
```

Ersetze `deine-domain.de` mit deiner WordPress-Domain.

### Schritt 3: Ergebnis prüfen

Du siehst:
- ✅ "Tabelle erfolgreich erstellt!" → Fertig!
- ❌ Fehler → Siehe Methode 2

### Schritt 4: Datei löschen!

**WICHTIG:** Lösche `fix-table.php` nach erfolgreicher Ausführung aus Sicherheitsgründen!

```bash
# Via SSH
rm /path/to/wordpress/fix-table.php

# Via FTP
# Rechtsklick auf fix-table.php → Löschen
```

---

## ✅ Methode 2: Plugin deaktivieren & reaktivieren

### Schritt 1: Plugin deaktivieren

```
WordPress Admin → Plugins → "Sui User Wallets" → Deactivate
```

### Schritt 2: Plugin reaktivieren

```
WordPress Admin → Plugins → "Sui User Wallets" → Activate
```

### Schritt 3: Prüfen

Erstelle einen Test-User und prüfe debug.log:

```
WordPress Admin → Users → Add New
Username: testuser2
Email: test2@example.com
→ Speichern
```

Dann:
```
WordPress Admin → Tools → Debug Log
```

Erwartetes Log:
```
[Sui User Wallets] Auto-creating wallet for user 8
[Sui User Wallets] Successfully created wallet: 0x...
```

---

## ✅ Methode 3: Via phpMyAdmin / SQL

### Schritt 1: phpMyAdmin öffnen

Gehe zu deinem Hosting-Control-Panel (z.B. Plesk, cPanel) und öffne phpMyAdmin.

### Schritt 2: Datenbank auswählen

Wähle die WordPress-Datenbank: `dbs15160173`

### Schritt 3: SQL ausführen

Klicke auf "SQL" und führe dieses Statement aus:

```sql
CREATE TABLE IF NOT EXISTS zabl_sui_user_wallets (
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

**Oder verwende die bereitgestellte Datei:**
```
wordpress-plugin-wallet/create-table.sql
```

### Schritt 4: Prüfen

```sql
SHOW TABLES LIKE 'zabl_sui_user_wallets';
```

Sollte 1 Zeile zurückgeben.

---

## 🧪 Nach Tabellenerstellung testen

### Test 1: Tabelle prüfen

**Via phpMyAdmin:**
```sql
SELECT * FROM zabl_sui_user_wallets;
```

Sollte leer sein (0 Einträge).

**Via fix-table.php:**
Rufe die Datei nochmal auf - sollte "Tabelle existiert bereits" zeigen.

### Test 2: Wallet erstellen

```
WordPress Admin → Users → Add New
Username: wallettest
Email: wallettest@example.com
→ Speichern
```

### Test 3: Debug Log prüfen

```
WordPress Admin → Tools → Debug Log
```

**Erwartetes Ergebnis:**
```
[13-Jan-2026 10:30:00 UTC] [Sui User Wallets] Auto-creating wallet for user X
[13-Jan-2026 10:30:01 UTC] [Sui User Wallets] Wallet generated: 0x...
[13-Jan-2026 10:30:02 UTC] [Sui User Wallets] Successfully created wallet
```

### Test 4: Datenbank prüfen

```sql
SELECT user_id, wallet_address, created_at FROM zabl_sui_user_wallets;
```

Sollte neuen Eintrag zeigen:
```
user_id | wallet_address          | created_at
--------|------------------------|--------------------
8       | 0x1234...              | 2026-01-13 10:30:02
```

---

## ❌ Troubleshooting

### "Permission denied" beim fix-table.php Aufruf

**Ursache:** Nicht als Admin eingeloggt

**Lösung:**
1. Logge dich als WordPress Admin ein
2. Rufe fix-table.php erneut auf

### "Table already exists" Fehler bei SQL

**Ursache:** Tabelle existiert bereits (gut!)

**Lösung:**
- Prüfe mit `SHOW TABLES LIKE 'zabl_sui_user_wallets';`
- Wenn existiert: Problem liegt woanders
- Prüfe debug.log für andere Fehler

### Tabelle wird erstellt, aber Wallet-Erstellung schlägt fehl

**Mögliche Ursachen:**
1. Vercel API nicht erreichbar
2. Vercel API URL nicht konfiguriert
3. Verschlüsselung schlägt fehl

**Debug:**
```
WordPress Admin → Einstellungen → Sui Wallets
→ Prüfe "Vercel API URL"
→ Klicke "Test Verbindung"
```

### "dbDelta failed" in fix-table.php

**Ursache:** Datenbank-Permissions

**Lösung:**
Verwende Methode 3 (phpMyAdmin) stattdessen.

---

## 📁 Erstellte Dateien:

```
wordpress-plugin-wallet/
├── create-table.sql              ← SQL für phpMyAdmin
├── fix-table.php                 ← WordPress Helper Script
└── TABELLE_ERSTELLEN.md          ← Diese Anleitung
```

---

## ✅ Checkliste

- [ ] Methode gewählt (1, 2 oder 3)
- [ ] Tabelle erstellt
- [ ] fix-table.php gelöscht (falls verwendet)
- [ ] Test-User erstellt
- [ ] Debug Log geprüft
- [ ] Wallet erfolgreich erstellt
- [ ] In Datenbank sichtbar

---

**Fertig!** 🎉

Wenn alles funktioniert, solltest du jetzt:
1. Neue User erstellen können
2. Wallets werden automatisch angelegt
3. Wallet-Adressen in User-Profilen sichtbar

Bei Problemen: Sende mir die letzten 20 Zeilen aus debug.log!
