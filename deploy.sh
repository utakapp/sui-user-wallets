#!/bin/bash

#############################################
# WordPress Plugin Deployment Script
# Unterstützt: FTP, SFTP, SSH, Local
#############################################

set -e

# Farben für Output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Konfiguration
PLUGIN_NAME="sui-user-wallets"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Load .env if exists
if [ -f "$SCRIPT_DIR/.env.deploy" ]; then
    source "$SCRIPT_DIR/.env.deploy"
fi

#############################################
# Funktionen
#############################################

print_header() {
    echo -e "${GREEN}================================${NC}"
    echo -e "${GREEN}  WordPress Plugin Deployer${NC}"
    echo -e "${GREEN}  Plugin: $PLUGIN_NAME${NC}"
    echo -e "${GREEN}================================${NC}"
    echo ""
}

print_success() {
    echo -e "${GREEN}✅ $1${NC}"
}

print_error() {
    echo -e "${RED}❌ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠️  $1${NC}"
}

# Deployment via SFTP
deploy_sftp() {
    print_header
    echo "Deployment Method: SFTP"
    echo ""

    # Prüfe Credentials
    if [ -z "$SFTP_HOST" ] || [ -z "$SFTP_USER" ]; then
        print_error "SFTP_HOST und SFTP_USER müssen in .env.deploy gesetzt sein!"
        exit 1
    fi

    print_warning "Deploying to: $SFTP_USER@$SFTP_HOST"
    echo "Remote Path: $SFTP_REMOTE_PATH"
    read -p "Fortfahren? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_error "Deployment abgebrochen"
        exit 1
    fi

    # SFTP Upload mit lftp (best tool for SFTP sync)
    if command -v lftp &> /dev/null; then
        print_success "Uploading via lftp..."
        lftp -c "
            set sftp:auto-confirm yes;
            open sftp://$SFTP_USER:$SFTP_PASSWORD@$SFTP_HOST;
            mirror -R \
                --exclude .git/ \
                --exclude .github/ \
                --exclude node_modules/ \
                --exclude .DS_Store \
                --exclude debug-test.php \
                --verbose \
                $SCRIPT_DIR $SFTP_REMOTE_PATH
        "
        print_success "SFTP Upload abgeschlossen!"
    else
        # Fallback: rsync über SSH
        print_warning "lftp nicht installiert, verwende rsync..."
        rsync -avz --delete \
            --exclude '.git/' \
            --exclude '.github/' \
            --exclude 'node_modules/' \
            --exclude '.DS_Store' \
            --exclude 'debug-test.php' \
            -e "ssh -p ${SFTP_PORT:-22}" \
            "$SCRIPT_DIR/" "$SFTP_USER@$SFTP_HOST:$SFTP_REMOTE_PATH/"
        print_success "Rsync Upload abgeschlossen!"
    fi
}

# Deployment via SSH
deploy_ssh() {
    print_header
    echo "Deployment Method: SSH"
    echo ""

    if [ -z "$SSH_HOST" ] || [ -z "$SSH_USER" ]; then
        print_error "SSH_HOST und SSH_USER müssen in .env.deploy gesetzt sein!"
        exit 1
    fi

    print_warning "Deploying to: $SSH_USER@$SSH_HOST"
    echo "Remote Path: $SSH_REMOTE_PATH"
    read -p "Fortfahren? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_error "Deployment abgebrochen"
        exit 1
    fi

    # Rsync über SSH
    rsync -avz --delete \
        --exclude '.git/' \
        --exclude '.github/' \
        --exclude 'node_modules/' \
        --exclude '.DS_Store' \
        --exclude 'debug-test.php' \
        -e "ssh -p ${SSH_PORT:-22}" \
        "$SCRIPT_DIR/" "$SSH_USER@$SSH_HOST:$SSH_REMOTE_PATH/"

    print_success "SSH Deployment abgeschlossen!"

    # Optional: WP-CLI commands ausführen
    if [ "$RUN_WP_CLI" = "true" ]; then
        print_warning "Running WP-CLI commands..."
        ssh -p ${SSH_PORT:-22} "$SSH_USER@$SSH_HOST" << 'EOF'
cd $(wp eval 'echo ABSPATH;')
wp plugin activate sui-user-wallets
wp cache flush
EOF
        print_success "WP-CLI commands executed!"
    fi
}

