# Claude Code Onboarding Guide

**Für neue Kollegen: So arbeitest du mit diesem Projekt in Claude Code**

---

## 📋 Was ist Claude Code?

Claude Code ist ein AI-Coding-Assistant CLI-Tool von Anthropic, das direkt in deinem Terminal läuft und Zugriff auf dein Dateisystem hat.

**Installation:** https://docs.anthropic.com/en/docs/claude-code

---

## 🚀 Schnellstart: Projekt mit Claude Code verstehen

### Schritt 1: Claude Code starten

```bash
# Gehe ins Projekt-Verzeichnis
cd /pfad/zu/wordpress-plugin-wallet

# Starte Claude Code
claude

# Oder direkt mit dem Projekt:
claude --project wordpress-plugin-wallet
```

### Schritt 2: Erste Konversation - Projekt-Übersicht

Wenn Claude Code startet, sage einfach:

```
"Lies bitte DEVELOPER_NOTES.md und erkläre mir die Architektur des Projekts."
```

**Claude wird:**
- Die Datei automatisch lesen
- Dir die Architektur erklären (WordPress → Vercel → Sui)
- Die wichtigsten Komponenten zeigen
- Fragen zu spezifischen Details beantworten

### Schritt 3: Dokumentation erkunden

Du kannst Claude bitten, spezifische Dokumentations-Dateien zu lesen:

```
"Zeige mir wie ich das Projekt lokal starten kann."
→ Claude liest DEVELOPER_NOTES.md Abschnitt "Lokale Entwicklung"

"Wie deploye ich das Plugin?"
→ Claude liest DEPLOYMENT.md oder DEPLOYMENT_QUICKSTART.md

"Wie funktioniert das Auto-Update System?"
→ Claude liest AUTO_UPDATE_SETUP.md
```

---

## 📚 Wichtige Dokumentations-Dateien

### Für neuen Kollegen - Lesen in dieser Reihenfolge:

#### 1. **README.md** (falls vorhanden)
   ```
   "Lies README.md und gib mir eine Projekt-Übersicht"
   ```
   - Was macht das Projekt?
   - Für wen ist es?

#### 2. **DEVELOPER_NOTES.md** ⭐ **Start hier!**
   ```
   "Lies DEVELOPER_NOTES.md komplett und fasse die wichtigsten Punkte zusammen"
   ```
   - **Vollständige technische Dokumentation**
   - Architektur-Diagramm
   - Environment Variables
   - Lokale Entwicklung
   - API Endpoints
   - Datenbank Schema
   - Security
   - Troubleshooting

#### 3. **QUICK_START.md**
   ```
   "Lies QUICK_START.md und hilf mir das Plugin zu installieren"
   ```
   - Schnellste Methode für erste Installation
   - WordPress-Setup
   - Vercel-Setup

#### 4. **DEPLOYMENT.md** oder **DEPLOYMENT_QUICKSTART.md**
   ```
   "Erkläre mir den Deployment-Prozess"
   ```
   - Wie deploye ich Änderungen?
   - FTP/SFTP/GitHub Actions

#### 5. **AUTO_UPDATE_SETUP.md**
   ```
   "Wie funktioniert das Auto-Update System?"
   ```
   - GitHub Releases
   - WordPress Auto-Updater
   - Versionierung

---

## 💡 Effektiv mit Claude Code arbeiten

### Best Practices

#### ✅ DO: Klare, spezifische Fragen stellen

**Gut:**
```
"Lies DEVELOPER_NOTES.md und zeige mir:
1. Welche Environment Variables brauche ich für Vercel?
2. Wo setze ich den ADMIN_PRIVATE_KEY?"
```

**Noch besser:**
```
"Ich möchte das Projekt lokal entwickeln.
Lies DEVELOPER_NOTES.md Abschnitt 'Lokale Entwicklung'
und führe mich Schritt-für-Schritt durch das Setup."
```

#### ✅ DO: Kontext geben

**Gut:**
```
"Ich habe einen Fehler beim Wallet erstellen.
Lies DEVELOPER_NOTES.md Abschnitt 'Troubleshooting'
und hilf mir das zu debuggen.

Fehler: Table 'wp_sui_user_wallets' doesn't exist"
```

