# Oracle Cloud Ubuntu VPS deployment

This deploys the PHP API on an Oracle Cloud Ubuntu VPS with Nginx, PHP-FPM, and MySQL on the same server.

## 1. Server packages

SSH into the VPS and install the runtime:

```bash
sudo apt update
sudo apt install -y nginx mysql-server php-fpm php-cli php-mysql php-curl unzip git certbot python3-certbot-nginx
```

Check the PHP-FPM socket name:

```bash
ls /run/php/
```

If it is not `php8.3-fpm.sock`, update `deploy/oracle/nginx-beyond66-api.conf`.

## 2. Clone project

```bash
sudo mkdir -p /var/www
sudo chown -R $USER:www-data /var/www
git clone https://github.com/Elleyxx/Beyond66.git /var/www/beyond66
cd /var/www/beyond66/backend
```

Create the backend env file:

```bash
cp deploy/oracle/env.example .env
nano .env
```

Use a strong MySQL password and your Gemini key.

## 3. MySQL database

Create the database and user:

```bash
sudo mysql
```

```sql
CREATE DATABASE beyond66 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'beyond66_user'@'localhost' IDENTIFIED BY 'replace_with_strong_password';
GRANT ALL PRIVILEGES ON beyond66.* TO 'beyond66_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

Initialize tables:

```bash
php scripts/setup_beyond66_db.php
```

If you have an exported SQL file instead:

```bash
mysql -u beyond66_user -p beyond66 < config/beyond66.sql
```

## 4. Nginx

Copy the Nginx config:

```bash
sudo cp deploy/oracle/nginx-beyond66-api.conf /etc/nginx/sites-available/beyond66-api
sudo ln -s /etc/nginx/sites-available/beyond66-api /etc/nginx/sites-enabled/beyond66-api
sudo nginx -t
sudo systemctl reload nginx
```

Point your DNS record, for example `api.yourdomain.com`, to the Oracle VPS public IP.

## 5. HTTPS

After DNS is pointing to the VPS:

```bash
sudo certbot --nginx -d api.yourdomain.com
```

## 6. Verify backend

Open:

```text
https://api.yourdomain.com/api/health
```

Expected:

```json
{"success":true,"status":"ok"}
```

## 7. Vercel frontend

Set Vercel environment variables:

```env
VITE_API_BASE=https://api.yourdomain.com
VITE_AI_API_BASE=https://api.yourdomain.com
```

Redeploy the Vercel frontend.

## 8. Updating backend later

```bash
cd /var/www/beyond66
git pull
sudo systemctl reload nginx
```