# Deployment via FTP
deploy_ftp() {
    print_header
    echo "Deployment Method: FTP"
    echo ""

    if [ -z "$FTP_HOST" ] || [ -z "$FTP_USER" ]; then
        print_error "FTP_HOST und FTP_USER müssen in .env.deploy gesetzt sein!"
        exit 1
    fi

    print_warning "Deploying to: $FTP_USER@$FTP_HOST"
    echo "Remote Path: $FTP_REMOTE_PATH"
    read -p "Fortfahren? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_error "Deployment abgebrochen"
        exit 1
    fi

    # FTP Upload mit lftp
    if command -v lftp &> /dev/null; then
        lftp -c "
            set ftp:ssl-allow no;
            open -u $FTP_USER,$FTP_PASSWORD $FTP_HOST;
            mirror -R \
                --exclude .git/ \
                --exclude .github/ \
                --exclude node_modules/ \
                --exclude .DS_Store \
                --exclude debug-test.php \
                --verbose \
                $SCRIPT_DIR $FTP_REMOTE_PATH
        "
        print_success "FTP Upload abgeschlossen!"
    else
        print_error "lftp ist nicht installiert!"
        echo "Installation: sudo apt-get install lftp"
        exit 1
    fi
}

# Create ZIP for manual upload
create_zip() {
    print_header
    echo "Creating ZIP for manual upload..."
    echo ""

    ZIP_NAME="${PLUGIN_NAME}-$(date +%Y%m%d-%H%M%S).zip"
    ZIP_PATH="$SCRIPT_DIR/../$ZIP_NAME"

    cd "$SCRIPT_DIR"
    zip -r "$ZIP_PATH" . \
        -x "*.git*" \
        -x "*node_modules*" \
        -x "*.DS_Store" \
        -x "*debug-test.php" \
        -x "*.github*" \
        -x "*deploy.sh" \
        -x "*.env.deploy"

    print_success "ZIP erstellt: $ZIP_PATH"
    echo ""
    echo "Manual Upload:"
    echo "1. WordPress Admin → Plugins → Add New → Upload Plugin"
    echo "2. Wähle: $ZIP_NAME"
    echo "3. Klicke 'Install Now'"
    echo ""
}

# Local deployment (für lokale WordPress Installation)
deploy_local() {
    print_header
    echo "Deployment Method: Local Copy"
    echo ""

    if [ -z "$LOCAL_WP_PATH" ]; then
        print_error "LOCAL_WP_PATH muss in .env.deploy gesetzt sein!"
        exit 1
    fi

    TARGET_PATH="$LOCAL_WP_PATH/wp-content/plugins/$PLUGIN_NAME"

    print_warning "Deploying to: $TARGET_PATH"
    read -p "Fortfahren? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        print_error "Deployment abgebrochen"
        exit 1
    fi

    # Erstelle Verzeichnis falls nicht existiert
    mkdir -p "$TARGET_PATH"

    # Kopiere Dateien
    rsync -av --delete \
        --exclude '.git/' \
        --exclude '.github/' \
        --exclude 'node_modules/' \
        --exclude '.DS_Store' \
        --exclude 'debug-test.php' \
        "$SCRIPT_DIR/" "$TARGET_PATH/"

    print_success "Local Deployment abgeschlossen!"
    echo ""
    echo "WordPress URL: $LOCAL_WP_URL"
}

#############################################
# Main Menu
#############################################

show_menu() {
    print_header
    echo "Wähle Deployment-Methode:"
    echo ""
    echo "1) SFTP Deployment"
    echo "2) SSH Deployment"
    echo "3) FTP Deployment"
    echo "4) Create ZIP (Manual Upload)"
    echo "5) Local Deployment"
    echo "6) Exit"
    echo ""
    read -p "Auswahl [1-6]: " choice

    case $choice in
        1) deploy_sftp ;;
        2) deploy_ssh ;;
        3) deploy_ftp ;;
        4) create_zip ;;
        5) deploy_local ;;
        6) exit 0 ;;
        *) print_error "Ungültige Auswahl"; show_menu ;;
    esac
}

#############################################
# Start
#############################################

# Check if running directly
if [ "$1" = "--sftp" ]; then
    deploy_sftp
elif [ "$1" = "--ssh" ]; then
    deploy_ssh
elif [ "$1" = "--ftp" ]; then
    deploy_ftp
elif [ "$1" = "--zip" ]; then
    create_zip
elif [ "$1" = "--local" ]; then
    deploy_local
else
    show_menu
fi
