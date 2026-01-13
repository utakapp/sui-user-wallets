# Quick Start - Sui User Wallets Plugin

**In 5 Minuten einsatzbereit!**

## ⚡ Schnellinstallation

### 1. Plugin hochladen (1 Minute)

```bash
# Via FTP oder direkt im WordPress Admin
# Ordner hochladen nach: /wp-content/plugins/sui-user-wallets/
```

Oder via WordPress Admin:
1. Packe `wordpress-plugin-wallet` als ZIP
2. WordPress Admin → Plugins → Installieren → ZIP hochladen
3. Aktiviere das Plugin

### 2. API konfigurieren (2 Minuten)

Gehe zu: **WordPress Admin → User Wallets → User Wallets**

Fülle aus:

```
Vercel API URL: https://sui-loyalty-vercel-api.vercel.app
API Secret Key: uPHYR1+HfXT6fE+4tJaU7zUk2qVsyvDAY4Q0tUhIdrA=
```

Aktiviere:
- ☑ Automatische Wallet-Erstellung
- ☑ Private Key Verschlüsselung
- ☑ User dürfen Private Keys exportieren

Netzwerk: **Testnet**

**→ Änderungen speichern**

### 3. Teste (2 Minuten)

#### Test 1: Erstelle Test-User
```
WordPress Admin → Users → Add New
Username: testuser
Email: test@example.com
→ Speichern
```

#### Test 2: Prüfe Wallet
```
WordPress Admin → User Wallets → All Wallets
```
Du solltest sehen:
- testuser
- Wallet Address: 0x...
- Balance: 0.0 SUI

#### Test 3: Zeige User-Wallet
```
WordPress Admin → Users → Edit testuser
→ Scrolle zu "Sui Wallet" Sektion
```
Du solltest sehen:
- Wallet Address (mit Copy)
- Balance (mit Refresh)
- Private Key anzeigen (Button)

### 4. Optional: Loyalty Integration

Falls **Sui Course Loyalty Plugin** installiert:

```
WordPress Admin → Course Loyalty → Badge Requests → Neuer Request
Student Name: Test User
Course ID: RUST101
→ Badge erstellen
```

Badge wird automatisch an User-Wallet gesendet!

## 🎯 Was passiert jetzt automatisch?

### Bei neuer User-Registration
1. User registriert sich → **Wallet wird sofort erstellt**
2. User hat eigene Sui-Adresse
3. Kann Badges empfangen

### Bei Kurs-Kauf (mit PMPro)
1. User kauft Kurs → **Wallet-Check**
2. Falls keine Wallet → **Wird erstellt**
3. Badge Request → **Automatisch an User-Wallet**
4. Badge-Erstellung → **Auf Blockchain**

### Alles läuft automatisch!

## 📊 Wie User ihre Wallet sehen

### Option 1: Shortcode auf Seite
Erstelle neue Seite: "Meine Wallet"
```
[sui_user_wallet]
```

User sieht:
- Seine Wallet-Adresse
- Balance
- Copy-Button

### Option 2: User-Profil
User geht zu: **WordPress → Profil**
Sieht "Sui Wallet" Sektion (falls Plugin das anzeigt)

## 🔥 Pro-Tipps

### Für existierende User Wallets erstellen
```
WordPress Admin → User Wallets → Einstellungen
→ "Wallets für alle existierenden Users erstellen"
```

### Balance refreshen
```
User Profil → Sui Wallet → Refresh Button
```
Holt aktuelle Balance von Blockchain

### Private Key exportieren
```
User Profil → Sui Wallet → "Private Key anzeigen"
```
⚠️ Niemals teilen!

### Test-SUI auf Testnet holen
```bash
curl --location --request POST 'https://faucet.testnet.sui.io/gas' \
--header 'Content-Type: application/json' \
--data-raw '{"FixedAmountRequest": {"recipient": "0xYOUR_WALLET_ADDRESS"}}'
```

Oder: https://discord.gg/sui → #devnet-faucet

## 🚨 Wichtig für Production!

### Vor dem Go-Live auf Mainnet:

1. **AUTH_KEY Security**
   ```php
   // wp-config.php
   // Stelle sicher, dass AUTH_KEY und SECURE_AUTH_KEY definiert sind!
   define('AUTH_KEY', 'your-unique-key-here');
   define('SECURE_AUTH_KEY', 'your-unique-key-here');
   ```

2. **Backups aktivieren**
   - DB-Backups täglich
   - Private Keys gehen verloren bei DB-Verlust!

3. **Netzwerk wechseln**
   ```
   User Wallets → Einstellungen → Netzwerk: Mainnet
   ```

4. **Optional: Private Key Export deaktivieren**
   ```
   □ User dürfen Private Keys exportieren
   ```
   Höhere Sicherheit auf Mainnet

## ❓ Probleme?

### Wallet wird nicht erstellt
- Prüfe Vercel API URL
- Teste: https://sui-loyalty-vercel-api.vercel.app/api/hello
- Sollte zeigen: `{"success":true,...}`

### "Encryption failed"
```bash
# Installiere PHP OpenSSL
sudo apt-get install php-openssl
sudo service apache2 restart
```

### Mehr Hilfe
Siehe `README.md` für vollständige Dokumentation!

---

**Das war's! Dein Custodial Wallet System ist einsatzbereit!** 🎉

User können jetzt automatisch Sui-Wallets erhalten und Badges empfangen - ohne jemals von Blockchain zu wissen!
