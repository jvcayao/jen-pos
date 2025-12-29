#!/bin/bash
set -e

# ============================================================
# First Deployment Script
# Run this after infrastructure is set up to do initial deploy
# ============================================================

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Check if aws-outputs.txt exists, otherwise prompt for values
if [ -f "deploy/aws-outputs.txt" ]; then
    source deploy/aws-outputs.txt
else
    echo -e "${YELLOW}No aws-outputs.txt found. Please enter your AWS details:${NC}"
    echo ""

    read -p "EC2 Public IP: " EC2_PUBLIC_IP
    read -p "SSH Key Path (e.g., ~/.ssh/my-key.pem): " SSH_KEY_PATH
    read -p "RDS Endpoint (e.g., mydb.xxx.rds.amazonaws.com): " RDS_ENDPOINT
    read -p "RDS Database Name: " RDS_DATABASE
    read -p "RDS Username: " RDS_USERNAME
    read -s -p "RDS Password: " RDS_PASSWORD
    echo ""

    # Save for future use
    cat > deploy/aws-outputs.txt << OUTPUTS
EC2_PUBLIC_IP=$EC2_PUBLIC_IP
SSH_KEY_PATH=$SSH_KEY_PATH
RDS_ENDPOINT=$RDS_ENDPOINT
RDS_DATABASE=$RDS_DATABASE
RDS_USERNAME=$RDS_USERNAME
RDS_PASSWORD=$RDS_PASSWORD
OUTPUTS

    echo -e "${GREEN}Credentials saved to deploy/aws-outputs.txt${NC}"
fi

echo -e "${GREEN}========================================${NC}"
echo -e "${GREEN}First Deployment to EC2${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "Target: ${YELLOW}$EC2_PUBLIC_IP${NC}"
echo ""

# Check SSH key
if [ ! -f "$SSH_KEY_PATH" ]; then
    SSH_KEY_PATH="${SSH_KEY_PATH/#\~/$HOME}"
fi

if [ ! -f "$SSH_KEY_PATH" ]; then
    echo -e "${RED}Error: SSH key not found at $SSH_KEY_PATH${NC}"
    exit 1
fi

# Step 1: Copy server setup script and run it
echo -e "\n${YELLOW}[1/5] Setting up server...${NC}"
scp -i "$SSH_KEY_PATH" -o StrictHostKeyChecking=no deploy/scripts/02-setup-server.sh ubuntu@$EC2_PUBLIC_IP:/tmp/
ssh -i "$SSH_KEY_PATH" ubuntu@$EC2_PUBLIC_IP "chmod +x /tmp/02-setup-server.sh && /tmp/02-setup-server.sh"

# Step 2: Decrypt and deploy .env.production
echo -e "\n${YELLOW}[2/5] Setting up production environment file...${NC}"

if [ ! -f ".env.production.encrypted" ]; then
    echo -e "${RED}Error: .env.production.encrypted not found${NC}"
    echo "Please create it with: php artisan env:encrypt --env=production"
    exit 1
fi

# Prompt for encryption key if not set
if [ -z "$LARAVEL_ENV_ENCRYPTION_KEY" ]; then
    read -s -p "Enter LARAVEL_ENV_ENCRYPTION_KEY: " LARAVEL_ENV_ENCRYPTION_KEY
    echo ""
fi

# Decrypt the environment file
echo "Decrypting .env.production.encrypted..."
php artisan env:decrypt --env=production --key="$LARAVEL_ENV_ENCRYPTION_KEY" --force

# Copy decrypted file to server as .env
scp -i "$SSH_KEY_PATH" .env.production ubuntu@$EC2_PUBLIC_IP:/var/www/sunbites-pos/.env

# Remove local decrypted file (keep only encrypted version)
rm -f .env.production

echo -e "${GREEN}Environment file deployed successfully${NC}"

# Step 3: Build and deploy application
echo -e "\n${YELLOW}[3/5] Building application locally...${NC}"
composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build

# Step 4: Deploy application
echo -e "\n${YELLOW}[4/5] Deploying application...${NC}"
rsync -avz --progress \
    --exclude='.git' \
    --exclude='.github' \
    --exclude='node_modules' \
    --exclude='tests' \
    --exclude='.env' \
    --exclude='.env.example' \
    --exclude='deploy' \
    --exclude='*.md' \
    --exclude='phpunit.xml' \
    --exclude='docker-compose*.yml' \
    -e "ssh -i $SSH_KEY_PATH" \
    ./ ubuntu@$EC2_PUBLIC_IP:/var/www/sunbites-pos/

# Step 5: Finalize deployment
echo -e "\n${YELLOW}[5/5] Finalizing deployment...${NC}"
ssh -i "$SSH_KEY_PATH" ubuntu@$EC2_PUBLIC_IP << 'FINALIZE'
    cd /var/www/sunbites-pos

    # Set permissions
    sudo chown -R ubuntu:www-data .
    sudo chmod -R 755 .
    sudo chmod -R 775 storage bootstrap/cache

    # Install dependencies
    composer install --no-dev --optimize-autoloader --no-interaction

    # Laravel setup
    php artisan storage:link
    php artisan migrate --force
    php artisan db:seed --force
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    php artisan event:cache

    # Restart services
    sudo systemctl restart php8.3-fpm
    sudo supervisorctl reread
    sudo supervisorctl update
    sudo supervisorctl start sunbites-worker:* || true

    echo "Deployment finalized!"
FINALIZE

echo -e "\n${GREEN}========================================${NC}"
echo -e "${GREEN}First Deployment Complete!${NC}"
echo -e "${GREEN}========================================${NC}"
echo -e "Your application is now live at:"
echo -e "${YELLOW}http://$EC2_PUBLIC_IP${NC}"
echo -e "\n${YELLOW}Next Steps:${NC}"
echo -e "1. Set up a domain name pointing to $EC2_PUBLIC_IP"
echo -e "2. Enable HTTPS with: sudo certbot --nginx -d yourdomain.com"
echo -e "3. Set up GitHub Actions secrets for automated deployments"
