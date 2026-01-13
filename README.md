# Sui User Wallets - WordPress Plugin

**Custodial Wallet Management für WordPress User**

Automatische Sui Wallet-Erstellung und -Verwaltung für WordPress User. Ideal für Plattformen, wo User keine Blockchain-Erfahrung haben.

## 🎯 Features

- ✅ **Automatische Wallet-Erstellung** bei User-Registration
- ✅ **Verschlüsselte Speicherung** von Private Keys in WordPress DB
- ✅ **User-freundlich** - Keine Blockchain-Kenntnisse erforderlich
- ✅ **Integration** mit Sui Course Loyalty Plugin
- ✅ **Balance-Checks** direkt von der Blockchain
- ✅ **Admin-Interface** für Wallet-Verwaltung
- ✅ **Vercel API Integration** für sichere Wallet-Generierung

## 📋 Voraussetzungen

1. **WordPress** 5.0+
2. **PHP** 7.4+
3. **Vercel API** deployed (siehe `../vercel-api/README.md`)
4. Optional: **Sui Course Loyalty Plugin** für automatische Badge-Zuweisung

## 🚀 Installation

### Schritt 1: Plugin hochladen

1. Lade das gesamte `wordpress-plugin-wallet` Verzeichnis hoch:
   - Via FTP: `/wp-content/plugins/sui-user-wallets/`
   - Oder: Packe als ZIP und installiere über WordPress Admin → Plugins

2. Gehe zu **WordPress Admin → Plugins**
3. Aktiviere **"Sui User Wallets"**

### Schritt 2: Vercel API konfigurieren

1. Gehe zu **WordPress Admin → User Wallets → User Wallets** (Einstellungen)

2. Fülle **API Integration** aus:

   **Vercel API URL:**
   ```
   https://sui-loyalty-vercel-api.vercel.app
   ```

   **API Secret Key:**
   ```
   uPHYR1+HfXT6fE+4tJaU7zUk2qVsyvDAY4Q0tUhIdrA=
   ```

3. **Einstellungen:**
   - ☑ **Automatische Wallet-Erstellung** bei User-Registration
   - ☑ **Private Key Verschlüsselung** (Empfohlen)
   - ☑ **User dürfen Private Keys exportieren** (Optional)
   - **Netzwerk:** `Testnet` (oder Mainnet für Production)

4. Klicke **"Änderungen speichern"**

### Schritt 3: Wallets für existierende User erstellen (Optional)

Falls Sie bereits User haben:

1. Auf der Einstellungsseite sehen Sie: **"Users ohne Wallet: X"**
2. Klicken Sie **"Wallets für alle existierenden Users erstellen"**
3. Das Plugin erstellt automatisch Wallets für alle User

## 🎨 Verwendung

### Als Administrator

#### Alle Wallets ansehen

**WordPress Admin → User Wallets → All Wallets**

Zeigt Liste aller User-Wallets:
- User Name & Email
- Wallet Address
- Balance
- Erstellungsdatum

#### User-Wallet verwalten

**WordPress Admin → Users → Edit User → Sui Wallet Sektion**

Für jeden User sehen Sie:
- **Wallet Address** (mit Copy-Button)
- **Balance** (mit Refresh-Button)
- **Private Key anzeigen** (falls aktiviert)
- **Wallet erstellen** (falls noch keine vorhanden)

### Als User (Frontend)

#### Shortcode: Wallet anzeigen

Füge diesen Shortcode auf einer Seite ein:
```
[sui_user_wallet]
```

Zeigt dem eingeloggten User:
- Seine Wallet-Adresse
- Balance
- Copy-Button

## 🔗 Integration mit Loyalty Plugin

Das Wallet-Plugin integriert sich automatisch mit **Sui Course Loyalty**:

### Automatische Workflow

1. **User registriert sich** → Wallet wird automatisch erstellt
2. **User kauft Kurs** (via PMPro) → Wallet-Check & Badge-Request
3. **Badge wird erstellt** → Automatisch an User-Wallet gesendet
4. **Progress Update** → Auf User-Wallet-Badge angewendet

