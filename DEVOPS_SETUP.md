# DevOps CI/CD Setup Guide

## 🎯 Arsitektur Deployment

```
┌─────────────────┐    ┌──────────────────┐    ┌─────────────────────────┐
│   PC Lokal      │    │    GitHub        │    │      VPS Server         │
│                 │    │   Repository     │    │                         │
│ ✅ Development  │    │ ✅ Source Code   │    │ ✅ Docker Containers    │
│ ✅ Git Push     │────┤ ✅ CI/CD Trigger │────┤ ✅ Staging (Port 8080)  │
│ ❌ No Docker    │    │ ✅ Webhooks      │    │ ✅ Production (Port 80) │
│                 │    │                  │    │ ✅ Auto Deployment      │
└─────────────────┘    └──────────────────┘    └─────────────────────────┘
```

## 🚀 Setup Process

### 1. Setup VPS Server (Jalankan Sekali)

```bash
# Di VPS Server, jalankan:
curl -sSL https://raw.githubusercontent.com/solusiitkreasi/ci4-api-jwt/master/scripts/setup-vps.sh | bash
```

### 2. Setup GitHub Secrets

Di GitHub repository, tambahkan secrets berikut:
- `VPS_HOST`: IP address VPS server
- `VPS_USERNAME`: Username SSH (biasanya 'root' atau 'deployer')  
- `VPS_SSH_KEY`: Private SSH key untuk akses VPS

### 3. Clone Repository di VPS

```bash
# SSH ke VPS server
ssh root@your-vps-ip

# Clone ke staging
cd /var/www/staging
git clone https://github.com/solusiitkreasi/ci4-api-jwt.git

# Clone ke production  
cd /var/www/production
git clone https://github.com/solusiitkreasi/ci4-api-jwt.git
```

### 4. Configure Environment Files

```bash
# Di setiap environment, copy dan edit .env
cd /var/www/staging/ci4-api-jwt
cp .env.staging .env
# Edit sesuai kebutuhan staging

cd /var/www/production/ci4-api-jwt  
cp .env.production .env
# Edit sesuai kebutuhan production
```

## 📋 Deployment Workflow

### Automatic Deployment (Recommended)

1. **Push ke branch `develop`** → Auto deploy ke **staging**
2. **Push ke branch `master`** → Auto deploy ke **production**

### Manual Deployment

```bash
# Deploy ke staging
./scripts/deploy.sh staging

# Deploy ke production  
./scripts/deploy.sh production

# Deploy ke keduanya
./scripts/deploy.sh both
```

## 🔧 Development Workflow

### Di PC Lokal:

```bash
# 1. Development
# Edit code menggunakan XAMPP atau native PHP

# 2. Testing
composer test

# 3. Commit & Push
git add .
git commit -m "Add new feature"
git push origin develop  # Auto deploy ke staging

# 4. Merge ke master untuk production
git checkout master
git merge develop  
git push origin master   # Auto deploy ke production
```

## 🌐 Akses Applications

- **Staging**: `http://your-vps-ip:8080`
- **Production**: `http://your-vps-ip:80`

## 📊 Monitoring

### Container Status
```bash
# Cek container yang running
docker ps

# Cek logs staging
docker-compose -f docker-compose.staging.yml logs -f

# Cek logs production  
docker-compose -f docker-compose.production.yml logs -f
```

### Database Access
```bash
# Access staging database
docker exec -it mysql-staging mysql -u root -p

# Access production database
docker exec -it mysql-production mysql -u root -p
```

## 🛠️ Troubleshooting

### Reset Environment
```bash
# Reset staging
cd /var/www/staging/ci4-api-jwt
docker-compose -f docker-compose.staging.yml down -v
docker-compose -f docker-compose.staging.yml up -d --build

# Reset production  
cd /var/www/production/ci4-api-jwt
docker-compose -f docker-compose.production.yml down -v
docker-compose -f docker-compose.production.yml up -d --build
```

### Rollback Deployment
```bash
# Rollback ke commit sebelumnya
git reset --hard HEAD~1
./scripts/deploy.sh production
```

## 🔒 Security Best Practices

1. **Environment Variables**: Jangan commit file `.env` yang berisi credentials
2. **SSH Keys**: Gunakan SSH keys yang berbeda untuk setiap environment  
3. **Database**: Password yang kuat dan berbeda per environment
4. **SSL**: Setup Let's Encrypt untuk HTTPS di production
5. **Firewall**: Hanya buka port yang diperlukan

## 📈 Next Steps

1. **SSL Certificate**: Setup Let's Encrypt
2. **Domain**: Point domain ke VPS
3. **Monitoring**: Add Prometheus + Grafana
4. **Backup**: Automated database backup
5. **CDN**: Setup Cloudflare
6. **Load Balancer**: Jika scale horizontal

## 💡 Tips

- **Staging Environment**: Gunakan untuk testing sebelum production
- **Database Migration**: Selalu test di staging dulu
- **Code Review**: Gunakan Pull Request untuk kontrol kualitas
- **Backup**: Database production di-backup otomatis sebelum deploy
- **Logs**: Monitor logs untuk debugging issues
