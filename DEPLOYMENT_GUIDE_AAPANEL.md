# 🚀 Panduan Deployment ke aaPanel VPS - Step by Step

## 📋 Prerequisites Checklist

Pastikan hal-hal berikut sudah ready:

### ✅ Server Requirements
- [x] Ubuntu VPS dengan aaPanel installed
- [x] Docker & Docker Compose installed
- [x] Domain demo.enampuluhenam.web.id dengan Cloudflare tunnel
- [x] Port 8081 tersedia (verified)
- [x] Minimum 2GB RAM (sudah ada)

### ✅ Local Development
- [x] Git repository dengan semua file DevOps
- [x] GitHub Actions pipeline configured
- [x] SSH access ke VPS

---

## 🔧 Step 1: Fix Docker Permissions di VPS

**Masalah yang ditemukan:** Docker permission denied untuk user `ubuntu`

### Langkah-langkah:

1. **Login ke VPS via SSH:**
```bash
ssh ubuntu@your-vps-ip
```

2. **Upload dan jalankan script fix permission:**
```bash
# Download script dari repository
wget https://raw.githubusercontent.com/your-username/your-repo/main/scripts/fix-docker-permission.sh

# Atau copy manual script ini:
cat > fix-docker-permission.sh << 'EOF'
#!/bin/bash
echo "🔧 Fixing Docker permissions for aaPanel VPS..."
sudo usermod -aG docker ubuntu
sudo systemctl restart docker
sudo chmod 666 /var/run/docker.sock
newgrp docker
echo "✅ Docker permissions fixed!"
echo "Testing Docker access..."
docker --version
docker ps
EOF

# Jalankan script
chmod +x fix-docker-permission.sh
./fix-docker-permission.sh
```

3. **Verify Docker access:**
```bash
docker ps
docker --version
```

**Expected Output:** Tidak ada error "permission denied"

---

## 🏗️ Step 2: Setup Deployment Directory

1. **Buat direktori deployment:**
```bash
sudo mkdir -p /www/wwwroot/demo.enampuluhenam.web.id
sudo chown ubuntu:ubuntu /www/wwwroot/demo.enampuluhenam.web.id
cd /www/wwwroot/demo.enampuluhenam.web.id
```

2. **Verify directory permissions:**
```bash
ls -la /www/wwwroot/
# Pastikan ubuntu punya akses ke demo.enampuluhenam.web.id
```

---

## 📦 Step 3: Deploy via GitHub Actions

### Option A: Automatic Deployment (Recommended)

1. **Push ke GitHub untuk trigger deployment:**
```bash
# Di local development PC
git add .
git commit -m "Deploy to aaPanel VPS demo environment"
git push origin main
```

2. **Monitor GitHub Actions:**
   - Buka repository di GitHub
   - Go to Actions tab
   - Lihat workflow "CI/CD Pipeline" 
   - Monitor job "deploy-demo"

### Option B: Manual Deployment

Jika GitHub Actions tidak bisa connect ke VPS, bisa manual:

1. **Clone repository di VPS:**
```bash
cd /www/wwwroot/demo.enampuluhenam.web.id
git clone https://github.com/your-username/your-repo.git .
```

2. **Jalankan deployment script:**
```bash
chmod +x scripts/deploy-aapanel-demo.sh
./scripts/deploy-aapanel-demo.sh
```

---

## 🔍 Step 4: Verify Deployment

### 4.1 Check Container Status
```bash
cd /www/wwwroot/demo.enampuluhenam.web.id
docker-compose -f docker-compose.demo.yml ps
```

**Expected Output:**
```
Name                     Command               State           Ports
------------------------------------------------------------------------
app-demo-ci4api         docker-php-entrypoint php-fpm    Up      9000/tcp
mysql-demo-ci4api       docker-entrypoint.sh mysqld      Up      0.0.0.0:3309->3306/tcp
nginx-demo-ci4api       /docker-entrypoint.sh nginx ...  Up      0.0.0.0:8081->80/tcp
redis-demo-ci4api       docker-entrypoint.sh redis ...   Up      0.0.0.0:6382->6379/tcp
```

