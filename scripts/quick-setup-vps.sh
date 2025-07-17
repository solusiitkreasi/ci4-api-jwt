#!/bin/bash

# Quick VPS Setup for CI4 API JWT
# Run this script on your VPS server

set -e

echo "🚀 Quick VPS Setup Started..."

# Update system
echo "📦 Updating system..."
apt update && apt upgrade -y

# Install Docker and Docker Compose
echo "🐳 Installing Docker..."
curl -fsSL https://get.docker.com -o get-docker.sh
sh get-docker.sh
rm get-docker.sh

curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose

# Install tools
echo "🔧 Installing tools..."
apt install -y git nano htop curl wget ufw tree

# Setup firewall
echo "🔥 Setting up firewall..."
ufw --force reset
ufw default deny incoming
ufw default allow outgoing
ufw allow ssh
ufw allow 80/tcp
ufw allow 8080/tcp
ufw allow 443/tcp
ufw --force enable

# Create directories
echo "📁 Creating directories..."
mkdir -p /var/www/staging
mkdir -p /var/www/production
chmod 755 /var/www/staging
chmod 755 /var/www/production

# Clone repositories
echo "📥 Cloning repositories..."
cd /var/www/staging
git clone https://github.com/solusiitkreasi/ci4-api-jwt.git
cd /var/www/production
git clone https://github.com/solusiitkreasi/ci4-api-jwt.git

echo "✅ Basic setup completed!"
echo ""
echo "Next steps:"
echo "1. cd /var/www/staging/ci4-api-jwt && cp .env.staging .env"
echo "2. cd /var/www/production/ci4-api-jwt && cp .env.production .env"
echo "3. Edit the .env files with your configurations"
echo "4. Run: docker-compose -f docker-compose.staging.yml up -d --build"
echo "5. Run: docker-compose -f docker-compose.production.yml up -d --build"
