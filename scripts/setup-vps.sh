# VPS Server Setup Script
# Jalankan script ini di VPS server untuk setup awal

#!/bin/bash

set -e

echo "🚀 Setting up VPS for CI/CD deployment..."

# Update system
echo "📦 Updating system packages..."
sudo apt update && sudo apt upgrade -y

# Install Docker jika belum ada
if ! command -v docker &> /dev/null; then
    echo "🐳 Installing Docker..."
    curl -fsSL https://get.docker.com -o get-docker.sh
    sudo sh get-docker.sh
    sudo usermod -aG docker $USER
fi

# Install Docker Compose jika belum ada
if ! command -v docker-compose &> /dev/null; then
    echo "🔧 Installing Docker Compose..."
    sudo curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
    sudo chmod +x /usr/local/bin/docker-compose
fi

# Create directory structure
echo "📁 Creating directory structure..."
sudo mkdir -p /var/www/staging
sudo mkdir -p /var/www/production
sudo mkdir -p /var/log/nginx
sudo mkdir -p /var/log/php

# Set permissions
sudo chown -R $USER:$USER /var/www
sudo chmod -R 755 /var/www

# Install Nginx reverse proxy (host level)
echo "🌐 Installing Nginx reverse proxy..."
sudo apt install nginx -y

# Create main Nginx config for reverse proxy
sudo tee /etc/nginx/sites-available/default > /dev/null << 'EOF'
server {
    listen 80 default_server;
    server_name _;

    # Production app (port 80 -> container port 80)
    location / {
        proxy_pass http://localhost:80;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}

server {
    listen 8080;
    server_name _;

    # Staging app (port 8080 -> container port 8080)
    location / {
        proxy_pass http://localhost:8080;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
EOF

# Restart Nginx
sudo systemctl restart nginx
sudo systemctl enable nginx

# Setup firewall
echo "🔥 Configuring firewall..."
sudo ufw allow ssh
sudo ufw allow 80/tcp
sudo ufw allow 8080/tcp
sudo ufw allow 443/tcp
sudo ufw --force enable

# Create deployment user (optional)
echo "👤 Creating deployment user..."
if ! id "deployer" &>/dev/null; then
    sudo useradd -m -s /bin/bash deployer
    sudo usermod -aG docker deployer
    sudo usermod -aG sudo deployer
fi

# Setup SSH keys directory for deployer
sudo mkdir -p /home/deployer/.ssh
sudo chmod 700 /home/deployer/.ssh
sudo chown deployer:deployer /home/deployer/.ssh

# Create webhook receiver script (opsional)
echo "🪝 Setting up webhook receiver..."
sudo tee /usr/local/bin/deploy-webhook > /dev/null << 'EOF'
#!/bin/bash
cd /var/www/production/ci4-api-jwt && ./scripts/deploy.sh production
EOF

sudo chmod +x /usr/local/bin/deploy-webhook

# Install monitoring tools
echo "📊 Installing monitoring tools..."
sudo apt install htop curl wget git -y

# Install fail2ban for security
echo "🛡️ Installing security tools..."
sudo apt install fail2ban -y

# Setup basic fail2ban config
sudo tee /etc/fail2ban/jail.local > /dev/null << 'EOF'
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 3

[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3
EOF

sudo systemctl restart fail2ban
sudo systemctl enable fail2ban

echo "✅ VPS setup completed!"
echo ""
echo "Next steps:"
echo "1. Clone your repository to /var/www/staging and /var/www/production"
echo "2. Configure .env files for each environment"
echo "3. Setup SSL certificates (Let's Encrypt recommended)"
echo "4. Configure GitHub secrets for CI/CD"
echo ""
echo "Directory structure created:"
echo "├── /var/www/staging/    # Staging environment"
echo "└── /var/www/production/ # Production environment"
