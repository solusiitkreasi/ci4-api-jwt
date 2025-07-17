#!/bin/bash

# VPS Deployment Testing Script

VPS_IP=${1:-localhost}

echo "🧪 Testing VPS deployment..."
echo "VPS IP: $VPS_IP"

# Test staging
echo "Testing staging environment..."
STAGING_HEALTH=$(curl -s -o /dev/null -w "%{http_code}" http://$VPS_IP:8080/api/health || echo "000")
STAGING_PING=$(curl -s -o /dev/null -w "%{http_code}" http://$VPS_IP:8080/api/ping || echo "000")

echo "Staging Health Check: HTTP $STAGING_HEALTH"
echo "Staging Ping: HTTP $STAGING_PING"

if [ "$STAGING_HEALTH" = "200" ] || [ "$STAGING_PING" = "200" ]; then
    echo "✅ Staging is running"
else
    echo "❌ Staging is not responding"
fi

# Test production
echo ""
echo "Testing production environment..."
PROD_HEALTH=$(curl -s -o /dev/null -w "%{http_code}" http://$VPS_IP/api/health || echo "000")
PROD_PING=$(curl -s -o /dev/null -w "%{http_code}" http://$VPS_IP/api/ping || echo "000")

echo "Production Health Check: HTTP $PROD_HEALTH"
echo "Production Ping: HTTP $PROD_PING"

if [ "$PROD_HEALTH" = "200" ] || [ "$PROD_PING" = "200" ]; then
    echo "✅ Production is running"
else
    echo "❌ Production is not responding"
fi

# Docker status
echo ""
echo "🐳 Docker container status:"
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}"

echo ""
echo "📊 System resources:"
echo "Memory usage:"
free -h
echo ""
echo "Disk usage:"
df -h /var/www

echo ""
echo "🔗 Access URLs:"
echo "Staging: http://$VPS_IP:8080"
echo "Production: http://$VPS_IP"
echo "Staging Health: http://$VPS_IP:8080/api/health"  
echo "Production Health: http://$VPS_IP/api/health"
