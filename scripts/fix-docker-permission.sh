#!/bin/bash

# Docker Permission Fix untuk Ubuntu User
echo "🔧 Fixing Docker permissions for ubuntu user..."

# Add ubuntu user to docker group (if not already)
sudo usermod -aG docker ubuntu

# Apply new group membership immediately
newgrp docker

# Test docker access
echo "🧪 Testing docker access..."
docker ps

if [ $? -eq 0 ]; then
    echo "✅ Docker permission fixed successfully!"
else
    echo "⚠️  Need to logout and login again, or run: sudo chmod 666 /var/run/docker.sock"
fi
