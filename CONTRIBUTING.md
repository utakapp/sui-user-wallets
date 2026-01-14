# Contributing to Sui User Wallets

Vielen Dank für dein Interesse, zu diesem Projekt beizutragen! 🎉

---

## 📋 Inhaltsverzeichnis

1. [Code of Conduct](#code-of-conduct)
2. [Wie kann ich beitragen?](#wie-kann-ich-beitragen)
3. [Development Setup](#development-setup)
4. [Coding Standards](#coding-standards)
5. [Commit Messages](#commit-messages)
6. [Pull Request Process](#pull-request-process)
7. [Testing](#testing)
8. [Dokumentation](#dokumentation)

---

## Code of Conduct

Dieses Projekt und alle Teilnehmer sind an einen Code of Conduct gebunden. Mit der Teilnahme erwartest du, diesen Code einzuhalten.

### Unsere Standards

**✅ Erwünscht:**
- Respektvoller und inklusiver Umgang
- Konstruktives Feedback
- Fokus auf das Beste für die Community
- Empathie für andere Community-Mitglieder

**❌ Nicht akzeptiert:**
- Belästigung jeglicher Art
- Trolling, beleidigende Kommentare
- Persönliche oder politische Angriffe
- Veröffentlichung privater Informationen ohne Erlaubnis

---

## Wie kann ich beitragen?

### 🐛 Bug Reports

**Bevor du einen Bug meldest:**
1. Check ob der Bug bereits gemeldet wurde: [Issues](https://github.com/utakapp/sui-user-wallets/issues)
2. Prüfe FAQ.md ob das Problem bekannt ist
3. Teste mit neuester Version

**Bug Report erstellen:**

```markdown
**Beschreibung:**
Kurze Beschreibung des Bugs

**Schritte zum Reproduzieren:**
1. Gehe zu...
2. Klicke auf...
3. Scrolle zu...
4. Siehe Fehler

**Erwartetes Verhalten:**
Was sollte passieren?

**Aktuelles Verhalten:**
Was passiert tatsächlich?

**Screenshots:**
Falls hilfreich

**Environment:**
- WordPress Version:
- PHP Version:
- Plugin Version:
- Browser (falls relevant):

**Debug Log:**
```
[Letzte 20 Zeilen aus wp-content/debug.log]
```
```

### ✨ Feature Requests

**Feature vorschlagen:**

```markdown
**Feature Beschreibung:**
Was soll das Feature tun?

**Use Case:**
Warum ist das nützlich?

**Vorgeschlagene Implementation:**
Wie könnte es implementiert werden?

**Alternativen:**
Andere Lösungsansätze?
```

### 📖 Dokumentation

Verbesserungen an der Dokumentation sind immer willkommen!

**Dokumentations-Dateien:**
- README.md
- DEVELOPER_NOTES.md
- CLAUDE_ONBOARDING.md
- FAQ.md
- Alle anderen .md Dateien

**Process:**
1. Fork Repository
2. Ändere Dokumentation
3. PR erstellen

### 💻 Code Contributions

**Bevor du Code schreibst:**
1. Check open Issues/PRs ob jemand anderes daran arbeitet
2. Erstelle Issue für große Features (diskutiere erst)
3. Für kleine Fixes: PR direkt

---

## Development Setup

### Prerequisites

```bash
# System Requirements
- PHP 7.4+
- Node.js 18+
- Composer
- Git
- WordPress 5.0+
- MySQL/MariaDB

# Tools
- Sui CLI (optional, für Blockchain-Tests)
- Vercel CLI (optional, für API-Tests)
```

### Setup

```bash
# 1. Fork & Clone
git clone https://github.com/YOUR-USERNAME/sui-user-wallets.git
cd sui-user-wallets

# 2. WordPress Plugin Setup
# Symlink ins WordPress-Verzeichnis
ln -s $(pwd) /path/to/wordpress/wp-content/plugins/sui-user-wallets

# Oder kopiere Dateien:
cp -r . /path/to/wordpress/wp-content/plugins/sui-user-wallets/

# 3. Composer Dependencies (für Tests)
composer install

# 4. Vercel API Setup (optional)
cd ../vercel-api
npm install
cp .env.example .env
# Bearbeite .env

# 5. Starte lokale Umgebung
# WordPress: Via XAMPP/MAMP/Docker
# Vercel API: vercel dev
```

**Siehe:** DEVELOPER_NOTES.md "Lokale Entwicklung" für Details

### Vercel API lokal

```bash
cd vercel-api
npm install
vercel dev
# Läuft auf: http://localhost:3000
```

### WordPress lokal

**Option A: Docker**
```bash
# docker-compose.yml verwenden
docker-compose up -d
# WordPress: http://localhost:8080
```

**Option B: XAMPP/MAMP**
- Installiere XAMPP oder MAMP
- Kopiere Plugin nach `/wp-content/plugins/`
- Aktiviere Plugin in WordPress

---

## Coding Standards

### PHP (WordPress Plugin)

**Style Guide:** [WordPress PHP Coding Standards](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/)

**Key Points:**

```php
// Naming Conventions
class SUW_Wallet_Manager {}  // Classes: Prefix + PascalCase
function suw_create_wallet() {}  // Functions: prefix + snake_case
$wallet_address = '';  // Variables: snake_case

// Indentation: 4 spaces (no tabs)
function example() {
    if ($condition) {
        do_something();
    }
}

// Braces: Always use braces, even for single-line statements
if ($check) {
    return true;
}

// Spacing
$result = foo( $bar, $baz );  // Space after opening (, before closing )
$array = array( 'foo' => 'bar' );
if ( $condition ) {}  // Space after if, before (

// Documentation
/**
 * Short description
 *
 * Long description
 *
 * @param string $param Parameter description
 * @return bool Return description
 */
function foo( $param ) {
    return true;
}
```

**Security:**

```php
// Always sanitize input
$user_input = sanitize_text_field( $_POST['input'] );
$email = sanitize_email( $_POST['email'] );

// Always escape output
echo esc_html( $text );
echo esc_url( $url );
echo esc_attr( $attribute );

// Use nonces for forms
wp_nonce_field( 'action_name', 'nonce_name' );
check_ajax_referer( 'action_name' );

// Check capabilities
if ( ! current_user_can( 'manage_options' ) ) {
    wp_die( 'Unauthorized' );
}

// Use prepared statements
$wpdb->prepare( "SELECT * FROM table WHERE id = %d", $id );
```

### TypeScript (Vercel API)

**Style Guide:** [Airbnb JavaScript Style Guide](https://github.com/airbnb/javascript)

**Key Points:**

```typescript
// Naming Conventions
class SuiClient {}  // Classes: PascalCase
function generateWallet() {}  // Functions: camelCase
const walletAddress = '';  // Variables: camelCase
const MAX_RETRIES = 3;  // Constants: UPPER_SNAKE_CASE

// Indentation: 2 spaces
function example() {
  if (condition) {
    doSomething();
  }
}

// Types
interface WalletData {
  address: string;
  privateKey: string;
}

// Async/Await statt Promises
async function fetchData(): Promise<Data> {
  const response = await fetch(url);
  return response.json();
}

// Error Handling
try {
  await riskyOperation();
} catch (error) {
  console.error('[Vercel API] Error:', error);
  throw new Error('Failed to perform operation');
}
```

### Git

**Branch Naming:**
```bash
feature/wallet-export    # Neues Feature
fix/database-error       # Bug Fix
docs/update-readme       # Dokumentation
refactor/wallet-manager  # Refactoring
test/wallet-creation     # Tests
```

**Don'ts:**
```bash
# Vermeide:
my-branch
test
fix
feature
```

---

## Commit Messages

**Format:** [Conventional Commits](https://www.conventionalcommits.org/)

```
<type>(<scope>): <subject>

<body>

<footer>
```

### Types

```
feat:     Neues Feature
fix:      Bug Fix
docs:     Dokumentation
style:    Formatierung (keine Code-Änderung)
refactor: Code-Umstrukturierung (kein neues Feature/Fix)
test:     Tests hinzufügen/ändern
chore:    Build, Dependencies, etc.
perf:     Performance-Verbesserung
ci:       CI/CD Änderungen
```

### Beispiele

**Gute Commit Messages:**

```bash
feat(wallet): Add bulk export functionality

- Add bulk_export_wallets() method to WalletManager
- Create admin UI for selecting multiple wallets
- Add CSV export option
- Include error handling for large exports

Closes #42

fix(crypto): Fix AES decryption for keys with special characters

The decryption was failing when private keys contained certain
special characters. This fix properly escapes the key before
decryption.

Fixes #38

docs(contributing): Add coding standards section

Add detailed coding standards for PHP and TypeScript to make it
easier for contributors to follow project conventions.
```

**Schlechte Commit Messages:**

```bash
update           # Was wurde updated?
fix bug          # Welcher Bug?
changes          # Welche Changes?
wip              # Work in progress - nicht committen!
```

---

## Pull Request Process

### 1. Vorbereitung

```bash
# Fork & Clone
git clone https://github.com/YOUR-USERNAME/sui-user-wallets.git
cd sui-user-wallets

# Upstream hinzufügen
git remote add upstream https://github.com/utakapp/sui-user-wallets.git

# Aktuellsten main holen
git fetch upstream
git checkout main
git merge upstream/main
```

### 2. Branch erstellen

```bash
git checkout -b feature/my-new-feature
```

### 3. Änderungen machen

```bash
# Code schreiben
# Tests schreiben
# Dokumentation updaten

git add .
git commit -m "feat(feature): Add my new feature"
```

### 4. Tests ausführen

```bash
# PHP Tests
composer test

# TypeScript Tests (falls Vercel API geändert)
cd vercel-api
npm test

# Manuelle Tests
# - WordPress Plugin aktivieren
# - Feature testen
# - Edge Cases prüfen
```

### 5. Push & PR erstellen

```bash
git push origin feature/my-new-feature
```

Dann auf GitHub:
1. Gehe zu deinem Fork
2. Klicke "Compare & pull request"
3. Fülle PR Template aus

### PR Template

```markdown
## Beschreibung
Kurze Beschreibung der Änderungen

## Motivation
Warum ist diese Änderung nötig?

## Type of Change
- [ ] Bug fix
- [ ] New feature
- [ ] Breaking change
- [ ] Documentation update

## Testing
Wie wurde getestet?
- [ ] Manual testing
- [ ] Unit tests added
- [ ] Integration tests added

## Checklist
- [ ] Code folgt Coding Standards
- [ ] Tests laufen durch
- [ ] Dokumentation updated
- [ ] CHANGELOG.md updated
- [ ] No breaking changes (oder klar dokumentiert)

## Screenshots (optional)
Wenn UI-Änderungen

## Related Issues
Closes #123
Related to #456
```

### 6. Code Review

**Nach PR Erstellung:**
- Maintainer reviewen deinen Code
- Feedback wird gegeben
- Du machst ggf. Anpassungen
- Nach Approval: Merge!

**Während Review:**
- Sei offen für Feedback
- Erkläre deine Entscheidungen
- Frage nach wenn unklar

### 7. Nach Merge

```bash
# Branch aufräumen
git checkout main
git pull upstream main
git branch -d feature/my-new-feature
git push origin --delete feature/my-new-feature
```

---

## Testing

### PHP Tests (WordPress Plugin)

**Setup:**
```bash
composer install
```

**Run Tests:**
```bash
./vendor/bin/phpunit
```

**Test schreiben:**

```php
// tests/test-wallet-manager.php
class Test_Wallet_Manager extends WP_UnitTestCase {

    public function test_create_wallet() {
        $user_id = $this->factory->user->create();

        $wallet = SUW_Wallet_Manager::create_wallet_for_user($user_id);

        $this->assertNotEmpty($wallet);
        $this->assertArrayHasKey('address', $wallet);
    }
}
```

### TypeScript Tests (Vercel API)

**Setup:**
```bash
cd vercel-api
npm install
```

**Run Tests:**
```bash
npm test
```

**Test schreiben:**

```typescript
// vercel-api/tests/generate-wallet.test.ts
import { generateWallet } from '../api/generate-wallet';

describe('generateWallet', () => {
  it('should generate valid wallet', async () => {
    const wallet = await generateWallet();

    expect(wallet).toHaveProperty('address');
    expect(wallet).toHaveProperty('privateKey');
    expect(wallet.address).toMatch(/^0x[a-f0-9]{64}$/);
  });
});
```

### Manual Testing

**Checklist:**

```
WordPress Plugin:
- [ ] Installation funktioniert
- [ ] Aktivierung funktioniert
- [ ] Datenbanktabelle wird erstellt
- [ ] Wallet wird bei User-Registrierung erstellt
- [ ] User kann Wallet sehen
- [ ] Admin kann alle Wallets sehen
- [ ] Private Key Export funktioniert
- [ ] Balance Check funktioniert
- [ ] Settings werden gespeichert
- [ ] Auto-Update funktioniert

Vercel API:
- [ ] /api/generate-wallet funktioniert
- [ ] /api/get-balance funktioniert
- [ ] /api/create-badge funktioniert
- [ ] /api/test funktioniert
- [ ] Error Handling korrekt
```

---

## Dokumentation

### Wann Dokumentation schreiben?

**Immer wenn:**
- Neues Feature hinzugefügt
- Public API geändert
- Breaking Change
- Komplexe Logik implementiert

### Welche Dateien updaten?

```
Neue Feature:
- CHANGELOG.md (Add Sektion)
- README.md (falls relevant)
- DEVELOPER_NOTES.md (falls API/Architektur)
- FAQ.md (falls häufige Frage)

Bug Fix:
- CHANGELOG.md (Fixed Sektion)

Breaking Change:
- CHANGELOG.md (Changed + Breaking Sektion)
- README.md (Migration Guide)
- DEVELOPER_NOTES.md (Updated API)
```

### Code Comments

**PHP:**
```php
/**
 * Create wallet for user via Vercel API
 *
 * Generates a new Sui wallet by calling the Vercel API endpoint.
 * The private key is encrypted using AES-256-CBC before storing
 * in the database.
 *
 * @param int $user_id WordPress user ID
 * @return array|WP_Error Wallet data or error
 */
public function create_wallet_for_user( $user_id ) {
    // Implementation
}
```

**TypeScript:**
```typescript
/**
 * Generate new Sui wallet with Ed25519 keypair
 *
 * @returns {Promise<WalletData>} Wallet address and private key
 * @throws {Error} If wallet generation fails
 */
async function generateWallet(): Promise<WalletData> {
  // Implementation
}
```

---

## Review Process

### Was wir prüfen

**Code Quality:**
- ✅ Folgt Coding Standards
- ✅ Keine unnötige Komplexität
- ✅ DRY (Don't Repeat Yourself)
- ✅ Lesbar und wartbar

**Security:**
- ✅ Input Sanitization
- ✅ Output Escaping
- ✅ SQL Injection Prevention
- ✅ XSS Prevention
- ✅ CSRF Protection (Nonces)

**Tests:**
- ✅ Tests vorhanden
- ✅ Tests laufen durch
- ✅ Edge Cases geprüft

**Dokumentation:**
- ✅ Code kommentiert
- ✅ CHANGELOG.md updated
- ✅ Relevante Docs updated

### Timeline

- **Initial Review:** Innerhalb 2-3 Tagen
- **Follow-up:** Innerhalb 1-2 Tagen
- **Merge:** Nach Approval

---

## Fragen?

**Unsicher wo/wie anfangen?**

1. Lies DEVELOPER_NOTES.md
2. Lies CLAUDE_ONBOARDING.md
3. Schau dir open "good first issue" Issues an
4. Frag in Discussion: https://github.com/utakapp/sui-user-wallets/discussions

**Während Development:**

Nutze Claude Code:
```
claude
"Lies CONTRIBUTING.md und hilf mir einen Beitrag zu erstellen"
```

---

## Lizenz

Mit deinem Beitrag stimmst du zu, dass dein Code unter der GPL v2 or later Lizenz veröffentlicht wird.

---

**Danke für deinen Beitrag! 🎉**

Jeder Beitrag, egal wie klein, hilft das Projekt besser zu machen.
