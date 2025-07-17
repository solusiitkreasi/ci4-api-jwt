# 🔐 GitHub Actions Secrets Setup Guide

## 📋 Required Secrets untuk GitHub Actions

Untuk mengamankan deployment, Anda perlu menambahkan secrets di GitHub repository:

### **1. Go to GitHub Repository Settings:**
1. Buka repository: `https://github.com/solusiitkreasi/ci4-api-jwt`
2. Go to **Settings** → **Secrets and variables** → **Actions**
3. Click **New repository secret**

### **2. Add These Secrets:**

#### **VPS Connection Secrets:**
```
Secret Name: VPS_HOST
Value: your-vps-ip-address
```

```
Secret Name: VPS_USERNAME
Value: ubuntu
```

```
Secret Name: VPS_SSH_KEY
Value: -----BEGIN OPENSSH PRIVATE KEY-----
(paste your private SSH key here)
-----END OPENSSH PRIVATE KEY-----
```

```
Secret Name: VPS_SSH_PORT
Value: 22
```

#### **Database Secrets:**
```
Secret Name: DB_PASSWORD
Value: userpassword
```

```
Secret Name: MYSQL_ROOT_PASSWORD
Value: rootpassword
```

```
Secret Name: REDIS_PASSWORD
Value: ci4redis2024
```

#### **Application Secrets:**
```
Secret Name: JWT_SECRET
Value: ci4_jwt_secret_vps_demo_2024_secure_random_key_here
```

```
Secret Name: ENCRYPTION_KEY
Value: hex2bin:32_character_encryption_key_here_random
```

#### **Domain Configuration:**
```
Secret Name: APP_URL
Value: https://demo.enampuluhenam.web.id
```

---

## 🔑 **Generate SSH Key untuk GitHub Actions**

### **Step 1: Generate SSH Key Pair di local PC:**
```bash
# Generate SSH key khusus untuk GitHub Actions
ssh-keygen -t rsa -b 4096 -C "github-actions@demo.enampuluhenam.web.id" -f ~/.ssh/id_rsa_github_actions

# Akan membuat 2 file:
# ~/.ssh/id_rsa_github_actions (private key) → untuk GitHub Secrets
# ~/.ssh/id_rsa_github_actions.pub (public key) → untuk VPS
```

### **Step 2: Copy Public Key ke VPS:**
```bash
# Copy public key ke VPS
ssh-copy-id -i ~/.ssh/id_rsa_github_actions.pub ubuntu@your-vps-ip

# Atau manual:
cat ~/.ssh/id_rsa_github_actions.pub
# Copy output, lalu di VPS:
# echo "ssh-rsa AAAAB3NzaC1yc2E... github-actions@demo.enampuluhenam.web.id" >> ~/.ssh/authorized_keys
```

### **Step 3: Test SSH Connection:**
```bash
# Test dari local PC
ssh -i ~/.ssh/id_rsa_github_actions ubuntu@your-vps-ip

# Jika berhasil connect, copy private key untuk GitHub Secrets:
cat ~/.ssh/id_rsa_github_actions
```

---

## 🚀 **LANGKAH-LANGKAH SETUP LENGKAP**

### **Step 1: Generate SSH Key untuk GitHub Actions**

Di PC lokal Anda, jalankan:

```bash
# Generate SSH key pair khusus untuk GitHub Actions
ssh-keygen -t rsa -b 4096 -C "github-actions@demo.enampuluhenam.web.id" -f ~/.ssh/id_rsa_github_actions

# Akan menghasilkan:
# ~/.ssh/id_rsa_github_actions (private key)
# ~/.ssh/id_rsa_github_actions.pub (public key)
```

### **Step 2: Copy Public Key ke VPS**

```bash
# Method 1: Otomatis
ssh-copy-id -i ~/.ssh/id_rsa_github_actions.pub ubuntu@your-vps-ip

# Method 2: Manual
# Copy public key
cat ~/.ssh/id_rsa_github_actions.pub

# SSH ke VPS dan tambahkan ke authorized_keys
ssh ubuntu@your-vps-ip
echo "ssh-rsa AAAAB3NzaC1yc2E... (paste public key here)" >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
```

