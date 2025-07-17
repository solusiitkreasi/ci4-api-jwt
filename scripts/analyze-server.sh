#!/bin/bash

# Server Analysis Script untuk VPS dengan aaPanel
# Jalankan script ini untuk menganalisa kondisi server

echo "🔍 Analyzing VPS Server Configuration..."
echo "=================================="

echo "📅 Server Time: $(date)"
echo "🖥️  Hostname: $(hostname)"
echo "💻 OS Info: $(cat /etc/os-release | grep PRETTY_NAME)"
echo ""

echo "🐳 Docker Analysis:"
echo "-------------------"
if command -v docker &> /dev/null; then
    echo "✅ Docker installed: $(docker --version)"
    echo "📊 Docker status: $(systemctl is-active docker)"
    echo ""
    
    echo "📦 Currently running containers:"
    docker ps --format "table {{.Names}}\t{{.Image}}\t{{.Status}}\t{{.Ports}}" || echo "No containers running"
    echo ""
    
    echo "🔌 Used ports by Docker:"
    docker ps --format "{{.Ports}}" | grep -o '0.0.0.0:[0-9]*' | sort -u || echo "No exposed ports"
    echo ""
else
    echo "❌ Docker not installed"
fi

if command -v docker-compose &> /dev/null; then
    echo "✅ Docker Compose installed: $(docker-compose --version)"
else
    echo "❌ Docker Compose not found"
fi

echo ""
echo "🌐 Network Analysis:"
echo "-------------------"
echo "📡 Used ports on system:"
netstat -tlnp | grep LISTEN | head -10
echo ""

echo "🔥 Firewall status:"
if command -v ufw &> /dev/null; then
    ufw status | head -10
else
    echo "UFW not installed (normal for aaPanel)"
fi

echo ""
echo "📁 Directory Analysis:"
echo "---------------------"
echo "🗂️  /var/www structure:"
ls -la /var/www/ 2>/dev/null || echo "/var/www not found"
echo ""

echo "🏠 Home directory:"
ls -la ~/ | grep -E "(docker|compose|ci4|api)" || echo "No related files found"
echo ""

echo "📊 System Resources:"
echo "-------------------"
echo "💾 Memory usage:"
free -h
echo ""
echo "💿 Disk usage:"
df -h / /var /home 2>/dev/null
echo ""

echo "🔑 User and Permissions:"
echo "-----------------------"
echo "👤 Current user: $(whoami)"
echo "🔐 Groups: $(groups)"
echo "📝 Docker group membership:"
groups | grep docker && echo "✅ User in docker group" || echo "⚠️  User NOT in docker group"
echo ""

echo "🌍 aaPanel Detection:"
echo "--------------------"
if [ -d "/www" ]; then
    echo "✅ aaPanel directory structure detected"
    echo "📁 aaPanel web directory:"
    ls -la /www/wwwroot/ 2>/dev/null | head -5 || echo "Web directory not accessible"
else
    echo "⚠️  aaPanel structure not detected"
fi

if command -v bt &> /dev/null; then
    echo "✅ aaPanel CLI tool available"
else
    echo "⚠️  aaPanel CLI not found"
fi

echo ""
echo "🔍 Analysis completed!"
echo "======================"
