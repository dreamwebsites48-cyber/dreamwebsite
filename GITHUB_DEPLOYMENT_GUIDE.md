# 📚 GitHub & Deployment Guide - DreamWebsites

## 🎯 Quick Start: Upload to GitHub

### Step 1: Create GitHub Repository

1. Go to [github.com/new](https://github.com/new)
2. Name: `dreamwebsites`
3. Description: `Premium website marketplace platform with PHP, MySQL, Bootstrap`
4. Choose Public (for portfolio) or Private
5. Click "Create repository"

### Step 2: Clone & Setup Locally

```bash
# Clone the repository
git clone https://github.com/YOUR_USERNAME/dreamwebsites.git
cd dreamwebsites

# Initialize git if starting fresh
git init

# Add all files
git add .

# Commit
git commit -m "Initial commit: DreamWebsites platform v2.0"

# Push to GitHub (first time)
git remote add origin https://github.com/YOUR_USERNAME/dreamwebsites.git
git branch -M main
git push -u origin main
```

### Step 3: Create .gitignore

Create `.gitignore` file in root directory:

```
# Environment & Config
.env
config.php
*.log

# Dependencies
vendor/
node_modules/

# Uploads
uploads/images/*
uploads/zips/*
!uploads/.gitkeep

# Session & Cache
*.tmp
.cache/
session/

# IDE
.vscode/
.idea/
*.swp
*.swo

# OS
.DS_Store
Thumbs.db

# Test files
test_credentials.txt
cookies.txt
server.log
```

### Step 4: Add to GitHub

```bash
# After creating .gitignore
git add .gitignore
git commit -m "Add .gitignore"
git push origin main
```

---

## 🌐 Deployment Options

### Option 1: InfinityFree (Free Hosting) ⭐

**Best for:** Beginners, portfolio projects

#### Setup Steps:

1. **Create Account**
   - Visit [infinityfree.net](https://infinityfree.net)
   - Sign up for free account

2. **Create Website**
   - Go to Dashboard → Create New Site
   - Enter domain name (e.g., `dreamwebsites.infinityfree.app`)
   - Click "Create"

3. **Get FTP Credentials**
   - Copy FTP Host, Username, Password
   - Save these for later

4. **Upload Files via FTP**
   - Download [FileZilla](https://filezilla-project.org/) or similar FTP client
   - Host: `ftpaccount.infinityfree.net`
   - Enter credentials
   - Upload all files to `htdocs` folder

5. **Setup Database**
   - Go to MySQL Database
   - Create new database
   - Copy credentials
   - Edit `config.php`:

```php
define('DB_HOST', 'your-infinityfree-host');
define('DB_NAME', 'if0_XXXX_dreamwebsites');
define('DB_USER', 'if0_XXXX_dbuser');
define('DB_PASS', 'your_password');
```

6. **Import Database Schema**
   - Go to InfinityFree → MySQL Management
   - Click "phpmyadmin"
   - Select database → Import
   - Upload `InfinityFreeSQL.sql`
   - Click Go

7. **Access Your Site**
   - Visit `https://dreamwebsites.infinityfree.app`

---

### Option 2: Local Development Setup 🖥️

**Best for:** Development, testing

#### Prerequisites:
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx (or PHP built-in server)

#### Setup:

```bash
# 1. Clone repository
git clone https://github.com/YOUR_USERNAME/dreamwebsites.git
cd dreamwebsites

# 2. Start PHP server
php -S localhost:8000

# 3. Create database
mysql -u root -p < LocalSQL.sql

# 4. Configure
# Edit config.php with your database details

# 5. Access
# Visit http://localhost:8000
```

---

### Option 3: Heroku (With Buildpack) 💙

**Best for:** Production deployment, scalability

#### Setup:

```bash
# 1. Install Heroku CLI
# Visit https://devcenter.heroku.com/articles/heroku-cli

# 2. Login
heroku login

# 3. Create Heroku app
heroku create dreamwebsites

# 4. Add buildpack
heroku buildpacks:add heroku/php
heroku buildpacks:add heroku/python

# 5. Configure environment
heroku config:set DB_HOST=your_db_host
heroku config:set DB_NAME=your_db_name
heroku config:set DB_USER=your_db_user
heroku config:set DB_PASS=your_db_pass

# 6. Deploy
git push heroku main

# 7. View logs
heroku logs --tail
```

---

### Option 4: DigitalOcean Droplet 🌊

**Best for:** Full control, scaling, professional deployment

#### Setup (Ubuntu 20.04):

```bash
# 1. SSH into droplet
ssh root@your_droplet_ip

# 2. Update system
sudo apt update && sudo apt upgrade -y

# 3. Install LAMP stack
sudo apt install -y apache2 php php-mysql mysql-server

# 4. Enable mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2

# 5. Clone repository
cd /var/www/html
sudo git clone https://github.com/YOUR_USERNAME/dreamwebsites.git
sudo chown -R www-data:www-data dreamwebsites

# 6. Setup MySQL
sudo mysql -u root -p
CREATE DATABASE dreamwebsites;
CREATE USER 'dreamweb'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON dreamwebsites.* TO 'dreamweb'@'localhost';
FLUSH PRIVILEGES;

# 7. Import schema
sudo mysql -u dreamweb -p dreamwebsites < /var/www/html/dreamwebsites/LocalSQL.sql

# 8. Update config.php
sudo nano /var/www/html/dreamwebsites/config.php

# 9. Set permissions
sudo chmod -R 755 /var/www/html/dreamwebsites/uploads/

# 10. Access via IP
# Visit http://your_droplet_ip/dreamwebsites
```

---

### Option 5: Docker Deployment 🐳

**Best for:** Containerized deployment, consistency

Create `Dockerfile`:

```dockerfile
FROM php:7.4-apache

# Install dependencies
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable mod_rewrite
RUN a2enmod rewrite

# Copy application
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/uploads

EXPOSE 80
```

Create `docker-compose.yml`:

```yaml
version: '3.8'

services:
  web:
    build: .
    ports:
      - "8000:80"
    volumes:
      - ./:/var/www/html
    environment:
      - DB_HOST=mysql
      - DB_NAME=dreamwebsites
      - DB_USER=root
      - DB_PASS=password

  mysql:
    image: mysql:5.7
    environment:
      MYSQL_ROOT_PASSWORD: password
      MYSQL_DATABASE: dreamwebsites
    volumes:
      - ./LocalSQL.sql:/docker-entrypoint-initdb.d/init.sql
    ports:
      - "3306:3306"
```

Run with:
```bash
docker-compose up -d
```

---

## 📊 GitHub Repository Structure

### Recommended Folder Organization:

```
dreamwebsites/
├── .github/
│   ├── workflows/
│   │   └── php-tests.yml       # CI/CD
│   └── ISSUE_TEMPLATE/
│       └── bug_report.md
│
├── docs/
│   ├── INSTALLATION.md
│   ├── DEPLOYMENT.md
│   ├── API.md
│   └── DATABASE.md
│
├── tests/
│   └── ConnectionTest.php
│
├── src/ (optional)
│   └── Database.php
│
├── [existing files and folders]
├── README.md
├── LICENSE
├── .gitignore
└── docker-compose.yml
```

---

## 🔄 Git Workflow

### Regular Updates:

```bash
# Make changes
# Edit files...

# Check status
git status

# Stage changes
git add .

# Commit with clear message
git commit -m "feat: add user dashboard enhancements"

# Push to GitHub
git push origin main
```

### Branch Strategy:

```bash
# Create feature branch
git checkout -b feature/add-payment-gateway

# Make changes...
git add .
git commit -m "Add Stripe payment integration"

# Push branch
git push origin feature/add-payment-gateway

# Create Pull Request on GitHub
# (Merge via GitHub interface)
```

---

## 📝 Create README Badges

Add to your GitHub README.md:

```markdown
![PHP](https://img.shields.io/badge/php-7.4+-green)
![MySQL](https://img.shields.io/badge/mysql-5.7+-blue)
![Bootstrap](https://img.shields.io/badge/bootstrap-5.3-purple)
![License](https://img.shields.io/badge/license-MIT-green)
[![GitHub Issues](https://img.shields.io/github/issues/USERNAME/dreamwebsites)](https://github.com/USERNAME/dreamwebsites/issues)
[![GitHub Stars](https://img.shields.io/github/stars/USERNAME/dreamwebsites)](https://github.com/USERNAME/dreamwebsites)
```

---

## 🔐 Security Checklist Before Deployment

- [ ] Never commit `config.php` with real credentials
- [ ] Use environment variables for sensitive data
- [ ] Set proper file permissions (755 for dirs, 644 for files)
- [ ] Enable HTTPS/SSL certificate
- [ ] Implement password hashing (bcrypt)
- [ ] Add CSRF token validation
- [ ] Enable prepared statements for DB queries
- [ ] Set up regular backups
- [ ] Configure firewall rules
- [ ] Keep PHP and MySQL updated

---

## 📚 Additional Resources

- [GitHub Guides](https://guides.github.com)
- [GitHub Pages](https://pages.github.com)
- [GitHub Actions CI/CD](https://github.com/features/actions)
- [PHP Security](https://www.php.net/manual/en/security.php)
- [MySQL Best Practices](https://dev.mysql.com/doc/)

---

## ✅ Verification Checklist

After deployment, verify:

- [ ] Homepage loads correctly
- [ ] Theme toggle works (light/dark mode)
- [ ] Database connection successful
- [ ] Login/register functionality works
- [ ] Image uploads work
- [ ] Responsive design on mobile
- [ ] No console errors
- [ ] All links functional
- [ ] Footer displays correctly
- [ ] Navigation smooth scrolling works

---

## 🆘 Troubleshooting

### Common Issues

**Issue:** `500 Internal Server Error`
```
Solution:
1. Check error logs
2. Verify database connection
3. Check file permissions
4. Review PHP error reporting
```

**Issue:** `Database connection failed`
```
Solution:
1. Verify MySQL is running
2. Check credentials in config.php
3. Ensure database exists
4. Check host/port settings
```

**Issue:** `Files not uploading`
```
Solution:
1. Check upload directory permissions
2. Verify uploads folder exists
3. Check PHP upload size limits
4. Review web server permissions
```

---

## 📞 Support

For issues, questions, or contributions:
- Open GitHub Issue
- Email: support@dreamwebsites.com
- Discord: [Join Community]

---

**Happy Coding! 🚀**
