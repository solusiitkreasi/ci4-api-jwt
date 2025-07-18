# 🔧 Fix SSH Connection untuk VPS dengan Cloudflare Tunnel

## 🚨 Problem: SSH Connection Timeout

Ketika VPS menggunakan Cloudflare Tunnel, SSH port 22 mungkin tidak exposed ke internet.

## 🔧 Solution 1: Configure SSH via Cloudflare Tunnel

### **Step 1: Update Cloudflare Tunnel Config**

Di dashboard Cloudflare Zero Trust:

1. **Go to Access → Tunnels**
2. **Edit tunnel untuk demo.enampuluhenam.web.id**
3. **Add SSH service:**
   - **Service Type**: SSH
   - **Subdomain**: ssh
   - **Domain**: enampuluhenam.web.id  
   - **URL**: localhost:22

4. **Result**: `ssh.enampuluhenam.web.id` → VPS SSH

### **Step 2: Test SSH via Tunnel**
```bash
# Test SSH via Cloudflare tunnel
ssh ubuntu@ssh.enampuluhenam.web.id
```

---

## 🔧 Solution 2: Temporary Enable Direct SSH

### **Step 1: Check VPS Provider Firewall**

#### **For DigitalOcean:**
1. Go to Networking → Firewalls
2. Add rule: SSH (port 22) from All IPv4

#### **For Vultr:**
1. Go to Firewall → Add Rule
2. Protocol: TCP, Port: 22, Source: 0.0.0.0/0

#### **For AWS EC2:**
1. Go to Security Groups
2. Add Inbound Rule: SSH (22) from 0.0.0.0/0

### **Step 2: Check aaPanel Firewall**

SSH ke VPS via console provider, lalu:

```bash
# Check firewall status
sudo ufw status

# Enable SSH if blocked
sudo ufw allow ssh
sudo ufw allow 22

# Check iptables
sudo iptables -L -n | grep 22
```

---

## 🔧 Solution 3: Alternative Deployment (Manual)

Jika SSH tetap tidak bisa dari luar:

### **Option A: Deploy via VPS Console**

1. **Access VPS via provider console/terminal**
2. **Manual deployment:**

```bash
# Clone repository
cd /www/wwwroot/demo.enampuluhenam.web.id
git clone https://github.com/solusiitkreasi/ci4-api-jwt.git .

# Setup environment
cat > .env << EOF
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=demo_db
DB_USERNAME=demo_user
DB_PASSWORD=userpassword
MYSQL_ROOT_PASSWORD=rootpassword

REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=ci4redis2024

APP_URL=https://demo.enampuluhenam.web.id
CI_ENVIRONMENT=production
APP_FORCE_GLOBAL_SECURE_REQUESTS=true

JWT_SECRET=ci4_jwt_secret_vps_demo_2024_secure_key
JWT_ALGORITHM=HS256
JWT_EXPIRED=3600
ENCRYPTION_KEY=hex2bin:ci4_encryption_vps_demo_32_chars_key

CORS_ALLOWED_ORIGINS=https://demo.enampuluhenam.web.id,https://enampuluhenam.web.id
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization,X-Requested-With

LOG_LEVEL=error
LOG_HANDLERS=file

RATE_LIMIT_ENABLED=true
RATE_LIMIT_REQUESTS=100
RATE_LIMIT_WINDOW=3600

CLOUDFLARE_TUNNEL=true
CLOUDFLARE_DOMAIN=demo.enampuluhenam.web.id
CLOUDFLARE_PORT=8081
EOF

# Deploy
chmod +x scripts/deploy-vps.sh
./scripts/deploy-vps.sh
```

### **Option B: Change SSH Port**

Di VPS console:

```bash
# Edit SSH config
sudo nano /etc/ssh/sshd_config

# Change Port 22 to Port 2222
Port 2222

# Restart SSH
sudo systemctl restart ssh

# Update firewall
sudo ufw allow 2222
```

Lalu dari PC lokal:
```bash
ssh -p 2222 ubuntu@182.253.54.149
```

---

## 🎯 **Recommended Actions**

1. **Check VPS provider console** - apakah ada firewall rules
2. **Try SSH via Cloudflare tunnel** (if configured)
3. **Use VPS console** untuk manual deployment
4. **Contact VPS support** untuk enable SSH access

---

## 🚀 **Quick Manual Deployment**

Karena SSH bermasalah, saya rekomendasikan:

1. **Access VPS via provider console/dashboard**
2. **Manual clone dan deploy** dengan script yang sudah saya siapkan
3. **Skip GitHub Actions** untuk sementara
4. **Fix SSH nanti** setelah aplikasi running

**Apakah Anda bisa akses VPS console dari provider dashboard? Mari lanjut manual deployment! 🎯**
