# ⚠️ WICHTIG: Korrekte Installation

## Problem erkannt

Dein Plugin ist aktuell in diesem Ordner installiert:
```
/wp-content/plugins/sui-user-wallets-main/
```

Das ist **falsch**! Der Ordner sollte sein:
```
/wp-content/plugins/sui-user-wallets/
```

## Warum ist das falsch?

Du hast den **main branch als ZIP** heruntergeladen statt das **offizielle Release**.

### Was du gemacht hast (falsch):
1. GitHub → Code → Download ZIP
2. → Ergebnis: `sui-user-wallets-main.zip`
3. → Installiert als: `/sui-user-wallets-main/`

### Was du tun sollst (richtig):
1. GitHub → Releases → v1.0.3
2. Download: `sui-user-wallets-1.0.3.zip`
3. → Installiert als: `/sui-user-wallets/`

---

## ✅ Korrekte Installation - Schritt für Schritt

### Schritt 1: Altes Plugin deinstallieren

```
WordPress Admin → Plugins → "Sui User Wallets"
→ Deactivate
→ Delete
```

**Wichtig:** Notiere dir vorher diese Einstellungen:
- Vercel API URL
- Vercel API Key
- Alle anderen Einstellungen

### Schritt 2: Richtiges ZIP herunterladen

**Gehe zu:**
https://github.com/utakapp/sui-user-wallets/releases/latest

**Download:**
```
sui-user-wallets-1.0.3.zip  (46 KB)
```

**NICHT den grünen "Code" Button verwenden!**

### Schritt 3: Plugin installieren

```
WordPress Admin → Plugins → Add New → Upload Plugin
→ Wähle: sui-user-wallets-1.0.3.zip
→ Install Now
→ Activate
```

### Schritt 4: Datenbanktabelle erstellen

Nach der Aktivierung siehst du eine rote Warnung:

```
┌────────────────────────────────────────┐
│ Sui User Wallets: Datenbanktabelle fehlt!│
│ Das Plugin kann keine Wallets speichern.│
│                                         │
│ [Jetzt reparieren]                     │
└────────────────────────────────────────┘
```

**Klicke auf "Jetzt reparieren"** → Fertig!

### Schritt 5: Einstellungen wiederherstellen

```
WordPress Admin → User Wallets (Menü links)
→ Trage ein:
   - Vercel API URL: https://deine-vercel-url.vercel.app
   - Vercel API Key: (falls vorhanden)
→ Save Changes
```

### Schritt 6: Testen

```
WordPress Admin → Users → Add New
Username: testuser
Email: test@example.com
→ Save

→ Gehe zu User-Profil
→ Solltest du sehen: "Wallet Address: 0x..."
```

---

## 🔍 Wie erkenne ich die korrekte Installation?

### Via FTP/SFTP:
```bash
# Richtig:
/wp-content/plugins/sui-user-wallets/
├── sui-user-wallets.php
├── includes/
└── ...

# Falsch:
/wp-content/plugins/sui-user-wallets-main/
```

### Via WordPress Admin:
```
Plugins → Installed Plugins

Richtig:
┌──────────────────────────────────────┐
│ Sui User Wallets                     │
│ Version: 1.0.3                       │
│ By utakapp                           │
│ Activate | Edit | Delete             │
└──────────────────────────────────────┘

Falsch:
┌──────────────────────────────────────┐
│ sui-user-wallets-main                │
│ Version: 1.0.3                       │
└──────────────────────────────────────┘
```

---

## ⚡ Schnellste Methode (wenn bereits installiert)

Falls du das Plugin bereits aus dem falschen ZIP installiert hast:

### Via FTP/SFTP:

1. **Lade das richtige ZIP hoch:**
   ```
   Local:  sui-user-wallets-1.0.3.zip
   Remote: /tmp/sui-user-wallets-1.0.3.zip
   ```

2. **Entpacke es:**
   ```bash
   ssh your-user@your-server.com
   cd /path/to/wordpress/wp-content/plugins
   rm -rf sui-user-wallets-main
   unzip /tmp/sui-user-wallets-1.0.3.zip
   # Sollte erstellen: sui-user-wallets/
   ```

3. **Via WordPress:**
   ```
   WordPress Admin → Plugins
   → Activate "Sui User Wallets"
   → Klick "Jetzt reparieren"
   ```

---

## 📋 Checkliste

- [ ] Altes Plugin deinstalliert (`sui-user-wallets-main`)
- [ ] Richtiges ZIP von Releases-Seite heruntergeladen
- [ ] Plugin installiert als `sui-user-wallets/`
- [ ] Plugin aktiviert
- [ ] "Jetzt reparieren" Button geklickt
- [ ] Datenbanktabelle erstellt (keine Fehler mehr in debug.log)
- [ ] Vercel API URL eingetragen
- [ ] Test-User erstellt → Wallet wurde angelegt

---

## 🐛 Troubleshooting

### "Ich sehe keinen 'Jetzt reparieren' Button"

**Ursache:** Du bist nicht als Admin eingeloggt

**Lösung:**
- Logge dich als Administrator ein
- Refresh die Seite

### "Button wurde geklickt, aber Tabelle existiert nicht"

**Ursache:** Datenbank-Permissions

**Lösung:**
Verwende fix-table.php:
```
1. Upload: fix-table.php ins WordPress-Root
2. Aufruf: https://deine-domain.de/fix-table.php
3. Löschen: fix-table.php danach löschen!
```

### "Auto-Update funktioniert nicht"

**Ursache:** Plugin in falschem Ordner

**Lösung:**
- Deinstalliere Plugin
- Installiere aus offiziellem Release ZIP
- Auto-Update prüft alle 12 Stunden

---

## ✅ Nach korrekter Installation

### Funktioniert:
✅ Auto-Update alle 12 Stunden
✅ Automatische Wallet-Erstellung bei User-Registrierung
✅ Wallet-Anzeige in User-Profilen
✅ Vercel API Integration
✅ Verschlüsselung der Private Keys

### Debug Log sollte zeigen:
```
[Sui User Wallets] Auto-creating wallet for user 5
[Sui User Wallets] Wallet generated: 0x...
[Sui User Wallets] Successfully created wallet
```

**Keine Fehler mehr!** 🎉

---

**Hinweis:** Diese Anleitung kannst du löschen sobald das Plugin korrekt installiert ist.
