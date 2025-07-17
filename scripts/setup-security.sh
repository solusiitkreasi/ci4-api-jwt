#!/bin/bash

# VPS Security Setup Script
echo "🛡️ Setting up VPS security..."

# Configure UFW Firewall
ufw --force reset
ufw default deny incoming
ufw default allow outgoing

# Allow essential ports
ufw allow ssh
ufw allow 80/tcp     # HTTP
ufw allow 8080/tcp   # Staging HTTP
ufw allow 443/tcp    # HTTPS

# Enable firewall
ufw --force enable

echo "✅ Firewall configured:"
ufw status

# Configure fail2ban for SSH protection
echo "🔒 Setting up fail2ban..."

cat > /etc/fail2ban/jail.local << 'EOF'
[DEFAULT]
bantime = 3600
findtime = 600
maxretry = 3
backend = systemd

[sshd]
enabled = true
port = ssh
filter = sshd
logpath = /var/log/auth.log
maxretry = 3
bantime = 3600
EOF

# Restart fail2ban
systemctl restart fail2ban
systemctl enable fail2ban

echo "✅ Security setup completed!"