#### ✅ DO: Dokumentation referenzieren

**Gut:**
```
"Laut DEVELOPER_NOTES.md gibt es einen /api/generate-wallet Endpoint.
Zeige mir wie ich den lokal teste."
```

#### ❌ DON'T: Vage Fragen ohne Kontext

**Schlecht:**
```
"Wie funktioniert das?"
"Was macht das Projekt?"
"Hilf mir"
```

**Besser:**
```
"Lies DEVELOPER_NOTES.md und erkläre mir wie die Wallet-Verschlüsselung funktioniert."
```

---

## 🎯 Typische Arbeitsabläufe mit Claude Code

### Workflow 1: Neues Feature entwickeln

```
Du: "Ich möchte ein neues Feature hinzufügen: Wallet-Export als PDF.
     Lies DEVELOPER_NOTES.md und erkläre mir:
     1. Welche Dateien ich anpassen muss
     2. Wie die Wallet-Manager Klasse funktioniert
     3. Wo ich den Export-Button hinzufüge"

Claude: [Liest DEVELOPER_NOTES.md, analysiert Code, gibt Empfehlungen]

Du: "Zeige mir die class-wallet-manager.php Datei"

Claude: [Zeigt die Datei]

Du: "Füge eine neue Methode export_wallet_pdf() hinzu"

Claude: [Erstellt den Code mit korrekter Struktur basierend auf bestehendem Code]
```

### Workflow 2: Fehler debuggen

```
Du: "Ich habe einen Fehler beim Deployen.
     Lies DEVELOPER_NOTES.md Abschnitt 'Troubleshooting'
     und hilf mir.

     Fehler: 'Invalid mnemonic'"

Claude: [Liest Troubleshooting-Abschnitt]
       "Dieser Fehler ist dokumentiert in DEVELOPER_NOTES.md.
        Problem: Vercel nutzt falsches Key-Format.
        Lösung: ... [zeigt Lösung aus Dokumentation]"

Du: "Zeige mir die sui-client.ts Datei und fixe das"

Claude: [Zeigt Datei, schlägt Fix vor basierend auf Dokumentation]
```

### Workflow 3: Deployment

```
Du: "Ich möchte eine neue Version deployen.
     Lies DEVELOPER_NOTES.md Abschnitt 'Deployment'
     und führe mich durch den Prozess."

Claude: [Zeigt Schritt-für-Schritt Anleitung]

Du: "Erstelle v1.0.7 mit diesen Änderungen"

Claude: [Updated Version in sui-user-wallets.php,
        erstellt Commit, Tag, Push,
        erklärt wie Release erstellt wird]
```

### Workflow 4: API Testing

```
Du: "Ich möchte die Vercel API lokal testen.
     Lies DEVELOPER_NOTES.md und zeige mir die Test-Befehle."

Claude: [Zeigt curl-Befehle aus Dokumentation]

Du: "Die /api/generate-wallet Antwort sieht komisch aus.
     Zeige mir die generate-wallet.ts Datei"

Claude: [Zeigt Datei, analysiert Code]

Du: "Was ist das erwartete Response-Format laut Dokumentation?"

Claude: [Referenziert DEVELOPER_NOTES.md API Endpoints Abschnitt]
```

---

## 🔧 Wichtige Claude Code Befehle

### Dateien lesen

```
"Lies DEVELOPER_NOTES.md"
"Zeige mir sui-user-wallets.php"
"Öffne includes/class-wallet-manager.php"
```

### Code schreiben/ändern

```
"Füge eine neue Funktion get_all_users_with_wallets() hinzu"
"Ändere die Version auf 1.0.7"
"Fixe den Fehler in Zeile 42"
```

### Suchen

```
"Suche nach allen Stellen wo 'ADMIN_PRIVATE_KEY' verwendet wird"
"Finde alle TODO-Kommentare im Code"
"Wo wird die create_wallet_for_user Funktion aufgerufen?"
```

### Git-Operationen

```
"Committe diese Änderungen mit Message 'Fix wallet encryption'"
"Erstelle einen neuen Branch feature/pdf-export"
"Zeige mir den Git-Status"
"Pushe zu GitHub"
```

