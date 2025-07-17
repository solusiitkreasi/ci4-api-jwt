#!/bin/bash

# Demo Deployment Script untuk VPS dengan aaPanel
# Script untuk deploy CI4 API JWT ke Docker di server yang sudah ada

set -e

echo "🚀 Deploying CI4 API JWT Demo to VPS with aaPanel..."

# Variables
PROJECT_DIR="/var/www/ci4-api-jwt-demo"
REPO_URL="https://github.com/solusiitkreasi/ci4-api-jwt.git"
DEMO_PORT="8081"

# Function untuk deploy
deploy_demo() {
    echo "📁 Setting up project directory..."
    
    # Create project directory jika belum ada
    if [ ! -d "$PROJECT_DIR" ]; then
        echo "📦 Cloning repository..."
        git clone $REPO_URL $PROJECT_DIR
    else
        echo "📥 Updating repository..."
        cd $PROJECT_DIR
        git fetch origin
        git reset --hard origin/master
        git pull origin master
    fi
    
    cd $PROJECT_DIR
    
    echo "⚙️ Setting up environment..."
    
    # Copy environment file
    if [ ! -f ".env" ]; then
        cp .env.demo .env
        echo "✅ Environment file created from .env.demo"
        echo "⚠️  Please edit .env file with your actual configurations"
    else
        echo "⚙️ Environment file already exists"
    fi
    
    echo "🛑 Stopping existing containers..."
    docker-compose -f docker-compose.demo.yml down || true
    
    echo "🔨 Building and starting containers..."
    docker-compose -f docker-compose.demo.yml up -d --build
    
    echo "⏳ Waiting for containers to be ready..."
    sleep 20
    
    echo "📦 Installing dependencies..."
    docker-compose -f docker-compose.demo.yml exec -T php-fpm-demo composer install --optimize-autoloader --no-dev
    
    echo "🔒 Setting permissions..."
    docker-compose -f docker-compose.demo.yml exec -T php-fpm-demo chown -R www:www /var/www/html/writable
    docker-compose -f docker-compose.demo.yml exec -T php-fpm-demo chmod -R 755 /var/www/html/writable
    
    echo "🗃️ Running database migrations..."
    docker-compose -f docker-compose.demo.yml exec -T php-fpm-demo php spark migrate || echo "⚠️ Migration failed or already up to date"
    
    echo "🧹 Clearing cache..."
    docker-compose -f docker-compose.demo.yml exec -T php-fpm-demo php spark cache:clear || true
    
    echo "🧪 Testing deployment..."
    sleep 5
    
    # Test local port
    if curl -f http://localhost:$DEMO_PORT/api/ping &>/dev/null; then
        echo "✅ Local deployment test successful!"
    else
        echo "⚠️ Local deployment test failed"
    fi
    
    # Test domain (jika accessible)
    if curl -f https://demo.enampuluhenam.web.id/api/ping &>/dev/null; then
        echo "✅ Domain test successful!"
    else
        echo "⚠️ Domain test failed (normal jika Cloudflare belum sync)"
    fi
    
    echo ""
    echo "✅ Deployment completed successfully!"
    echo ""
    echo "📊 Container status:"
    docker-compose -f docker-compose.demo.yml ps
    echo ""
    echo "🔗 Access URLs:"
    echo "  Local: http://localhost:$DEMO_PORT"
    echo "  Domain: https://demo.enampuluhenam.web.id"
    echo "  Health: https://demo.enampuluhenam.web.id/api/health"
    echo "  API Docs: https://demo.enampuluhenam.web.id/api/docs"
    echo ""
    echo "📋 Next steps:"
    echo "1. Update Cloudflare tunnel to point to localhost:$DEMO_PORT"
    echo "2. Edit $PROJECT_DIR/.env with your actual configurations"
    echo "3. Test all endpoints"
}

# Function untuk stop deployment
stop_demo() {
    echo "🛑 Stopping demo deployment..."
    cd $PROJECT_DIR
    docker-compose -f docker-compose.demo.yml down
    echo "✅ Demo stopped"
}

# Function untuk restart deployment
restart_demo() {
    echo "🔄 Restarting demo deployment..."
    cd $PROJECT_DIR
    docker-compose -f docker-compose.demo.yml restart
    echo "✅ Demo restarted"
}

# Function untuk check status
status_demo() {
    echo "📊 Demo deployment status:"
    if [ -d "$PROJECT_DIR" ]; then
        cd $PROJECT_DIR
        docker-compose -f docker-compose.demo.yml ps
        echo ""
        echo "🔗 Quick tests:"
        curl -s http://localhost:$DEMO_PORT/api/ping || echo "❌ Local ping failed"
        curl -s https://demo.enampuluhenam.web.id/api/ping || echo "❌ Domain ping failed"
    else
        echo "❌ Project not deployed yet"
    fi
}

# Main script
case "$1" in
    "deploy")
        deploy_demo
        ;;
    "stop")
        stop_demo
        ;;
    "restart")
        restart_demo
        ;;
    "status")
        status_demo
        ;;
    *)
        echo "Usage: $0 {deploy|stop|restart|status}"
        echo ""
        echo "Commands:"
        echo "  deploy  - Deploy/update the demo application"
        echo "  stop    - Stop all demo containers"
        echo "  restart - Restart demo containers"
        echo "  status  - Check deployment status"
        echo ""
        echo "Examples:"
        echo "  $0 deploy   # First deployment or update"
        echo "  $0 status   # Check if everything is running"
        echo "  $0 restart  # Restart if having issues"
        exit 1
        ;;
esac
