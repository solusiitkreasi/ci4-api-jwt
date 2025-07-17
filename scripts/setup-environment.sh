#!/bin/bash

# Environment Configuration Script for VPS
# Run this after quick-setup-vps.sh

read -p "🌐 Enter your VPS IP address: " VPS_IP
read -p "🔐 Enter database password for staging: " STAGING_DB_PASS
read -p "🔐 Enter database password for production: " PROD_DB_PASS

echo "⚙️ Configuring staging environment..."
cd /var/www/staging/ci4-api-jwt

# Create staging .env
cat > .env << EOF
CI_ENVIRONMENT = development

app.baseURL = 'http://${VPS_IP}:8080'

database.default.hostname = mysql-staging
database.default.database = ci4_api_jwt_staging
database.default.username = ci4_user
database.default.password = ${STAGING_DB_PASS}
database.default.DBDriver = MySQLi
database.default.port = 3306

JWT_SECRET_KEY = staging_jwt_$(openssl rand -hex 16)
JWT_TIME_TO_LIVE = 7200

encryption.key = $(php spark key:generate --show)
EOF

echo "⚙️ Configuring production environment..."
cd /var/www/production/ci4-api-jwt

# Create production .env
cat > .env << EOF
CI_ENVIRONMENT = production

app.baseURL = 'http://${VPS_IP}'

database.default.hostname = mysql-production
database.default.database = ci4_api_jwt
database.default.username = ci4_user
database.default.password = ${PROD_DB_PASS}
database.default.DBDriver = MySQLi
database.default.port = 3306

JWT_SECRET_KEY = prod_jwt_$(openssl rand -hex 16)
JWT_TIME_TO_LIVE = 3600

encryption.key = $(php spark key:generate --show)
EOF

echo "✅ Environment configuration completed!"
echo ""
echo "Now you can run:"
echo "1. cd /var/www/staging/ci4-api-jwt && docker-compose -f docker-compose.staging.yml up -d --build"
echo "2. cd /var/www/production/ci4-api-jwt && docker-compose -f docker-compose.production.yml up -d --build"
