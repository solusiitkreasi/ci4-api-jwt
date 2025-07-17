#!/bin/bash

# VPS Deployment Script
# Script untuk deploy otomatis ke VPS

set -e

# Konfigurasi
VPS_HOST="your-vps-ip"
VPS_USER="your-username"
VPS_SSH_KEY="~/.ssh/id_rsa"
REPO_URL="https://github.com/solusiitkreasi/ci4-api-jwt.git"

# Fungsi untuk deploy ke environment tertentu
deploy_to_environment() {
    local ENV=$1
    local BRANCH=$2
    local PORT=$3
    
    echo "🚀 Deploying to $ENV environment..."
    
    ssh -i $VPS_SSH_KEY $VPS_USER@$VPS_HOST << EOF
        set -e
        
        # Masuk ke direktori deployment
        cd /var/www/$ENV
        
        # Clone repo jika belum ada
        if [ ! -d "ci4-api-jwt" ]; then
            echo "📦 Cloning repository..."
            git clone $REPO_URL ci4-api-jwt
        fi
        
        cd ci4-api-jwt
        
        # Backup database jika production
        if [ "$ENV" = "production" ]; then
            echo "💾 Creating database backup..."
            timestamp=\$(date +%Y%m%d_%H%M%S)
            docker exec mysql-production mysqldump -u root -p\$MYSQL_ROOT_PASSWORD ci4_api_jwt > backup_\$timestamp.sql || true
        fi
        
        # Git pull latest changes
        echo "📥 Pulling latest changes from $BRANCH..."
        git fetch origin
        git checkout $BRANCH
        git pull origin $BRANCH
        
        # Copy environment file
        if [ ! -f ".env" ]; then
            echo "⚙️ Creating environment file..."
            cp env-example .env
            # Update environment specific configurations
            if [ "$ENV" = "production" ]; then
                sed -i 's/CI_ENVIRONMENT = development/CI_ENVIRONMENT = production/' .env
            else
                sed -i 's/CI_ENVIRONMENT = development/CI_ENVIRONMENT = development/' .env
            fi
        fi
        
        # Stop existing containers
        echo "🛑 Stopping existing containers..."
        docker-compose -f docker-compose.$ENV.yml down || true
        
        # Build dan start containers
        echo "🔨 Building and starting containers..."
        docker-compose -f docker-compose.$ENV.yml build --no-cache
        docker-compose -f docker-compose.$ENV.yml up -d
        
        # Tunggu container siap
        echo "⏳ Waiting for containers to be ready..."
        sleep 15
        
        # Install dependencies
        echo "📦 Installing dependencies..."
        docker-compose -f docker-compose.$ENV.yml exec -T php-fpm composer install --optimize-autoloader \$([ "$ENV" = "production" ] && echo "--no-dev" || echo "")
        
        # Set permissions
        echo "🔒 Setting permissions..."
        docker-compose -f docker-compose.$ENV.yml exec -T php-fpm chown -R www:www /var/www/html/writable
        docker-compose -f docker-compose.$ENV.yml exec -T php-fpm chmod -R 755 /var/www/html/writable
        
        # Database migrations
        echo "🗃️ Running database migrations..."
        docker-compose -f docker-compose.$ENV.yml exec -T php-fpm php spark migrate || true
        
        # Clear cache
        echo "🧹 Clearing cache..."
        docker-compose -f docker-compose.$ENV.yml exec -T php-fpm php spark cache:clear || true
        
        # Test deployment
        echo "🧪 Testing deployment..."
        sleep 5
        curl -f http://localhost:$PORT/api/health || echo "⚠️ Health check failed"
        
        echo "✅ Deployment to $ENV completed successfully!"
        echo "🌐 Access URL: http://$VPS_HOST:$PORT"
EOF
}

# Main deployment logic
case "$1" in
    "staging")
        deploy_to_environment "staging" "develop" "8080"
        ;;
    "production")
        deploy_to_environment "production" "master" "80"
        ;;
    "both")
        deploy_to_environment "staging" "develop" "8080"
        deploy_to_environment "production" "master" "80"
        ;;
    *)
        echo "Usage: $0 {staging|production|both}"
        echo ""
        echo "Examples:"
        echo "  $0 staging     # Deploy to staging environment"
        echo "  $0 production  # Deploy to production environment"
        echo "  $0 both        # Deploy to both environments"
        exit 1
        ;;
esac