### Manuelle Integration

Falls Sie das Loyalty-Plugin manuell verwenden:

```php
// Hole User Wallet Address
$wallet_manager = new SUW_Wallet_Manager();
$address = $wallet_manager->get_user_wallet_address($user_id);

// Verwende Adresse für Badge-Erstellung
// ...
```

## 🔒 Sicherheit

### Private Key Verschlüsselung

**Aktiviert (Empfohlen):**
- Private Keys werden mit AES-256-CBC verschlüsselt
- Encryption Key basiert auf WordPress `AUTH_KEY` + `SECURE_AUTH_KEY`
- Keys sind in der DB nicht im Klartext lesbar

**Wichtig:** Stelle sicher, dass `AUTH_KEY` und `SECURE_AUTH_KEY` in `wp-config.php` definiert sind!

### Private Key Export

**Aktiviert (Standard):**
- Admins können Private Keys exportieren
- Jeder Export wird geloggt
- User können ihre eigenen Keys sehen

**Deaktiviert:**
- Niemand kann Private Keys exportieren
- Höhere Sicherheit, aber User können Wallet nicht woanders verwenden

**Empfehlung für Production:**
- Deaktivieren, falls User ihre Wallets nicht exportieren müssen
- Aktivieren, falls User Wallet-Ownership haben sollen

### Best Practices

1. **Backups:**
   - Regelmäßige DB-Backups erstellen
   - Private Keys gehen verloren, wenn DB verloren geht

2. **Zugriffsrechte:**
   - Nur vertrauenswürdige Admins sollten Zugriff haben
   - Private Key Export nur für Super-Admins aktivieren

3. **Testnet vs Mainnet:**
   - Teste IMMER auf Testnet zuerst
   - Auf Mainnet haben Wallets echte SUI-Coins

4. **AUTH_KEY Security:**
   - Ändere niemals `AUTH_KEY` in `wp-config.php` nach Wallet-Erstellung
   - Sonst können Private Keys nicht mehr entschlüsselt werden!

## 🧪 Testing

### Test 1: Wallet-Generierung

1. Gehe zu **Users → Add New**
2. Erstelle einen Test-User
3. Gehe zu **User Wallets → All Wallets**
4. Prüfe ob Wallet automatisch erstellt wurde

### Test 2: Balance-Check

1. Gehe zu **Users → Edit User**
2. Scrolle zu **Sui Wallet** Sektion
3. Klicke **"Refresh"** bei Balance
4. Balance sollte von Blockchain geladen werden (0.0 SUI bei neuer Wallet)

### Test 3: Private Key Export

1. Aktiviere **"User dürfen Private Keys exportieren"** in Einstellungen
2. Gehe zu User-Profil
3. Klicke **"Private Key anzeigen"**
4. Private Key sollte angezeigt werden (suiprivkey1...)

### Test 4: Loyalty Integration

Voraussetzung: Sui Course Loyalty Plugin installiert

1. Erstelle Badge Request für Test-User
2. Wallet-Adresse sollte automatisch verwendet werden
3. Badge sollte an User-Wallet gesendet werden

## 📊 API Endpoints

Das Plugin verwendet folgende Vercel API Endpoints:

### POST `/api/generate-wallet`

Erstellt neue Sui Wallet

**Response:**
```json
{
  "success": true,
  "data": {
    "address": "0x...",
    "privateKey": "suiprivkey1...",
    "network": "testnet"
  }
}
```

### POST `/api/get-balance`

Holt Balance von Blockchain

**Request:**
```json
{
  "address": "0x..."
}
```

**Response:**
```json
{
  "success": true,
  "data": {
    "address": "0x...",
    "balance": "1.5",
    "network": "testnet"
  }
}
```

## 🗄️ Datenbank

### Tabelle: `wp_sui_user_wallets`

