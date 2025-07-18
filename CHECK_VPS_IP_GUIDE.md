# 🔍 Cara Mendapatkan IP VPS dengan Cloudflare Tunnel

## 📋 Method 1: Dashboard VPS Provider

### **DigitalOcean:**
1. Login ke DigitalOcean Dashboard
2. Go to **Droplets**
3. Lihat IP address di kolom "ipv4"

### **Vultr:**
1. Login ke Vultr Dashboard  
2. Go to **Products → Instances**
3. Lihat "Main IP" di instance list

### **Linode:**
1. Login ke Linode Cloud Manager
2. Go to **Linodes**
3. Lihat IP address di kolom "IP Address"

### **AWS EC2:**
1. Login ke AWS Console
2. Go to **EC2 → Instances**
3. Lihat "Public IPv4 address"

### **Google Cloud:**
1. Login ke Google Cloud Console
2. Go to **Compute Engine → VM instances**
3. Lihat "External IP"

---

## 📋 Method 2: Command dari dalam VPS (SSH)

Jika Anda sudah bisa SSH ke VPS:

```bash
# Cek IP public dari dalam VPS
curl ifconfig.me
# atau
curl ipinfo.io/ip
# atau
curl icanhazip.com

# Cek IP internal/private
hostname -I
# atau
ip addr show
```

---

## 📋 Method 3: Cek dari Cloudflare Tunnel Dashboard

1. **Login ke Cloudflare Zero Trust:**
   - https://one.dash.cloudflare.com/

2. **Go to Access → Tunnels**

3. **Click tunnel Anda untuk demo.enampuluhenam.web.id**

4. **Lihat di "Connector" atau "Origin Server"**
   - Mungkin ada info IP server yang terhubung

---

## 📋 Method 4: Cek dari aaPanel Dashboard

Jika Anda punya akses aaPanel:

1. **Login ke aaPanel**
2. **Dashboard utama** biasanya menampilkan server IP
3. **System Info** atau **Server Status**

---

## 🔧 Alternative: SSH Tanpa IP Langsung

### **Option A: SSH via Domain (jika tunnel mendukung SSH)**

Beberapa setup Cloudflare Tunnel bisa forward SSH:

```bash
# Test apakah SSH bisa via domain
ssh ubuntu@demo.enampuluhenam.web.id

# Jika tidak bisa, berarti tunnel hanya untuk HTTP/HTTPS
```

### **Option B: Temporary IP Exposure**

Jika perlu setup SSH untuk GitHub Actions:

1. **Temporary disable Cloudflare proxy:**
   - Go to Cloudflare DNS settings
   - Turn off "Proxy" (orange cloud) untuk A record
   - Ini akan expose real IP sementara

2. **Setup SSH access**

3. **Re-enable Cloudflare proxy** setelah setup selesai

---

## 🎯 **Recommended Approach untuk GitHub Actions**

### **Method 1: Domain-based SSH (Best)**

Update GitHub Actions workflow untuk menggunakan domain:

```yaml
# Di .github/workflows/deploy-vps.yml
# Ganti VPS_HOST dengan domain
VPS_HOST: demo.enampuluhenam.web.id  # instead of IP
```

### **Method 2: Internal Network Access**

Jika VPS punya IP internal yang bisa diakses:

```bash
# Dari dalam server lain di network yang sama
ssh ubuntu@internal-ip-address
```

---

## 📝 **Quick Check Commands**

Jalankan dari PC lokal untuk test koneksi:

```bash
# Test 1: Ping domain
ping demo.enampuluhenam.web.id

# Test 2: Test SSH via domain  
ssh ubuntu@demo.enampuluhenam.web.id

# Test 3: Nslookup untuk cek IP
nslookup demo.enampuluhenam.web.id

# Test 4: Dig untuk detail DNS
dig demo.enampuluhenam.web.id
```

---

## 🚀 **Next Steps**

1. **Cek dashboard VPS provider** untuk dapatkan IP
2. **Atau test SSH via domain** jika tunnel mendukung
3. **Setup GitHub Secrets** dengan IP atau domain
4. **Continue dengan deployment**

**Bisa share VPS provider mana yang Anda gunakan? Saya bisa kasih petunjuk lebih spesifik! 🎯**
