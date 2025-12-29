# Sunbites POS - AWS Deployment Guide

## Architecture Overview

This deployment uses AWS Free Tier resources:
- **EC2 t2.micro**: Application server (750 hrs/month free for 12 months)
- **RDS MySQL db.t2.micro**: Database (750 hrs/month free for 12 months)
- **Elastic IP**: Static IP address (free when attached to running instance)

## Prerequisites

1. AWS CLI installed and configured with profile `jhersonn-sunbite-admin`
2. SSH client available
3. Node.js and PHP installed locally (for building assets)

## Initial Setup (One-Time)

### Step 1: Provision AWS Infrastructure

```bash
cd /home/jhersonn/sunbites-pos
chmod +x deploy/scripts/*.sh
./deploy/scripts/01-setup-aws-infrastructure.sh
```

This creates:
- EC2 instance with Ubuntu 22.04
- RDS MySQL database
- Security groups
- SSH key pair (saved to `~/.ssh/sunbites-pos-key.pem`)
- Elastic IP

**Important**: Save the outputs displayed at the end!

### Step 2: First Deployment

```bash
./deploy/scripts/03-first-deploy.sh
```

This will:
- Install PHP, Nginx, Node.js on the server
- Deploy your application
- Set up the database
- Configure everything for production

## GitHub Actions Setup

### Required Secrets

Go to your GitHub repository → Settings → Secrets and variables → Actions

Add these secrets:

| Secret Name | Value |
|-------------|-------|
| `EC2_HOST` | Your EC2 Elastic IP (e.g., `54.123.45.67`) |
| `EC2_SSH_PRIVATE_KEY` | Contents of `~/.ssh/sunbites-pos-key.pem` |
| `LARAVEL_ENV_ENCRYPTION_KEY` | Your Laravel env encryption key |

To get the SSH key content:
```bash
cat ~/.ssh/sunbites-pos-key.pem
```

To get your Laravel encryption key (if you don't have it saved):
```bash
# The key was shown when you first encrypted the file
# If lost, decrypt with your current key and re-encrypt to generate a new one
php artisan env:decrypt --env=production
php artisan env:encrypt --env=production --force
# Save the new key that is displayed!
```

### Manual Deployment

1. Go to your GitHub repository
2. Click **Actions** tab
3. Select **Deploy to AWS EC2** workflow
4. Click **Run workflow**
5. Choose options:
   - Environment: `production`
   - Run migrations: `true` (if you have new migrations)
   - Run seeders: `false` (only for fresh installs)
6. Click **Run workflow**

## Environment Variables

This project uses Laravel's encrypted environment files (`.env.production.encrypted`).

### How It Works
1. `.env.production.encrypted` is committed to the repository (safe)
2. The encryption key is stored in GitHub Actions secrets
3. During deployment, the file is decrypted and deployed as `.env`

### Updating Environment Variables

```bash
# 1. Decrypt the current .env.production
php artisan env:decrypt --env=production

# 2. Edit .env.production
nano .env.production

# 3. Re-encrypt with your key
php artisan env:encrypt --env=production --force

# 4. Commit the updated .env.production.encrypted
git add .env.production.encrypted
git commit -m "Update production environment"
git push

# 5. Trigger a new deployment via GitHub Actions
```

### Emergency Server Edit
If you need to edit directly on the server:
```bash
ssh -i ~/.ssh/sunbites-pos-key.pem ubuntu@YOUR_EC2_IP
nano /var/www/sunbites-pos/.env
cd /var/www/sunbites-pos
php artisan config:cache
sudo systemctl reload php8.3-fpm
```
**Note**: Server edits will be overwritten on next deployment.

## Useful Commands

### SSH into Server
```bash
ssh -i ~/.ssh/sunbites-pos-key.pem ubuntu@YOUR_EC2_IP
```

### View Application Logs
```bash
ssh -i ~/.ssh/sunbites-pos-key.pem ubuntu@YOUR_EC2_IP
tail -f /var/www/sunbites-pos/storage/logs/laravel.log
```

### Restart Services
```bash
sudo systemctl restart php8.3-fpm
sudo systemctl restart nginx
sudo supervisorctl restart sunbites-worker:*
```

### Run Artisan Commands
```bash
cd /var/www/sunbites-pos
php artisan migrate --force
php artisan cache:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Setting Up HTTPS (Free with Let's Encrypt)

After pointing your domain to the EC2 IP:
```bash
ssh -i ~/.ssh/sunbites-pos-key.pem ubuntu@YOUR_EC2_IP
sudo certbot --nginx -d yourdomain.com
```

Then update `.env`:
```
APP_URL=https://yourdomain.com
```

## Cost Breakdown (Free Tier)

| Resource | Free Tier Limit | Expected Usage |
|----------|-----------------|----------------|
| EC2 t2.micro | 750 hrs/month | ~720 hrs/month |
| RDS db.t2.micro | 750 hrs/month | ~720 hrs/month |
| Elastic IP | Free when attached | Always attached |
| Data Transfer | 15 GB/month out | Varies |
| EBS Storage | 30 GB | 20 GB used |

**Note**: Free tier is valid for 12 months from AWS account creation.

## Rollback Procedure

Backups are automatically created during deployment at `/var/www/backups/`.

To rollback:
```bash
ssh -i ~/.ssh/sunbites-pos-key.pem ubuntu@YOUR_EC2_IP
cd /var/www
# List backups
ls -la backups/
# Restore a backup
sudo rm -rf sunbites-pos/*
sudo tar -xzf backups/backup_TIMESTAMP.tar.gz -C sunbites-pos/
cd sunbites-pos
php artisan config:cache
sudo systemctl reload php8.3-fpm
```

## Troubleshooting

### 502 Bad Gateway
```bash
sudo systemctl status php8.3-fpm
sudo tail -f /var/log/nginx/error.log
```

### Permission Issues
```bash
cd /var/www/sunbites-pos
sudo chown -R ubuntu:www-data .
sudo chmod -R 775 storage bootstrap/cache
```

### Database Connection Issues
```bash
# Test connection from EC2
mysql -h YOUR_RDS_ENDPOINT -u sunbites_admin -p
```

### Queue Workers Not Running
```bash
sudo supervisorctl status
sudo supervisorctl restart sunbites-worker:*
```
