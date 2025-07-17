#!/bin/bash

# =====================================================
# Deployment Script untuk VPS dengan aaPanel + Shared phpMyAdmin
# Database: demo_db (sudah dibuat)
# Network: manajemen_network (eksternal untuk phpMyAdmin)
# =====================================================

set -e

# Warna untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

PROJECT_NAME="ci4-api-jwt"
DEPLOY_PATH="/www/wwwroot/demo.enampuluhenam.web.id"
DOCKER_COMPOSE_FILE="docker-compose.vps.yml"
ENV_FILE=".env.vps"

echo -e "${BLUE}======================================================${NC}"
echo -e "${BLUE}  🚀 Deployment CI4 API JWT ke VPS aaPanel           ${NC}"
echo -e "${BLUE}======================================================${NC}"

# Function untuk logging
log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# 1. Verifikasi Docker permissions
log_info "Checking Docker permissions..."
if ! docker ps >/dev/null 2>&1; then
    log_error "Docker permission error detected!"
    log_warn "Running Docker permission fix..."
    sudo usermod -aG docker ubuntu
    sudo systemctl restart docker
    sudo chmod 666 /var/run/docker.sock
    log_info "Docker permissions fixed. Please re-run this script."
    exit 1
fi

# 2. Verifikasi external network
log_info "Checking external network 'manajemen_network'..."
if ! docker network ls | grep -q "manajemen_network"; then
    log_warn "Creating external network 'manajemen_network'..."
    docker network create manajemen_network
fi

# 3. Verifikasi port availability
log_info "Checking port 8081 availability..."
if ss -tuln | grep -q ":8081 "; then
    log_warn "Port 8081 is in use. Checking if it's our container..."
    if docker ps | grep -q "8081:80"; then
        log_info "Port 8081 is used by our container. Continuing..."
    else
        log_error "Port 8081 is used by another service!"
        ss -tuln | grep ":8081"
        exit 1
    fi
fi

# 4. Create deployment directory
log_info "Setting up deployment directory..."
sudo mkdir -p "$DEPLOY_PATH"
sudo chown ubuntu:ubuntu "$DEPLOY_PATH"
cd "$DEPLOY_PATH"

# 5. Backup existing deployment if exists
if [ -f "$DOCKER_COMPOSE_FILE" ]; then
    log_warn "Existing deployment found. Creating backup..."
    sudo tar -czf "backup-$(date +%Y%m%d_%H%M%S).tar.gz" . 2>/dev/null || true
fi

# 6. Copy deployment files
log_info "Copying deployment files..."
cp "$GITHUB_WORKSPACE/$DOCKER_COMPOSE_FILE" .
cp "$GITHUB_WORKSPACE/$ENV_FILE" .env
cp -r "$GITHUB_WORKSPACE/docker" .

# 7. Validate environment variables
log_info "Validating environment configuration..."
if [ ! -f ".env" ]; then
    log_error "Environment file not found!"
    exit 1
fi

# 8. Stop existing containers
log_info "Stopping existing containers..."
docker-compose -f "$DOCKER_COMPOSE_FILE" down --remove-orphans 2>/dev/null || true

# 9. Pull latest images
log_info "Pulling latest Docker images..."
docker-compose -f "$DOCKER_COMPOSE_FILE" pull

# 10. Build and start services
log_info "Building and starting services..."
docker-compose -f "$DOCKER_COMPOSE_FILE" up -d --build

# 11. Wait for services to be ready
log_info "Waiting for services to be ready..."
sleep 30

# 12. Health checks
log_info "Performing health checks..."

# Check if containers are running
CONTAINERS=(
    "ci4_api_jwt_nginx"
    "ci4_api_jwt_app"
    "ci4_api_jwt_mysql"
    "ci4_api_jwt_redis"
)

for container in "${CONTAINERS[@]}"; do
    if docker ps | grep -q "$container"; then
        log_info "✓ Container $container is running"
    else
        log_error "✗ Container $container is not running"
        docker logs "$container" --tail 20
        exit 1
    fi
done

# 13. Test database connection
log_info "Testing database connection..."
if docker exec ci4_api_jwt_mysql mysql -u demo_user -p'userpassword' -e "USE demo_db; SELECT 1;" >/dev/null 2>&1; then
    log_info "✓ Database connection successful"
else
    log_error "✗ Database connection failed"
    docker logs ci4_api_jwt_mysql --tail 10
    exit 1
fi

# 14. Test Redis connection
log_info "Testing Redis connection..."
if docker exec ci4_api_jwt_redis redis-cli -a "ci4redis2024" ping >/dev/null 2>&1; then
    log_info "✓ Redis connection successful"
else
    log_error "✗ Redis connection failed"
    docker logs ci4_api_jwt_redis --tail 10
    exit 1
fi

# 15. Test web application
log_info "Testing web application..."
if curl -f http://localhost:8081/health >/dev/null 2>&1; then
    log_info "✓ Web application is responding"
else
    log_warn "Web application health check failed. Checking logs..."
    docker logs ci4_api_jwt_nginx --tail 10
    docker logs ci4_api_jwt_app --tail 10
fi

# 16. Display deployment status
echo -e "\n${GREEN}======================================================${NC}"
echo -e "${GREEN}  🎉 Deployment Completed Successfully!              ${NC}"
echo -e "${GREEN}======================================================${NC}"
echo -e "Application URL: ${BLUE}https://demo.enampuluhenam.web.id${NC}"
echo -e "Local URL: ${BLUE}http://localhost:8081${NC}"
echo -e "phpMyAdmin: ${BLUE}Accessible via shared phpMyAdmin${NC}"
echo -e ""
echo -e "Container Status:"
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep "ci4_api_jwt"
echo -e ""
echo -e "Database Info:"
echo -e "- Database: demo_db"
echo -e "- User: demo_user"
echo -e "- Password: userpassword"
echo -e "- Host: ci4_api_jwt_mysql (dalam network manajemen_network)"
echo -e ""
echo -e "System Resources:"
echo -e "Memory Usage: $(free -h | awk 'NR==2{printf \"%.1f/%.1f GB (%.0f%%)\", $3/1024, $2/1024, $3*100/$2}')"
echo -e "Disk Usage: $(df -h "$DEPLOY_PATH" | awk 'NR==2{print $3"/"$2" ("$5")"}')"
echo -e ""
echo -e "${YELLOW}Important Notes:${NC}"
echo -e "1. Database terhubung ke network 'manajemen_network' untuk phpMyAdmin"
echo -e "2. Pastikan Cloudflare tunnel mengarah ke localhost:8081"
echo -e "3. Database 'demo_db' sudah ready untuk digunakan"
echo -e "4. Monitoring: docker-compose -f $DOCKER_COMPOSE_FILE logs -f"
echo -e ""
echo -e "${GREEN}Deployment berhasil! 🚀${NC}"
