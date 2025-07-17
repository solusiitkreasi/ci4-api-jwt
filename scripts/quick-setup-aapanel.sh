#!/bin/bash

# =====================================================
# Quick Setup Script untuk aaPanel VPS Demo Deployment
# Menggabungkan semua langkah dalam satu script
# =====================================================

set -e

# Warna untuk output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
PURPLE='\033[0;35m'
NC='\033[0m' # No Color

# Configuration
DOMAIN="demo.enampuluhenam.web.id"
DEPLOY_PATH="/www/wwwroot/$DOMAIN"
REPO_URL="https://github.com/your-username/ci4-api-jwt.git"  # Update dengan URL repo Anda
BRANCH="main"

echo -e "${PURPLE}============================================${NC}"
echo -e "${PURPLE} 🚀 aaPanel VPS Quick Setup & Deployment  ${NC}"
echo -e "${PURPLE}============================================${NC}"
echo -e "Domain: ${BLUE}$DOMAIN${NC}"
echo -e "Path: ${BLUE}$DEPLOY_PATH${NC}"
echo -e ""

# Function untuk logging
log_step() {
    echo -e "\n${BLUE}📋 STEP $1: $2${NC}"
    echo -e "${BLUE}----------------------------------------${NC}"
}

log_info() {
    echo -e "${GREEN}[INFO]${NC} $1"
}