### 4.2 Test Local Access
```bash
# Test health endpoint
curl http://localhost:8081/health

# Expected: "Healthy - aaPanel Demo Environment"
```

### 4.3 Test Database Connection
```bash
# Test MySQL connection
docker exec mysql-demo-ci4api mysql -u ci4_demo_user -p'Demo_VPS_SecurE_P4ssw0rd_2024!' -e "SELECT 1;"

# Test Redis connection  
docker exec redis-demo-ci4api redis-cli -a 'demo_redis_secure_2024!' ping
```

---

## 🌐 Step 5: Configure Cloudflare Tunnel

### 5.1 Update Cloudflare Tunnel Configuration

Di Cloudflare Zero Trust dashboard:

1. **Go to Access > Tunnels**
2. **Edit tunnel untuk demo.enampuluhenam.web.id**
3. **Update Public Hostname:**
   - **Subdomain:** demo
   - **Domain:** enampuluhenam.web.id
   - **Service Type:** HTTP
   - **URL:** localhost:8081

### 5.2 Test External Access
```bash
# Test dari local PC atau device lain
curl -I https://demo.enampuluhenam.web.id/health
```

**Expected:** HTTP 200 response

---

## 📊 Step 6: Monitoring & Maintenance

### 6.1 View Logs
```bash
# View all containers logs
docker-compose -f docker-compose.demo.yml logs -f

# View specific container
docker logs nginx-demo-ci4api -f
docker logs app-demo-ci4api -f
docker logs mysql-demo-ci4api -f
docker logs redis-demo-ci4api -f
```

### 6.2 Monitor Resources
```bash
# System resources
free -h
df -h

# Docker resources
docker stats
```

### 6.3 Backup Data
```bash
# Backup database
docker exec mysql-demo-ci4api mysqldump -u root -p'Demo_MySQL_R00t_P4ssw0rd_2024!' ci4_api_jwt_demo > backup_$(date +%Y%m%d).sql

# Backup application files
tar -czf app_backup_$(date +%Y%m%d).tar.gz /www/wwwroot/demo.enampuluhenam.web.id
```

---

## 🚨 Troubleshooting

### Common Issues & Solutions

#### Issue 1: Port 8081 Already in Use
```bash
# Check what's using port 8081
sudo netstat -tulpn | grep :8081
# Kill process if needed
sudo kill -9 <PID>
```

#### Issue 2: Database Connection Failed
```bash
# Check MySQL container logs
docker logs mysql-demo-ci4api

# Reset MySQL container
docker-compose -f docker-compose.demo.yml restart mysql-demo
```

#### Issue 3: Nginx 502 Bad Gateway
```bash
# Check app container
docker logs app-demo-ci4api

# Restart app container
docker-compose -f docker-compose.demo.yml restart app
```

#### Issue 4: Low Memory
```bash
# Clear Docker unused resources
docker system prune -f

# Check memory usage
free -h
```

---

## 📝 Final Checklist

- [ ] Docker permissions fixed
- [ ] All containers running (4 containers)
- [ ] Port 8081 accessible locally
- [ ] Database connection working
- [ ] Redis connection working
- [ ] Cloudflare tunnel configured
- [ ] External access working (https://demo.enampuluhenam.web.id)
- [ ] Health endpoint responding
- [ ] Logs monitoring setup

---

## 🎉 Success Indicators

Jika semua berhasil, Anda akan melihat:

1. **Container Status:** 4 containers running
2. **Local Access:** `curl http://localhost:8081/health` returns "Healthy"
3. **External Access:** `https://demo.enampuluhenam.web.id` accessible
4. **Performance:** Response time < 2 seconds
5. **Memory Usage:** < 1.5GB dari 2GB total

**🚀 Deployment Complete! Aplikasi siap digunakan.**
