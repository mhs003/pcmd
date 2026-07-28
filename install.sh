#!/usr/bin/env bash
#
# pcmd — installer
#
# Usage:
#   curl -fsSL https://raw.githubusercontent.com/YOUR_ORG/pcmd/main/install.sh | bash
#   bash install.sh              # install or update
#   bash install.sh --uninstall  # remove
#   bash install.sh --help       # this message

set -euo pipefail

INSTALL_DIR="${PCMD_HOME:-$HOME/.local/share/pcmd}"
BIN_DIR="${PCMD_BIN_DIR:-$HOME/.local/bin}"
REPO_URL="https://github.com/your-org/pcmd.git"
VERSION="main"

usage() {
    sed -n '3,10p' "$0"
    exit 0
}

uninstall() {
    if [ -L "$BIN_DIR/pcmd" ]; then
        rm "$BIN_DIR/pcmd"
        echo "Removed symlink: $BIN_DIR/pcmd"
    fi

    if [ -d "$INSTALL_DIR" ]; then
        rm -rf "$INSTALL_DIR"
        echo "Removed install directory: $INSTALL_DIR"
    fi

    echo "pcmd uninstalled."
    exit 0
}

[ "${1:-}" = "--help" ] && usage
[ "${1:-}" = "--uninstall" ] && uninstall

# ── Requirements ────────────────────────────────────────────────────────

command -v php >/dev/null 2>&1 || { echo "Error: PHP is required (php 8.3+)."; exit 1; }
command -v composer >/dev/null 2>&1 || { echo "Error: Composer is required."; exit 1; }
command -v git >/dev/null 2>&1 || { echo "Error: git is required."; exit 1; }

PHP_VERSION=$(php -r 'echo PHP_MAJOR_VERSION . "." . PHP_MINOR_VERSION;')
if php -r "exit(version_compare(PHP_VERSION, '8.3.0', '<') ? 0 : 1);"; then
    echo "Error: PHP 8.3+ required (found $PHP_VERSION)."
    exit 1
fi

# ── Install / Update ────────────────────────────────────────────────────

echo "Installing pcmd..."

mkdir -p "$INSTALL_DIR" "$BIN_DIR"

if [ -d "$INSTALL_DIR/.git" ]; then
    echo "Updating existing installation..."
    git -C "$INSTALL_DIR" fetch --depth 1 origin "$VERSION"
    git -C "$INSTALL_DIR" checkout -q "$VERSION"
else
    echo "Cloning repository..."
    git clone --depth 1 --branch "$VERSION" "$REPO_URL" "$INSTALL_DIR"
fi

echo "Installing Composer dependencies..."
composer install --no-dev --optimize-autoloader --working-dir="$INSTALL_DIR" --quiet

ln -sf "$INSTALL_DIR/bin/pcmd" "$BIN_DIR/pcmd"

echo ""
echo "pcmd installed successfully."
echo ""
echo "  Binary:  $BIN_DIR/pcmd"
echo "  Data:    $INSTALL_DIR"
echo "  Version: $("$BIN_DIR/pcmd" --version 2>/dev/null || echo '?')"
echo ""

if ! echo "$PATH" | tr ':' '\n' | grep -qx "$BIN_DIR"; then
    echo "Warning: $BIN_DIR is not in your PATH."
    echo "Add this to your ~/.bashrc or ~/.zshrc:"
    echo ""
    echo "  export PATH=\"\$HOME/.local/bin:\$PATH\""
    echo ""
fi

echo "Run 'pcmd doctor' to verify the installation."