log_warn() {
    echo -e "${YELLOW}[WARN]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

# Check if running as ubuntu user
if [ "$USER" != "ubuntu" ]; then
    log_error "This script must be run as ubuntu user!"
    exit 1
fi

# Step 1: Fix Docker Permissions
log_step "1" "Fixing Docker Permissions"
if ! docker ps >/dev/null 2>&1; then
    log_warn "Docker permission error detected. Fixing..."
    sudo usermod -aG docker ubuntu
    sudo systemctl restart docker
    sudo chmod 666 /var/run/docker.sock
    
    log_info "Testing Docker access..."
    if docker ps >/dev/null 2>&1; then
        log_success "Docker permissions fixed!"
    else
        log_error "Docker permission fix failed. Please run: newgrp docker"
        exit 1
    fi
else
    log_success "Docker permissions already OK!"
fi

# Step 2: System Requirements Check
log_step "2" "Checking System Requirements"

# Check memory
MEMORY_GB=$(free -g | awk 'NR==2{print $2}')
if [ "$MEMORY_GB" -lt 2 ]; then
    log_warn "Memory: ${MEMORY_GB}GB (recommended: 2GB+)"
else
    log_success "Memory: ${MEMORY_GB}GB ✓"
fi

# Check disk space
DISK_FREE=$(df -h / | awk 'NR==2{print $4}')
log_info "Free disk space: $DISK_FREE"

# Check port 8081
if ss -tuln | grep -q ":8081 "; then
    log_warn "Port 8081 is in use. Will try to stop conflicting services."
    sudo fuser -k 8081/tcp 2>/dev/null || true
else
    log_success "Port 8081 is available ✓"
fi

# Step 3: Setup Deployment Directory
log_step "3" "Setting Up Deployment Directory"
sudo mkdir -p "$DEPLOY_PATH"
sudo chown ubuntu:ubuntu "$DEPLOY_PATH"
cd "$DEPLOY_PATH"
log_success "Deployment directory ready: $DEPLOY_PATH"

# Step 4: Clone Repository
log_step "4" "Cloning Application Repository"
if [ -d ".git" ]; then
    log_info "Repository already exists. Pulling latest changes..."
    git pull origin "$BRANCH"
else
    log_info "Cloning repository..."
    git clone "$REPO_URL" .
    git checkout "$BRANCH"
fi
log_success "Repository ready!"

# Step 5: Environment Setup
log_step "5" "Setting Up Environment"
if [ ! -f ".env" ]; then
    if [ -f ".env.demo" ]; then
        cp .env.demo .env
        log_success "Environment file created from .env.demo"
    else
        log_error ".env.demo file not found!"
        exit 1
    fi
else
    log_info "Environment file already exists"
fi

# Step 6: Docker Deployment
log_step "6" "Deploying with Docker"

# Stop existing containers
log_info "Stopping existing containers..."
docker-compose -f docker-compose.demo.yml down --remove-orphans 2>/dev/null || true

# Pull latest images
log_info "Pulling latest Docker images..."
docker-compose -f docker-compose.demo.yml pull

# Build and start
log_info "Building and starting services..."
docker-compose -f docker-compose.demo.yml up -d --build

# Step 7: Health Checks
log_step "7" "Performing Health Checks"

log_info "Waiting for services to start (30 seconds)..."
sleep 30

# Check containers
log_info "Checking container status..."
CONTAINERS=("nginx-demo-ci4api" "app-demo-ci4api" "mysql-demo-ci4api" "redis-demo-ci4api")
ALL_RUNNING=true

for container in "${CONTAINERS[@]}"; do
    if docker ps --format "table {{.Names}}" | grep -q "$container"; then
        log_success "✓ $container is running"
    else
        log_error "✗ $container failed to start"
        docker logs "$container" --tail 10
        ALL_RUNNING=false
    fi
done

if [ "$ALL_RUNNING" = false ]; then
    log_error "Some containers failed to start. Check logs above."
    exit 1
fi

# Test local access
log_info "Testing local access..."
sleep 10
if curl -f http://localhost:8081/health >/dev/null 2>&1; then
    log_success "✓ Application is responding locally"
else
    log_warn "Local health check failed. Checking logs..."
    docker logs nginx-demo-ci4api --tail 5
    docker logs app-demo-ci4api --tail 5
fi

# Step 8: Database & Redis Tests
log_step "8" "Testing Database Connections"

# Get passwords from .env
DB_PASSWORD=$(grep "^DB_PASSWORD=" .env | cut -d'=' -f2)
REDIS_PASSWORD=$(grep "^REDIS_PASSWORD=" .env | cut -d'=' -f2)

# Test MySQL
if docker exec mysql-demo-ci4api mysql -u root -p"$DB_PASSWORD" -e "SELECT 1;" >/dev/null 2>&1; then
    log_success "✓ MySQL connection successful"
else
    log_error "✗ MySQL connection failed"
    docker logs mysql-demo-ci4api --tail 5
fi

# Test Redis
if docker exec redis-demo-ci4api redis-cli -a "$REDIS_PASSWORD" ping >/dev/null 2>&1; then
    log_success "✓ Redis connection successful"
else
    log_error "✗ Redis connection failed"
    docker logs redis-demo-ci4api --tail 5
fi

# Final Status Report
echo -e "\n${GREEN}============================================${NC}"
echo -e "${GREEN} 🎉 DEPLOYMENT COMPLETED!                 ${NC}"
echo -e "${GREEN}============================================${NC}"
echo -e ""
echo -e "🌐 Application URLs:"
echo -e "   Local:    ${BLUE}http://localhost:8081${NC}"
echo -e "   External: ${BLUE}https://$DOMAIN${NC}"
echo -e ""
echo -e "📊 Container Status:"
docker ps --format "table {{.Names}}\t{{.Status}}\t{{.Ports}}" | grep "ci4api" || true
echo -e ""
echo -e "💾 System Resources:"
echo -e "   Memory: $(free -h | awk 'NR==2{printf \"%.1f/%.1f GB\", $3/1024/1024/1024, $2/1024/1024/1024}')"
echo -e "   Disk:   $(df -h "$DEPLOY_PATH" | awk 'NR==2{print $3"/"$2}')"
echo -e ""
echo -e "${YELLOW}📝 Next Steps:${NC}"
echo -e "1. Configure Cloudflare tunnel to point to localhost:8081"
echo -e "2. Test external access: curl -I https://$DOMAIN/health"
echo -e "3. Monitor logs: docker-compose -f docker-compose.demo.yml logs -f"
echo -e ""
echo -e "${GREEN}🚀 Ready for production use!${NC}"

# Save deployment info
cat > deployment-info.txt << EOF
Deployment completed at: $(date)
Domain: $DOMAIN
Path: $DEPLOY_PATH
Branch: $BRANCH
Containers: nginx-demo-ci4api, app-demo-ci4api, mysql-demo-ci4api, redis-demo-ci4api
Ports: 8081 (nginx), 3309 (mysql), 6382 (redis)
Local URL: http://localhost:8081
External URL: https://$DOMAIN

Useful commands:
- View logs: docker-compose -f docker-compose.demo.yml logs -f
- Restart: docker-compose -f docker-compose.demo.yml restart
- Stop: docker-compose -f docker-compose.demo.yml down
- Status: docker-compose -f docker-compose.demo.yml ps
EOF

log_success "Deployment info saved to deployment-info.txt"
