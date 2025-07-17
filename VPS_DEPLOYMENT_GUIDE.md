# 🚀 Panduan Deployment CI4 API JWT ke VPS aaPanel

## 📋 Setup yang Sudah Ada di VPS Anda:

### ✅ Database Configuration:
- **Database**: `demo_db`
- **User**: `demo_user`
- **Password**: `userpassword`
- **Root Password**: `rootpassword`

### ✅ Network Configuration:
- **External Network**: `manajemen_network` (untuk shared phpMyAdmin)
- **Port**: `8081` (untuk Nginx)
- **Domain**: `demo.enampuluhenam.web.id`

---

## 🔧 Step-by-Step Deployment

### **Step 1: Persiapan di Local (PC Anda)**

1. **Commit dan Push ke GitHub:**
```bash
git add .
git commit -m "Add VPS deployment configuration for aaPanel"
git push origin master
```

### **Step 2: Setup di VPS**

1. **SSH ke VPS:**
```bash
ssh ubuntu@your-vps-ip
```

2. **Pastikan Docker Permission OK:**
```bash
# Test Docker access
docker ps

# Jika error, jalankan:
sudo usermod -aG docker ubuntu
sudo systemctl restart docker
sudo chmod 666 /var/run/docker.sock
```

3. **Pastikan External Network Exists:**
```bash
# Check network manajemen_network
docker network ls | grep manajemen_network

# Jika belum ada, buat:
docker network create manajemen_network
```

### **Step 3: Clone dan Deploy**

1. **Clone Repository:**
```bash
# Navigate ke directory
cd /www/wwwroot/demo.enampuluhenam.web.id

# Clone project
git clone https://github.com/solusiitkreasi/ci4-api-jwt.git .

# Atau jika sudah ada, pull latest:
git pull origin master
```

2. **Setup Environment:**
```bash
# Copy environment file
cp .env.vps .env

# Verify environment
cat .env | head -20
```

3. **Deploy dengan Docker:**
```bash
# Make script executable
chmod +x scripts/deploy-vps.sh

# Run deployment
./scripts/deploy-vps.sh
```

### **Step 4: Verify Deployment**

1. **Check Container Status:**
```bash
docker-compose -f docker-compose.vps.yml ps
```

**Expected Output:**
```
       Name                     Command               State          Ports        
----------------------------------------------------------------------------------
ci4_api_jwt_app      docker-php-entrypoint php-fpm   Up      9000/tcp            
ci4_api_jwt_mysql    docker-entrypoint.sh mysqld     Up      3306/tcp, 33060/tcp
ci4_api_jwt_nginx    /docker-entrypoint.sh nginx ...  Up      0.0.0.0:8081->80/tcp
ci4_api_jwt_redis    docker-entrypoint.sh redis ...  Up      6379/tcp
```

2. **Test Local Access:**
```bash
# Test health endpoint
curl http://localhost:8081/health

# Test API endpoint
curl http://localhost:8081/api/v1/health
```

3. **Test Database dari phpMyAdmin Shared:**
   - Host: `ci4_api_jwt_mysql`
   - Database: `demo_db`
   - Username: `demo_user`
   - Password: `userpassword`

### **Step 5: Configure Cloudflare Tunnel**

Di Cloudflare Zero Trust Dashboard:

1. **Go to Access > Tunnels**
2. **Edit tunnel untuk demo.enampuluhenam.web.id:**
   - **Subdomain**: `demo`
   - **Domain**: `enampuluhenam.web.id`
   - **Service Type**: `HTTP`
   - **URL**: `localhost:8081`

3. **Test External Access:**
```bash
curl -I https://demo.enampuluhenam.web.id/health
```

---

## 🔍 Monitoring & Troubleshooting

### **View Logs:**
```bash
# All containers
docker-compose -f docker-compose.vps.yml logs -f

# Specific container
docker logs ci4_api_jwt_nginx -f
docker logs ci4_api_jwt_app -f
docker logs ci4_api_jwt_mysql -f
```

### **Restart Services:**
```bash
# Restart all
docker-compose -f docker-compose.vps.yml restart

# Restart specific service
docker-compose -f docker-compose.vps.yml restart nginx
```

### **Database Access via phpMyAdmin:**
Gunakan phpMyAdmin shared Anda dengan koneksi:
- **Server**: `ci4_api_jwt_mysql`
- **Database**: `demo_db`
- **Username**: `demo_user`
- **Password**: `userpassword`

---

## 📊 Expected Results

### ✅ Success Indicators:
- [ ] 4 containers running (nginx, app, mysql, redis)
- [ ] Port 8081 accessible locally
- [ ] Database accessible via shared phpMyAdmin
- [ ] External URL https://demo.enampuluhenam.web.id working
- [ ] API endpoints responding
- [ ] Memory usage < 1.5GB

### 🚨 Common Issues:

#### **Port 8081 in use:**
```bash
sudo fuser -k 8081/tcp
```

#### **Network not found:**
```bash
docker network create manajemen_network
```

#### **Database connection failed:**
```bash
docker logs ci4_api_jwt_mysql
# Check password dan database name
```

---

## 🎯 Quick Commands Cheat Sheet

```bash
# Start all services
docker-compose -f docker-compose.vps.yml up -d

# Stop all services
docker-compose -f docker-compose.vps.yml down

# View status
docker-compose -f docker-compose.vps.yml ps

# View logs
docker-compose -f docker-compose.vps.yml logs -f

# Restart specific service
docker-compose -f docker-compose.vps.yml restart app

# Access MySQL CLI
docker exec -it ci4_api_jwt_mysql mysql -u demo_user -p

# Access Redis CLI
docker exec -it ci4_api_jwt_redis redis-cli -a ci4redis2024

# Test local access
curl http://localhost:8081/health

# Test external access
curl https://demo.enampuluhenam.web.id/health
```

**🚀 Ready untuk deployment! Semua sudah disesuaikan dengan setup VPS Anda.**
