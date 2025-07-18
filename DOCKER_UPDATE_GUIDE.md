# Docker Restart & Update Guide

## 1. Quick Restart (Tanpa Update Code)
```bash
# Restart semua services
docker-compose restart

# Restart service tertentu
docker-compose restart demo_app_nginx
docker-compose restart demo_app_php
docker-compose restart demo_app_mysql
```

## 2. Restart dengan Code Update
```bash
# Pull latest code dari repository
cd /home/ubuntu/docker_project/demo_app/src/
git pull origin main

# Restart containers
cd /home/ubuntu/docker_project/demo_app/
docker-compose restart
```

## 3. Restart dengan Composer Update
```bash
# Update PHP dependencies
docker-compose exec demo_app_php composer update

# Clear cache
docker-compose exec demo_app_php php spark cache:clear

# Restart PHP container
docker-compose restart demo_app_php
```

## 4. Full Rebuild (Major Updates)
```bash
# Stop all containers
docker-compose down

# Rebuild images dengan latest code
docker-compose build --no-cache

# Start dengan fresh containers
docker-compose up -d
```

## 5. Update dengan Environment Changes
```bash
# Edit environment file
nano .env

# Recreate containers dengan new env
docker-compose down
docker-compose up -d
```

## 6. Database Migration Update
```bash
# Run database migrations
docker-compose exec demo_app_php php spark migrate

# Restart aplikasi
docker-compose restart demo_app_php
```

## 7. Zero Downtime Update (Production)
```bash
# Scale up new version
docker-compose up -d --scale demo_app_php=2

# Wait for health check
sleep 30

# Scale down old version
docker-compose up -d --scale demo_app_php=1
```

## 8. Rollback Strategy
```bash
# Stop current version
docker-compose down

# Revert code
cd src/
git checkout HEAD~1

# Restart with previous version
cd ..
docker-compose up -d
```

## Common Update Scenarios

### Code Changes Only
```bash
# Copy updated files
sudo cp -r /www/wwwroot/demo.enampuluhenam.web.id/* /home/ubuntu/docker_project/demo_app/src/

# Restart PHP service
docker-compose restart demo_app_php
```

### Config Changes
```bash
# Update config files
docker-compose restart demo_app_nginx demo_app_php
```

### Database Schema Changes
```bash
# Run migrations
docker-compose exec demo_app_php php spark migrate

# Restart app
docker-compose restart demo_app_php
```

### New Dependencies
```bash
# Update composer
docker-compose exec demo_app_php composer install

# Restart
docker-compose restart demo_app_php
```

## Health Check Commands
```bash
# Check container status
docker-compose ps

# Check logs
docker-compose logs demo_app_php --tail 50

# Test application
curl -I http://localhost:8081

# Monitor resources
docker stats
```

## Best Practices

1. **Always backup** before major updates
2. **Test in staging** environment first  
3. **Monitor logs** during restart
4. **Check health endpoints** after restart
5. **Have rollback plan** ready

## Quick Commands Cheat Sheet
```bash
# Basic restart
docker-compose restart

# Full rebuild  
docker-compose down && docker-compose up -d --build

# Update code only
cp -r /source/* /target/ && docker-compose restart demo_app_php

# Database migration
docker-compose exec demo_app_php php spark migrate
```
