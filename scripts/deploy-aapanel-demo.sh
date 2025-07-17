#!/bin/bash

# =====================================================
# Deployment Script untuk aaPanel VPS Demo Environment
# Server: 2 vCPU, 2GB RAM, Ubuntu dengan Docker
# Domain: demo.enampuluhenam.web.id (Cloudflare tunnel)
# =====================================================

set -e

# Warna untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

PROJECT_NAME="ci4-api-jwt-demo"
DEPLOY_PATH="/www/wwwroot/demo.enampuluhenam.web.id"
DOCKER_COMPOSE_FILE="docker-compose.demo.yml"
ENV_FILE=".env.demo"

echo -e "${BLUE}======================================================${NC}"
echo -e "${BLUE}  🚀 Deployment ke aaPanel VPS Demo Environment      ${NC}"
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

# 2. Verifikasi port availability
log_info "Checking port availability..."
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

# 3. Create deployment directory
log_info "Creating deployment directory..."
sudo mkdir -p "$DEPLOY_PATH"
sudo chown ubuntu:ubuntu "$DEPLOY_PATH"
cd "$DEPLOY_PATH"

# 4. Backup existing deployment if exists
if [ -f "$DOCKER_COMPOSE_FILE" ]; then
    log_warn "Existing deployment found. Creating backup..."
    sudo tar -czf "backup-$(date +%Y%m%d_%H%M%S).tar.gz" . 2>/dev/null || true
fi

# 5. Copy deployment files
log_info "Copying deployment files..."
cp "$GITHUB_WORKSPACE/$DOCKER_COMPOSE_FILE" .
cp "$GITHUB_WORKSPACE/$ENV_FILE" .env
cp -r "$GITHUB_WORKSPACE/docker" .

# 6. Validate environment variables
log_info "Validating environment configuration..."
if [ ! -f ".env" ]; then
    log_error "Environment file not found!"
    exit 1
fi

# Check required variables
required_vars=("DB_DATABASE" "DB_USERNAME" "DB_PASSWORD" "REDIS_PASSWORD" "JWT_SECRET" "ENCRYPTION_KEY")
for var in "${required_vars[@]}"; do
    if ! grep -q "^$var=" .env; then
        log_error "Required environment variable $var not found in .env"
        exit 1
    fi
done

# 7. Check system resources
log_info "Checking system resources..."
MEMORY_MB=$(free -m | awk 'NR==2{print $7}')
if [ "$MEMORY_MB" -lt 500 ]; then
    log_warn "Low available memory: ${MEMORY_MB}MB. Consider stopping other services."
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
    "nginx-demo-ci4api"
    "app-demo-ci4api"
    "mysql-demo-ci4api"
    "redis-demo-ci4api"
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
DB_PASSWORD=$(grep "^DB_PASSWORD=" .env | cut -d'=' -f2)
if docker exec mysql-demo-ci4api mysql -u root -p"$DB_PASSWORD" -e "SELECT 1;" >/dev/null 2>&1; then
    log_info "✓ Database connection successful"
else
    log_error "✗ Database connection failed"
    docker logs mysql-demo-ci4api --tail 10
    exit 1
fi

# 14. Test Redis connection
log_info "Testing Redis connection..."
REDIS_PASSWORD=$(grep "^REDIS_PASSWORD=" .env | cut -d'=' -f2)
if docker exec redis-demo-ci4api redis-cli -a "$REDIS_PASSWORD" ping >/dev/null 2>&1; then
    log_info "✓ Redis connection successful"
else
    log_error "✗ Redis connection failed"
    docker logs redis-demo-ci4api --tail 10
    exit 1
fi

# 15. Test web application
log_info "Testing web application..."
if curl -f http://localhost:8081/health >/dev/null 2>&1; then
    log_info "✓ Web application is responding"
else
    log_warn "Web application health check failed. Checking logs..."
    docker logs nginx-demo-ci4api --tail 10
    docker logs app-demo-ci4api --tail 10
fi

# 16. Display deployment status
echo -e "\n${GREEN}======================================================${NC}"
echo -e "${GREEN}  🎉 Deployment Completed Successfully!              ${NC}"
echo -e "${GREEN}======================================================${NC}"
echo -e "Application URL: ${BLUE}https://demo.enampuluhenam.web.id${NC}"
echo -e "Local URL: ${BLUE}http://localhost:8081${NC}"
echo -e "Cloudflare Tunnel: ${BLUE}Configured for port 8081${NC}"
echo -e ""
echo -e "Container Status:"
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep "ci4api"
echo -e ""
echo -e "System Resources:"
echo -e "Memory Usage: $(free -h | awk 'NR==2{printf \"%.1f/%.1f GB (%.0f%%)\", $3/1024, $2/1024, $3*100/$2}')"
echo -e "Disk Usage: $(df -h "$DEPLOY_PATH" | awk 'NR==2{print $3"/"$2" ("$5")"}')"
echo -e ""
echo -e "${YELLOW}Important Notes:${NC}"
echo -e "1. Pastikan Cloudflare tunnel mengarah ke localhost:8081"
echo -e "2. Database MySQL berjalan di port 3309 (untuk avoid conflict)"
echo -e "3. Redis berjalan di port 6382 (untuk avoid conflict)"
echo -e "4. Monitoring: docker-compose -f $DOCKER_COMPOSE_FILE logs -f"
echo -e ""
echo -e "${GREEN}Deployment berhasil! 🚀${NC}"
