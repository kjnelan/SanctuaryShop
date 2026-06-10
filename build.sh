#!/usr/bin/env bash
# Builds the SanctuaryShop Joomla package (component + mini-cart module)
set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
VERSION=$(grep -oP '(?<=<version>)[^<]+' "$SCRIPT_DIR/sanctuaryshop.xml" | head -1)
BUILD_DIR="$SCRIPT_DIR/.build"
OUTFILE="$SCRIPT_DIR/pkg_sanctuaryshop_v${VERSION}.zip"

echo "Building SanctuaryShop v${VERSION}..."

rm -rf "$BUILD_DIR"
mkdir -p "$BUILD_DIR"

# 1. Component ZIP
echo "  → com_sanctuaryshop.zip"
cd "$SCRIPT_DIR"
zip -qr "$BUILD_DIR/com_sanctuaryshop.zip" \
    sanctuaryshop.xml \
    script.php \
    site/ \
    admin/ \
    media/ \
    -x "*.DS_Store" -x "*/.git/*" -x "*.gitignore" -x "*/worktrees/*"

# 2. Module ZIP
echo "  → mod_sanctuaryshop_cart.zip"
cd "$SCRIPT_DIR/modules/mod_sanctuaryshop_cart"
zip -qr "$BUILD_DIR/mod_sanctuaryshop_cart.zip" ./ \
    -x "*.DS_Store" -x "*/.git/*"

# 3. Package ZIP (manifest + both ZIPs)
echo "  → pkg_sanctuaryshop_v${VERSION}.zip"
cd "$BUILD_DIR"
cp "$SCRIPT_DIR/pkg_sanctuaryshop.xml" .
zip -q "$OUTFILE" pkg_sanctuaryshop.xml com_sanctuaryshop.zip mod_sanctuaryshop_cart.zip

rm -rf "$BUILD_DIR"

echo "Done: $OUTFILE"
echo "Size: $(du -sh "$OUTFILE" | cut -f1)"
