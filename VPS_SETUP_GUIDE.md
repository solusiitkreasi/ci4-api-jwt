# 🚀 VPS Setup Guide - Praktis & Cepat

## 📋 **Prerequisites**
- VPS server dengan Ubuntu 20.04+ atau Debian 11+
- Minimal 2GB RAM, 20GB storage
- Akses SSH ke VPS server
- Domain/IP public yang bisa diakses

## ⚡ **Quick Setup (5 Menit)**

### **1. SSH ke VPS Server**
```bash
ssh root@YOUR_VPS_IP
```

### **2. Download & Run Setup Script**
```bash
curl -sSL https://raw.githubusercontent.com/solusiitkreasi/ci4-api-jwt/master/scripts/quick-setup-vps.sh | bash
```

### **3. Configure Environment**
```bash
curl -sSL https://raw.githubusercontent.com/solusiitkreasi/ci4-api-jwt/master/scripts/setup-environment.sh | bash
```

### **4. Deploy Staging**
```bash
cd /var/www/staging/ci4-api-jwt
docker-compose -f docker-compose.staging.yml up -d --build
```

### **5. Deploy Production**
```bash
cd /var/www/production/ci4-api-jwt  
docker-compose -f docker-compose.production.yml up -d --build
```

### **6. Test Deployment**
```bash
curl -sSL https://raw.githubusercontent.com/solusiitkreasi/ci4-api-jwt/master/scripts/test-deployment.sh | bash -s YOUR_VPS_IP
```

## ✅ **Verification Checklist**

- [ ] Staging accessible: `http://YOUR_VPS_IP:8080/api/ping`
- [ ] Production accessible: `http://YOUR_VPS_IP/api/ping`
- [ ] Health check working: `http://YOUR_VPS_IP/api/health`
- [ ] Containers running: `docker ps`
- [ ] Logs clean: `docker-compose logs`

## 🔧 **Manual Setup (Jika Script Gagal)**

### **Install Docker**
```bash
apt update && apt upgrade -y
curl -fsSL https://get.docker.com | sh
curl -L "https://github.com/docker/compose/releases/latest/download/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
chmod +x /usr/local/bin/docker-compose
```

### **Setup Firewall**
```bash
ufw allow ssh && ufw allow 80 && ufw allow 8080 && ufw --force enable
```

### **Clone Repository**
```bash
mkdir -p /var/www/staging /var/www/production
git clone https://github.com/solusiitkreasi/ci4-api-jwt.git /var/www/staging/ci4-api-jwt
git clone https://github.com/solusiitkreasi/ci4-api-jwt.git /var/www/production/ci4-api-jwt
```

### **Configure Environment Files**

**Staging:**
```bash
cd /var/www/staging/ci4-api-jwt
cp .env.staging .env
nano .env  # Edit sesuai kebutuhan
```

**Production:**
```bash
cd /var/www/production/ci4-api-jwt
cp .env.production .env
nano .env  # Edit sesuai kebutuhan
```

## 🔐 **Setup GitHub Secrets untuk CI/CD**

1. Buka GitHub Repository → Settings → Secrets
2. Add New Repository Secret:
   - **Name**: `VPS_HOST` **Value**: `YOUR_VPS_IP`
   - **Name**: `VPS_USERNAME` **Value**: `root`
   - **Name**: `VPS_SSH_KEY` **Value**: `Private SSH Key Content`

### **Generate SSH Key (jika perlu):**
```bash
ssh-keygen -t rsa -b 4096 -C "deploy@vps"
cat ~/.ssh/id_rsa.pub >> ~/.ssh/authorized_keys
cat ~/.ssh/id_rsa  # Copy content untuk GitHub Secret
```

## 🚨 **Troubleshooting**

### **Container tidak start:**
```bash
docker-compose logs
docker ps -a
systemctl status docker
```

### **Port sudah digunakan:**
```bash
netstat -tlnp | grep :80
netstat -tlnp | grep :8080
```

### **Reset semua container:**
```bash
docker-compose down -v
docker system prune -a
docker-compose up -d --build
```

### **Cek logs:**
```bash
# Application logs
docker-compose logs -f php-fpm
docker-compose logs -f nginx-production

# System logs
journalctl -u docker
tail -f /var/log/nginx/error.log
```

## 📊 **Monitoring Commands**

```bash
# Container status
docker ps

# Resource usage
docker stats

# Logs
docker-compose logs -f

# System resources
htop
df -h
free -h

# Network ports
netstat -tlnp
```

## 🎯 **Next Steps Setelah Setup**

1. **Setup SSL Certificate:**
   ```bash
   apt install certbot python3-certbot-nginx
   certbot --nginx -d yourdomain.com
   ```

2. **Setup Domain:**
   - Point domain A record ke VPS IP
   - Update .env dengan domain URL

3. **Database Backup:**
   - Setup cron job untuk backup otomatis
   - Test restore procedure

4. **Monitoring:**
   - Setup log rotation
   - Configure alerts
   - Monitor disk space

## 🔗 **Access URLs**

- **Staging**: `http://YOUR_VPS_IP:8080`
- **Production**: `http://YOUR_VPS_IP`
- **Health Check**: `http://YOUR_VPS_IP/api/health`
- **API Documentation**: `http://YOUR_VPS_IP/api/docs`

---

**✅ Setup berhasil jika semua URL di atas dapat diakses dan mengembalikan response JSON yang valid.**
