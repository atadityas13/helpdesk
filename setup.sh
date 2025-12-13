#!/bin/bash
# Linux/Mac Setup Script untuk Helpdesk
# Run dengan: bash setup.sh

echo ""
echo "============================================================"
echo "  HELPDESK SETUP SCRIPT - MTsN 11 Majalengka"
echo "============================================================"
echo ""

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
cd "$SCRIPT_DIR"

# Check if running with sudo
if [[ $EUID -ne 0 ]]; then
   echo "ERROR: Script harus dijalankan dengan sudo"
   exit 1
fi

echo "[1/4] Checking if .env file exists..."
if [ ! -f ".env" ]; then
    echo "Creating .env from .env.example..."
    cp ".env.example" ".env"
    echo "✓ .env created. Silakan edit dengan credentials database Anda."
else
    echo "✓ .env file sudah ada"
fi

echo ""
echo "[2/4] Setting folder permissions..."
if [ ! -d "public/uploads" ]; then
    mkdir -p "public/uploads"
    echo "✓ Folder public/uploads created"
fi

if [ ! -d "logs" ]; then
    mkdir -p "logs"
    echo "✓ Folder logs created"
fi

chmod 755 public/uploads
chmod 755 logs
chmod 777 public/uploads  # For file uploads
chmod 666 logs/*

echo "✓ Permissions set"

echo ""
echo "[3/4] Checking requirements..."
echo ""

# Check PHP
if command -v php &> /dev/null; then
    php -v | head -1
    echo "✓ PHP found"
else
    echo "✗ PHP not found. Please install PHP 7.4+"
fi

# Check MySQL
if command -v mysql &> /dev/null; then
    mysql --version
    echo "✓ MySQL found"
else
    echo "✗ MySQL not found. Please install MySQL 5.7+"
fi

echo ""
echo "[4/4] Setup Summary"
echo "============================================================"
echo ""
echo "Project: Helpdesk MTsN 11 Majalengka"
echo "Directory: $SCRIPT_DIR"
echo ""
echo "Files created:"
echo "  ✓ .env - Environment configuration"
echo "  ✓ public/uploads - File uploads folder"
echo "  ✓ logs - Application logs folder"
echo ""
echo "NEXT STEPS:"
echo "  1. Edit .env dengan database credentials Anda"
echo "  2. Import database.sql ke MySQL:"
echo "     mysql -u root -p mtsnmaja_helpdesk < database.sql"
echo "  3. Akses http://localhost/helpdesk/"
echo "  4. Login dengan admin / admin123"
echo "  5. Ganti password admin"
echo ""
echo "URL Penting:"
echo "  Landing:     http://localhost/helpdesk/"
echo "  Login:       http://localhost/helpdesk/login.php"
echo "  Dashboard:   http://localhost/helpdesk/src/admin/dashboard.php"
echo ""
echo "============================================================"
echo ""
