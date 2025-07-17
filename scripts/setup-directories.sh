#!/bin/bash

# VPS Directory Setup Script
echo "📁 Creating directory structure..."

# Create main directories
mkdir -p /var/www/staging
mkdir -p /var/www/production
mkdir -p /var/log/nginx
mkdir -p /var/log/php
mkdir -p /var/backups/database

# Set proper permissions
chmod 755 /var/www/staging
chmod 755 /var/www/production
chmod 755 /var/log/nginx
chmod 755 /var/log/php
chmod 755 /var/backups/database

# Create deployment user (optional, more secure)
if ! id "deploy" &>/dev/null; then
    useradd -m -s /bin/bash deploy
    usermod -aG docker deploy
    usermod -aG sudo deploy
    
    # Setup SSH directory for deploy user
    mkdir -p /home/deploy/.ssh
    chmod 700 /home/deploy/.ssh
    chown deploy:deploy /home/deploy/.ssh
    
    echo "✅ Deploy user created"
fi

echo "✅ Directory structure created:"
tree /var/www -L 2 2>/dev/null || ls -la /var/www/

echo "✅ Directory setup completed!"
