# 🚀 Setup Guide untuk VPS dengan aaPanel

## 📋 **Kondisi Server Anda**
- ✅ VPS dengan aaPanel sudah running
- ✅ Docker sudah terinstall dan berjalan
- ✅ Cloudflare Tunnel sudah setup
- ✅ Domain: `demo.enampuluhenam.web.id`
- ✅ Target Port: **8081**

## 🎯 **Langkah Setup Demo**

### **Step 1: SSH ke VPS Server**
```bash
ssh root@YOUR_VPS_IP
# atau user aaPanel Anda
```

### **Step 2: Deploy Menggunakan Script Otomatis**
```bash
# Download dan jalankan deployment script
curl -sSL https://raw.githubusercontent.com/solusiitkreasi/ci4-api-jwt/master/scripts/deploy-demo-vps.sh -o deploy-demo.sh
chmod +x deploy-demo.sh

# Deploy aplikasi
./deploy-demo.sh deploy
```

### **Step 3: Configure Environment (Manual jika diperlukan)**
```bash
# Masuk ke project directory
cd /var/www/ci4-api-jwt-demo

# Edit environment file
nano .env

# Sesuaikan dengan konfigurasi server Anda:
# - Database credentials (jika ingin pakai MySQL aaPanel yang ada)
# - Domain URL
# - JWT secrets
# - Email settings
```

### **Step 4: Update Cloudflare Tunnel**
Di Cloudflare Dashboard:
1. Go to Zero Trust → Access → Tunnels
2. Edit tunnel Anda
3. Add/Update public hostname:
   - **Subdomain**: `demo`
   - **Domain**: `enampuluhenam.web.id`
   - **Service**: `http://localhost:8081`

### **Step 5: Test Deployment**
```bash
# Test local
curl http://localhost:8081/api/ping

# Test domain (setelah Cloudflare sync)
curl https://demo.enampuluhenam.web.id/api/ping

# Check container status
./deploy-demo.sh status
```

## 🔧 **Management Commands**

```bash
# Deploy/Update aplikasi
./deploy-demo.sh deploy

# Check status
./deploy-demo.sh status

# Restart containers
./deploy-demo.sh restart

# Stop containers
./deploy-demo.sh stop

# View logs
cd /var/www/ci4-api-jwt-demo
docker-compose -f docker-compose.demo.yml logs -f

# Check container details
docker ps | grep demo
```

## 🐳 **Container Overview**

Setelah deployment berhasil, akan ada containers:
- `nginx-demo-ci4api` - Port 8081 → Nginx web server
- `php-fpm-demo-ci4api` - PHP-FPM untuk CI4 aplikasi
- `mysql-demo-ci4api` - Port 3308 → MySQL database
- `redis-demo-ci4api` - Port 6381 → Redis cache

## ⚙️ **Integration dengan aaPanel**

### **Database Option 1: Gunakan MySQL Container (Recommended)**
- Database terpisah dalam container
- Port 3308 (tidak conflict dengan aaPanel MySQL)
- Isolated dan mudah di-manage

### **Database Option 2: Gunakan MySQL aaPanel**
Edit `.env` file:
```bash
DB_HOST=localhost  # atau IP MySQL aaPanel
DB_PORT=3306       # Port MySQL aaPanel
DB_DATABASE=ci4_api_jwt_demo
DB_USERNAME=database_user_dari_aapanel
DB_PASSWORD=password_dari_aapanel
```

## 🔐 **GitHub Actions Auto-Deploy**

Untuk enable auto-deployment, tambahkan GitHub Secrets:
- **Name**: `DEMO_VPS_HOST`, **Value**: `YOUR_VPS_IP`
- **Name**: `DEMO_VPS_USERNAME`, **Value**: `root` atau user aaPanel
- **Name**: `DEMO_VPS_SSH_KEY**, **Value**: Private SSH key

Setelah itu, setiap push ke master branch akan auto-deploy ke demo environment.

## 🧪 **Testing Endpoints**

```bash
# Health check
curl https://demo.enampuluhenam.web.id/api/health

# Ping test
curl https://demo.enampuluhenam.web.id/api/ping

# API endpoints (setelah setup)
curl https://demo.enampuluhenam.web.id/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

## 🚨 **Troubleshooting**

### **Port sudah digunakan:**
```bash
# Check apa yang menggunakan port 8081
netstat -tlnp | grep :8081
lsof -i :8081

# Ganti port jika perlu
nano docker-compose.demo.yml
# Ubah "8081:80" ke port lain misal "8082:80"
```

### **Container tidak start:**
```bash
# Check logs
docker-compose -f docker-compose.demo.yml logs

# Check docker service
systemctl status docker

# Restart docker
systemctl restart docker
```

### **Domain tidak accessible:**
1. Check Cloudflare tunnel status
2. Verify port forwarding ke localhost:8081
3. Check firewall aaPanel/VPS

### **Database connection error:**
```bash
# Check MySQL container
docker logs mysql-demo-ci4api

# Test database connection
docker exec -it mysql-demo-ci4api mysql -u root -p
```

## ✅ **Success Indicators**

- [ ] Containers running: `docker ps | grep demo`
- [ ] Local accessible: `curl http://localhost:8081/api/ping`
- [ ] Domain accessible: `curl https://demo.enampuluhenam.web.id/api/ping`
- [ ] Health check OK: `curl https://demo.enampuluhenam.web.id/api/health`
- [ ] No conflicts dengan aaPanel services
- [ ] Cloudflare tunnel pointing correctly

---

**🎯 Ready untuk deployment! Mulai dengan Step 1 di atas.**
