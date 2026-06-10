#!/usr/bin/env bash
# Builds a clean Joomla-installable ZIP for com_sanctuaryshop
set -e

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
VERSION=$(grep -oP '(?<=<version>)[^<]+' "$SCRIPT_DIR/sanctuaryshop.xml" | head -1)
OUTFILE="$SCRIPT_DIR/com_sanctuaryshop_v${VERSION}.zip"

echo "Building com_sanctuaryshop v${VERSION}..."

# Remove old build
rm -f "$OUTFILE"

# Create ZIP from the correct files — manifest at root, component dirs only
cd "$SCRIPT_DIR"
zip -r "$OUTFILE" \
    sanctuaryshop.xml \
    script.php \
    site/ \
    admin/ \
    media/ \
    -x "*.DS_Store" \
    -x "*/.git/*" \
    -x "*.gitignore" \
    -x "*/worktrees/*"

echo "Done: $OUTFILE"
echo "Size: $(du -sh "$OUTFILE" | cut -f1)"