### Terminal-Befehle

```
"Starte den Vercel Dev-Server"
"Installiere npm packages"
"Führe die Tests aus"
```

---

## 📖 Empfohlener Lernpfad

### Tag 1: Verstehen

1. **Projekt-Übersicht:**
   ```
   "Lies DEVELOPER_NOTES.md komplett und erkläre mir:
    - Die Architektur
    - Welche Probleme das Projekt löst
    - Die wichtigsten Komponenten"
   ```

2. **Setup verstehen:**
   ```
   "Erkläre mir die Environment Variables aus DEVELOPER_NOTES.md.
    Welche brauche ich für:
    - Lokale Entwicklung
    - Production Deployment"
   ```

3. **Code-Struktur:**
   ```
   "Zeige mir die Hauptdateien:
    - sui-user-wallets.php
    - includes/class-wallet-manager.php
    - includes/class-wallet-crypto.php

    Erkläre was jede Datei macht."
   ```

### Tag 2: Lokales Setup

1. **Vercel API lokal:**
   ```
   "Lies DEVELOPER_NOTES.md 'Lokale Entwicklung' Abschnitt.
    Hilf mir die Vercel API lokal zu starten."
   ```

2. **WordPress lokal:**
   ```
   "Hilf mir WordPress lokal mit Docker aufzusetzen
    basierend auf DEVELOPER_NOTES.md."
   ```

3. **Testen:**
   ```
   "Zeige mir wie ich teste ob alles funktioniert.
    Welche Test-Befehle gibt es in DEVELOPER_NOTES.md?"
   ```

### Tag 3: Erste Änderung

1. **Kleines Feature:**
   ```
   "Ich möchte einen Log-Eintrag hinzufügen wenn ein Wallet erstellt wird.
    Lies die class-wallet-manager.php und hilf mir das zu implementieren."
   ```

2. **Testen:**
   ```
   "Wie teste ich diese Änderung lokal?"
   ```

3. **Committen:**
   ```
   "Committe diese Änderung mit passender Commit-Message"
   ```

### Tag 4: Deployment

1. **Deployment verstehen:**
   ```
   "Lies DEPLOYMENT.md und erkläre mir:
    - Wie deploye ich zu Staging?
    - Wie deploye ich zu Production?
    - Was passiert automatisch via GitHub Actions?"
   ```

2. **Release erstellen:**
   ```
   "Zeige mir wie ich ein neues Release erstelle
    für das Auto-Update System."
   ```

---

## 🎓 Fortgeschrittene Nutzung

### Multi-File Änderungen

```
Du: "Ich möchte die Wallet-Verschlüsselung auf AES-256-GCM upgraden.

     Lies:
     - DEVELOPER_NOTES.md (Security Abschnitt)
     - includes/class-wallet-crypto.php

     Zeige mir welche Dateien ich ändern muss und wie."

Claude: [Analysiert, zeigt Plan, macht Änderungen in mehreren Dateien]
```

### Refactoring

```
Du: "Die class-wallet-manager.php ist zu groß.
     Lies die Datei und schlage eine Aufteilung vor
     basierend auf den Best Practices aus DEVELOPER_NOTES.md."

Claude: [Analysiert, schlägt Struktur vor, führt Refactoring durch]
```

### Neue API Endpoints

```
Du: "Ich brauche einen neuen Endpoint /api/export-wallets.

     Lies:
     - vercel-api/api/generate-wallet.ts (als Beispiel)
     - DEVELOPER_NOTES.md (API Endpoints Abschnitt)

     Erstelle den neuen Endpoint mit korrekter Struktur."

Claude: [Erstellt neuen Endpoint basierend auf bestehenden Patterns]
```

---

## 🐛 Troubleshooting mit Claude Code

### Problem: Claude findet Datei nicht

**Lösung:**
```
"Suche nach allen .md Dateien im aktuellen Verzeichnis"
"Liste alle Dateien im includes/ Ordner"
```

### Problem: Claude Code versteht Kontext nicht

**Lösung:**
```
# Gib mehr Kontext:
"Ich arbeite am WordPress Plugin für Sui Wallet Management.
 Lies DEVELOPER_NOTES.md für vollständigen Kontext.

 Mein Problem: [beschreibe Problem]"
```

