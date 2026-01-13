.PHONY: help deploy-sftp deploy-ssh deploy-ftp deploy-local zip release

help:
	@echo "WordPress Plugin Deployment Commands:"
	@echo ""
	@echo "  make deploy-sftp    Deploy via SFTP"
	@echo "  make deploy-ssh     Deploy via SSH/rsync"
	@echo "  make deploy-ftp     Deploy via FTP"
	@echo "  make deploy-local   Deploy to local WordPress"
	@echo "  make zip            Create ZIP for manual upload"
	@echo "  make release        Create GitHub release (requires tag)"
	@echo ""

deploy-sftp:
	@./deploy.sh --sftp

deploy-ssh:
	@./deploy.sh --ssh

deploy-ftp:
	@./deploy.sh --ftp

deploy-local:
	@./deploy.sh --local

zip:
	@./deploy.sh --zip

release:
	@echo "Creating release..."
	@if [ -z "$(VERSION)" ]; then \
		echo "Usage: make release VERSION=1.0.1"; \
		exit 1; \
	fi
	@git tag -a v$(VERSION) -m "Release version $(VERSION)"
	@git push origin v$(VERSION)
	@echo "Release v$(VERSION) erstellt und gepusht!"
	@echo "GitHub Actions wird automatisch ein ZIP erstellen."

setup:
	@echo "Setup Deployment Configuration..."
	@if [ ! -f .env.deploy ]; then \
		cp .env.deploy.example .env.deploy; \
		echo "✅ .env.deploy created. Please edit with your credentials."; \
	else \
		echo "⚠️  .env.deploy already exists."; \
	fi
	@chmod +x deploy.sh
	@echo "✅ deploy.sh is now executable"
