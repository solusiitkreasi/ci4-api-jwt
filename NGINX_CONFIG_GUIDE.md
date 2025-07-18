# Nginx Configuration Guide for CodeIgniter 4

## Key Differences: .htaccess vs nginx.conf

### Apache (.htaccess)
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php/$1 [QSA,L]
```

### Nginx (nginx.conf)
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

## Setup Steps untuk VPS

### 1. Copy nginx.conf ke VPS
```bash
# Copy nginx configuration
sudo cp /www/wwwroot/demo.enampuluhenam.web.id/nginx/default.conf /home/ubuntu/docker_project/demo_app/nginx/

# Set permissions
sudo chown ubuntu:ubuntu /home/ubuntu/docker_project/demo_app/nginx/default.conf
```

### 2. Update docker-compose.yml
Pastikan nginx volume mapping benar:
```yaml
demo_app_nginx:
  image: nginx:alpine
  volumes:
    - ./nginx/default.conf:/etc/nginx/conf.d/default.conf
    - ./src/public:/var/www/html/public
```

### 3. Restart Nginx Container
```bash
cd /home/ubuntu/docker_project/demo_app/
docker-compose restart demo_app_nginx
```

### 4. Verify Configuration
```bash
# Test nginx config
docker-compose exec demo_app_nginx nginx -t

# Check nginx logs
docker-compose logs demo_app_nginx
```

## Important Nginx Features for CI4

### 1. URL Rewriting
```nginx
# Handle CodeIgniter 4 routing
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### 2. API CORS Headers
```nginx
location ~ ^/api/ {
    add_header 'Access-Control-Allow-Origin' '*' always;
    add_header 'Access-Control-Allow-Methods' 'GET, POST, PUT, DELETE, OPTIONS' always;
}
```

### 3. Security Headers
```nginx
add_header X-Frame-Options "SAMEORIGIN" always;
add_header X-XSS-Protection "1; mode=block" always;
add_header X-Content-Type-Options "nosniff" always;
```

### 4. JWT Authorization Support
```nginx
location ~ \.php$ {
    fastcgi_param HTTP_AUTHORIZATION $http_authorization;
    fastcgi_pass_header Authorization;
}
```

### 5. Static File Caching
```nginx
location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

## Testing Configuration

### 1. Test Basic Access
```bash
curl -I http://localhost:8081
```

### 2. Test API Routing
```bash
curl http://localhost:8081/api/v1/
```

### 3. Test JWT Headers
```bash
curl -H "Authorization: Bearer your-token" http://localhost:8081/api/protected
```

### 4. Test Static Files
```bash
curl -I http://localhost:8081/assets/css/style.css
```

## Common Issues & Solutions

### Issue: 404 for API routes
**Solution:** Check try_files directive
```nginx
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Issue: CORS errors
**Solution:** Add proper CORS headers
```nginx
add_header 'Access-Control-Allow-Origin' '*' always;
```

### Issue: JWT not working
**Solution:** Pass Authorization header
```nginx
fastcgi_param HTTP_AUTHORIZATION $http_authorization;
```

### Issue: Static files not loading
**Solution:** Check static file location
```nginx
location ~* \.(css|js|png|jpg)$ {
    try_files $uri =404;
}
```

## Production Optimizations

### 1. Enable SSL
```nginx
listen 443 ssl http2;
ssl_certificate /etc/nginx/ssl/cert.pem;
ssl_certificate_key /etc/nginx/ssl/key.pem;
```

### 2. Enable Gzip Compression
```nginx
gzip on;
gzip_types text/plain text/css application/javascript;
```

### 3. Browser Caching
```nginx
location ~* \.(js|css|png|jpg)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

### 4. Rate Limiting
```nginx
limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
location /api/ {
    limit_req zone=api burst=20 nodelay;
}
```
