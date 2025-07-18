# VPS Console Deployment Guide

## Current Status
✅ VPS Console Access: Working  
✅ Project Files: Present in `/www/wwwroot/demo.enampuluhenam.web.id/`  
✅ Docker Compose Files: All present (`docker-compose.vps.yml`, etc.)  
✅ Docker Permissions: Fixed  

## Deployment Steps

### 1. Pastikan di Direktori Project
```bash
cd /www/wwwroot/demo.enampuluhenam.web.id/
pwd  # Pastikan berada di direktori yang benar
```

### 2. Set Permissions untuk Script
```bash
chmod +x scripts/deploy-vps.sh
ls -la scripts/deploy-vps.sh  # Verifikasi permissions
```

### 3. Cek Docker Compose File
```bash
ls -la docker-compose.vps.yml
cat docker-compose.vps.yml  # Pastikan file tidak corrupt
```

### 4. Jalankan Deployment
```bash
# Opsi 1: Jalankan script deployment
./scripts/deploy-vps.sh

# Opsi 2: Manual deployment jika script gagal
docker-compose -f docker-compose.vps.yml down
docker-compose -f docker-compose.vps.yml pull
docker-compose -f docker-compose.vps.yml up -d
```

### 5. Verifikasi Deployment
```bash
# Cek status container
docker ps

# Cek logs jika ada masalah
docker-compose -f docker-compose.vps.yml logs

# Test akses aplikasi
curl -I http://localhost:8081
```

## Troubleshooting

### Jika ada error "Permission denied"
```bash
sudo chmod +x scripts/deploy-vps.sh
sudo ./scripts/deploy-vps.sh
```

### Jika container tidak start
```bash
# Cek logs detail
docker-compose -f docker-compose.vps.yml logs ci4-api

# Restart services
docker-compose -f docker-compose.vps.yml restart
```

### Jika port conflict
```bash
# Cek port usage
netstat -tulpn | grep 8081

# Stop conflicting services jika ada
docker stop $(docker ps -q --filter "publish=8081")
```

## Expected Result
- Container `ci4-api` running di port 8081
- Aplikasi accessible via `http://localhost:8081` dan `https://demo.enampuluhenam.web.id`
- Database terhubung ke existing MySQL di `manajemen_network`

## Next Commands untuk VPS Console
```bash
cd /www/wwwroot/demo.enampuluhenam.web.id/
chmod +x scripts/deploy-vps.sh
./scripts/deploy-vps.sh
```
