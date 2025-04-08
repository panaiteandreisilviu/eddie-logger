#!/bin/bash

# Define the base directory
BASE_DIR="/var/www/config/eddie"

# Remove the directory if it exists
if [ -d "$BASE_DIR" ]; then
  rm -rf "$BASE_DIR"
  echo "Removed existing directory: $BASE_DIR"
fi

# Create the base directory and subdirectories
mkdir -p "$BASE_DIR/logs"
mkdir -p "$BASE_DIR/assets/sfdump"

# Copy the PHP file
cp eddie.php "$BASE_DIR/"
echo "Copied eddie.php to $BASE_DIR/"

# Copy the sfdump assets
cp assets/sfdump/sfdump.css "$BASE_DIR/assets/sfdump/"
cp assets/sfdump/sfdump.js "$BASE_DIR/assets/sfdump/"
echo "Copied sfdump assets to $BASE_DIR/assets/sfdump/"

echo "Installation complete."