### **Step 3: Test SSH Connection**

```bash
# Test connection dengan private key
ssh -i ~/.ssh/id_rsa_github_actions ubuntu@your-vps-ip

# Jika berhasil, lanjut ke step berikutnya
```

### **Step 4: Setup GitHub Secrets**

1. **Buka GitHub Repository:** https://github.com/solusiitkreasi/ci4-api-jwt
2. **Go to Settings → Secrets and variables → Actions**
3. **Add secrets satu per satu:**

#### **🔑 VPS Connection Secrets:**

| Secret Name | Value | Description |
|-------------|-------|-------------|
| `VPS_HOST` | `your-vps-ip-address` | IP address VPS Anda |
| `VPS_USERNAME` | `ubuntu` | Username SSH |
| `VPS_SSH_KEY` | `(isi dengan private key)` | Copy dari `~/.ssh/id_rsa_github_actions` |
| `VPS_SSH_PORT` | `22` | SSH port (default 22) |

#### **🗄️ Database Secrets:**

| Secret Name | Value | Description |
|-------------|-------|-------------|
| `DB_PASSWORD` | `userpassword` | Sesuai dengan database Anda |
| `MYSQL_ROOT_PASSWORD` | `rootpassword` | Root password MySQL |
| `REDIS_PASSWORD` | `ci4redis2024` | Password Redis |

#### **🔐 Application Secrets:**

| Secret Name | Value | Description |
|-------------|-------|-------------|
| `JWT_SECRET` | `ci4_jwt_secret_vps_demo_2024_secure_random_key_here` | Random string untuk JWT |
| `ENCRYPTION_KEY` | `hex2bin:32_character_encryption_key_here_random` | 32 karakter encryption key |
| `APP_URL` | `https://demo.enampuluhenam.web.id` | Domain aplikasi |

### **Step 5: Copy Private Key ke GitHub Secrets**

```bash
# Copy seluruh isi private key (termasuk header dan footer)
cat ~/.ssh/id_rsa_github_actions

# Output akan seperti:
# -----BEGIN OPENSSH PRIVATE KEY-----
# b3BlbnNzaC1rZXktdjEAAAAABG5vbmUAAAAEbm9uZQAAAAAAAAABAAACFwAAAAdzc2gtcn
# ... (banyak baris) ...
# -----END OPENSSH PRIVATE KEY-----
```

**Copy seluruh output ini ke GitHub Secret `VPS_SSH_KEY`**

### **Step 6: Generate Secure Keys**

```bash
# Generate JWT Secret (random 64 karakter)
openssl rand -hex 32

# Generate Encryption Key (32 karakter untuk hex2bin)
openssl rand -hex 16
```

---

## 🎯 **ALTERNATIVE: Manual Deployment (Tanpa GitHub Actions)**

Jika Anda tidak ingin menggunakan GitHub Actions, ikuti langkah manual:

### **1. Push ke GitHub (tanpa secrets):**
```bash
git add .
git commit -m "Add secure VPS deployment configuration"
git push origin master
```

### **2. Manual Deploy di VPS:**
```bash
# SSH ke VPS
ssh ubuntu@your-vps-ip

# Clone repository
cd /www/wwwroot/demo.enampuluhenam.web.id
git clone https://github.com/solusiitkreasi/ci4-api-jwt.git .

# Create environment file dengan nilai sebenarnya
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

---

## ⚡ **QUICK START RECOMMENDATION**

**Untuk pemula, saya rekomendasikan manual deployment dulu:**

1. ✅ Push code ke GitHub (tanpa credential)
2. ✅ SSH ke VPS dan clone repository
3. ✅ Manual create `.env` dengan nilai sebenarnya
4. ✅ Jalankan deployment script
5. ✅ Setelah berhasil, baru setup GitHub Actions (optional)

**Apakah Anda ingin mulai dengan manual deployment atau setup GitHub Actions lengkap?**
