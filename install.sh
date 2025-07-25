#!/bin/bash

set -e

TARGET_DIR="eddie"
LOGS_DIR="$TARGET_DIR/logs"
PHAR_URL="https://raw.githubusercontent.com/panaiteandreisilviu/eddie-logger/master/box/eddie.phar"
PHAR_FILE="$TARGET_DIR/eddie.phar"

# Create target and logs directories if they don't exist
mkdir -p "$LOGS_DIR"

# Set safe permissions for logs directory
chmod 766 "$LOGS_DIR"

# Download the PHAR file if not already present or if newer
curl -fsSL -o "$PHAR_FILE" "$PHAR_URL"

echo "Setup complete: $PHAR_FILE and $LOGS_DIR are ready."