### Problem: Änderungen wurden nicht gespeichert

**Lösung:**
```
"Zeige mir den Git-Status"
"Welche Dateien wurden geändert?"
"Committe alle Änderungen"
```

---

## 📝 Cheat Sheet: Häufige Befehle

### Dokumentation lesen
```
"Lies DEVELOPER_NOTES.md"
"Zeige mir den Abschnitt 'Environment Variables' aus DEVELOPER_NOTES.md"
"Fasse QUICK_START.md zusammen"
```

### Code verstehen
```
"Was macht die Funktion create_wallet_for_user()?"
"Erkläre mir die Verschlüsselungs-Logik"
"Wie funktioniert das Auto-Update System?"
```

### Code schreiben
```
"Füge Logging zu dieser Funktion hinzu"
"Erstelle eine neue Methode get_wallet_balance_cached()"
"Fixe diesen Bug: [beschreibung]"
```

### Debugging
```
"Warum schlägt dieser Test fehl?"
"Ich bekomme Fehler X, lies DEVELOPER_NOTES.md Troubleshooting"
"Was bedeutet dieser Error: [error message]"
```

### Git & Deployment
```
"Committe mit Message 'feat: Add wallet export'"
"Erstelle neues Release v1.0.7"
"Zeige mir die letzten 5 Commits"
```

---

## 💪 Best Practices

### 1. Immer Dokumentation referenzieren

**✅ Gut:**
```
"Lies DEVELOPER_NOTES.md und hilf mir die Vercel API zu deployen"
```

**❌ Schlecht:**
```
"Wie deploye ich das?"
```

### 2. Spezifische Probleme beschreiben

**✅ Gut:**
```
"Beim Ausführen von 'npm run dev' bekomme ich:
Error: Missing ADMIN_PRIVATE_KEY

Lies DEVELOPER_NOTES.md Environment Variables Abschnitt
und zeige mir wie ich das setze."
```

**❌ Schlecht:**
```
"Es funktioniert nicht"
```

### 3. Code-Konventionen beibehalten

**✅ Gut:**
```
"Füge eine neue Funktion hinzu.
Nutze den gleichen Stil wie in get_user_wallet()."
```

### 4. Testing nicht vergessen

**✅ Gut:**
```
"Ich habe diese Änderung gemacht.
Lies DEVELOPER_NOTES.md Testing Abschnitt
und zeige mir welche Tests ich ausführen sollte."
```

---

## 🎯 Zusammenfassung für neue Kollegen

### Quick Start - 5 Minuten:

1. **Claude Code starten:**
   ```bash
   cd wordpress-plugin-wallet
   claude
   ```

2. **Erste Frage:**
   ```
   "Lies DEVELOPER_NOTES.md und gib mir eine Projekt-Übersicht.
    Was macht dieses Projekt und wie ist es aufgebaut?"
   ```

3. **Zweite Frage:**
   ```
   "Erkläre mir die wichtigsten Komponenten und zeige mir die Hauptdateien."
   ```

4. **Setup starten:**
   ```
   "Hilf mir das Projekt lokal zu starten.
    Lies DEVELOPER_NOTES.md Abschnitt 'Lokale Entwicklung'
    und führe mich Schritt für Schritt durch."
   ```

### Das war's! 🎉

Claude Code hilft dir:
- ✅ Dokumentation zu verstehen
- ✅ Code zu lesen und zu schreiben
- ✅ Fehler zu debuggen
- ✅ Best Practices einzuhalten
- ✅ Zu deployen

**Viel Erfolg mit dem Projekt!** 🚀

---

## 📚 Weiterführende Links

- **Claude Code Dokumentation:** https://docs.anthropic.com/en/docs/claude-code
- **GitHub Repository:** https://github.com/utakapp/sui-user-wallets
- **Projekt-Dokumentation:** DEVELOPER_NOTES.md (⭐ Start hier!)

---

**Fragen?** Frag einfach Claude Code:
```
"Ich habe eine Frage zu [Topic]. Lies die relevante Dokumentation und hilf mir."
```

**Claude weiß wo die Antworten sind!** 🤖