| Feld | Typ | Beschreibung |
|------|-----|--------------|
| `id` | bigint | Primary Key |
| `user_id` | bigint | WordPress User ID |
| `wallet_address` | varchar(66) | Sui Address (0x...) |
| `encrypted_private_key` | text | Verschlüsselter Private Key |
| `created_at` | datetime | Erstellungszeitpunkt |
| `last_balance_check` | datetime | Letzter Balance-Check |
| `cached_balance` | varchar(50) | Gecachte Balance in SUI |

## 🔧 Troubleshooting

### Fehler: "API Fehler: Could not resolve host"

**Problem:** WordPress kann Vercel API nicht erreichen

**Lösung:**
1. Prüfe Vercel API URL (korrekt eingegeben?)
2. Teste im Browser: `https://sui-loyalty-vercel-api.vercel.app/api/hello`
3. Prüfe Server-Firewall (erlaubt ausgehende HTTPS Requests?)

### Fehler: "Encryption failed"

**Problem:** PHP OpenSSL Extension fehlt

**Lösung:**
```bash
# Ubuntu/Debian
sudo apt-get install php-openssl

# CentOS/RHEL
sudo yum install php-openssl

# Restart Apache/Nginx
sudo service apache2 restart
```

### Fehler: "Decryption failed"

**Problem:** `AUTH_KEY` wurde geändert nach Wallet-Erstellung

**Lösung:**
- **Kritisch!** Keys können nicht mehr entschlüsselt werden
- Restore old `AUTH_KEY` from backup
- Oder: Erstelle neue Wallets für alle User (alte gehen verloren)

### Wallets werden nicht automatisch erstellt

**Problem:** Auto-Create ist deaktiviert

**Lösung:**
1. Gehe zu **User Wallets → Einstellungen**
2. Aktiviere ☑ **"Automatisch Wallet für neue User erstellen"**
3. Für existierende User: Klicke **"Wallets für alle existierenden Users erstellen"**

## 📝 FAQ

### Sind Custodial Wallets sicher?

**Pros:**
- User-freundlich, keine Blockchain-Kenntnisse nötig
- Keine Gefahr, dass User Private Keys verlieren
- Einfache Integration

**Cons:**
- Platform hat Kontrolle über User-Wallets
- Bei DB-Hack könnten Private Keys kompromittiert werden
- User müssen der Platform vertrauen

**Empfehlung:**
- Für kleine Beträge (Badges, kleine NFTs): ✅ Gut geeignet
- Für große Werte: ❌ Besser Non-Custodial (User verwaltet selbst)

### Können User ihre Wallets exportieren?

Ja, falls **"User dürfen Private Keys exportieren"** aktiviert ist:
1. User sieht Private Key in seinem Profil
2. Kann ihn in Sui Wallet importieren (z.B. Sui Wallet Browser Extension)
3. Hat dann volle Kontrolle außerhalb von WordPress

### Was passiert bei User-Löschung?

Standardmäßig: Wallet bleibt in DB

**Option 1: Manuelles Cleanup**
- Admin löscht Wallet manuell aus **All Wallets**

**Option 2: Automatisches Cleanup** (TODO für v2.0)
- Hook auf `deleted_user` Event
- Wallet wird automatisch gelöscht

### Wie viele Wallets kann ich erstellen?

**Vercel Free Tier:**
- 100,000 Function Invocations/Monat
- = 100,000 Wallet-Generierungen

**Kosten:**
- Testnet: Kostenlos
- Mainnet: ~0.001 SUI pro Wallet-Creation Transaction (falls nötig)

## 🚀 Roadmap

- [ ] Bulk Wallet Creation Interface
- [ ] Wallet Import (User bringt eigene Wallet)
- [ ] Multi-Signature Support
- [ ] Wallet Recovery via Email
- [ ] Gas-less Transactions (Platform zahlt Gas)
- [ ] Export User Wallet History
- [ ] Analytics Dashboard

## 📄 Lizenz

MIT

## 💬 Support

**GitHub Repository:** https://github.com/utakapp/sui-loyalty-vercel-api

**Issues:** https://github.com/utakapp/sui-loyalty-vercel-api/issues

---

**Status:** ✅ Production Ready (Testnet)
**Version:** 1.0.0
**Letzte Aktualisierung:** 12. Januar 2026
