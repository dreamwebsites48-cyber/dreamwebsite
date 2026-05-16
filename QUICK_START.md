# 🚀 Quick Start Guide - DreamWebsites

## ⚡ 5-Minute Setup

### Option A: View Demo (No Setup Required)

1. Open `demo.html` in your browser
2. Click "Toggle Theme" button to switch between dark/light mode
3. Explore the responsive design
4. ✅ Done! See the design in action

---

## ✅ Option B: Full PHP Setup (20 minutes)

### Prerequisites Check

```bash
# Check PHP version (7.4 or higher)
php --version

# Check MySQL (5.7 or higher)
mysql --version
```

### Step-by-Step Installation

#### 1. **Prepare the Project**

```bash
# Extract files to your web directory
cd /var/www/html  # or Apache root directory

# Navigate to project
cd dreamwebsites
```

#### 2. **Setup Database**

```bash
# Create database
mysql -u root -p < LocalSQL.sql

# Enter your MySQL root password when prompted
```

#### 3. **Configure Application**

Edit `config.php`:

```php
<?php
define('DB_HOST', 'localhost');      // Your DB host
define('DB_NAME', 'dreamwebsites');  // Database name
define('DB_USER', 'root');           // DB username
define('DB_PASS', 'your_password');  // DB password
define('SITE_NAME', 'DreamWebsites');
define('SITE_URL', 'http://localhost:8000');
?>
```

#### 4. **Set File Permissions**

```bash
# Make upload directories writable
chmod -R 755 uploads/
chmod -R 755 uploads/images/
chmod -R 755 uploads/zips/
```

#### 5. **Start the Server**

```bash
# Using PHP built-in server (easiest)
php -S localhost:8000

# Or use Apache if configured
# Visit: http://localhost:8000
```

#### 6. **Access the Application**

```
🌐 http://localhost:8000
```

---

## 📋 Test Credentials

Default accounts for testing:

### Admin Account
- **Email:** admin@dream.local
- **Password:** admin123
- **Access:** http://localhost:8000/admin/login.php

### Developer Account
- **Email:** dev@dream.local
- **Password:** dev123
- **Access:** http://localhost:8000/developer/dashboard.php

### User Account
- **Email:** user@dream.local
- **Password:** user123
- **Access:** http://localhost:8000/user/dashboard.php

---

## 🎯 First Things to Try

After setup, test these features:

### 1. **Homepage Exploration**
- [ ] Visit homepage
- [ ] Click "Explore Websites"
- [ ] Toggle dark/light theme
- [ ] View featured websites

### 2. **Authentication**
- [ ] Click "Login"
- [ ] Use test credentials above
- [ ] Try password recovery
- [ ] Explore user dashboard

### 3. **Developer Features**
- [ ] Login as developer
- [ ] Add a new website
- [ ] Upload preview images
- [ ] Set pricing and description

### 4. **Admin Features**
- [ ] Login to admin panel
- [ ] Manage users
- [ ] Manage developers
- [ ] Approve website listings

### 5. **Mobile Responsiveness**
- [ ] Resize browser to 375px width
- [ ] Test on actual mobile device
- [ ] Check touch interactions
- [ ] Verify navigation menu

---

## 📁 Important Files

| File | Purpose |
|------|---------|
| `index.php` | Homepage |
| `login.php` | User login |
| `register.php` | New user registration |
| `admin/dashboard.php` | Admin panel |
| `developer/dashboard.php` | Developer panel |
| `user/dashboard.php` | User dashboard |
| `config.php` | Database configuration |
| `assets/css/theme.css` | Main stylesheet |
| `assets/js/theme.js` | JavaScript functions |
| `demo.html` | Standalone demo (no PHP) |

---

## 🎨 Customization Quick Tips

### Change Theme Colors

Edit `assets/css/theme.css`:

```css
:root {
    --bg-primary: #0f111a;           /* Background dark */
    --text-primary: #f0f6fc;          /* Text light */
    --accent-primary: #3b82f6;        /* Primary blue */
    --accent-secondary: #8b5cf6;      /* Secondary purple */
}
```

### Change Site Name

Edit `config.php`:
```php
define('SITE_NAME', 'Your Site Name');
```

### Add Custom Logo

Replace favicon:
```html
<link rel="icon" href="assets/images/favicon.ico">
```

### Modify Typography

Edit `theme.css` fonts import:
```css
@import url('https://fonts.googleapis.com/css2?family=NewFont:wght@400;700&display=swap');
```

---

## 🔧 Troubleshooting

### Issue: "Blank page" or errors

**Solution:**
```bash
# Check PHP error log
tail -f /var/log/php-errors.log

# Or add to config.php temporarily
ini_set('display_errors', 1);
error_reporting(E_ALL);
```

### Issue: "Database connection failed"

**Solution:**
```bash
# Verify MySQL is running
sudo systemctl status mysql

# Check credentials
# Edit config.php and verify host/user/password

# Test connection
mysql -h localhost -u root -p
```

### Issue: "Can't upload images"

**Solution:**
```bash
# Fix permissions
sudo chmod -R 777 uploads/

# Verify directory exists
ls -la uploads/
```

### Issue: "CSS not loading"

**Solution:**
- Check file paths are correct
- Verify server is running
- Clear browser cache (Ctrl+Shift+Delete)
- Check browser console for errors (F12)

---

## 📚 Documentation Files

All documentation included:

- **README.md** - Full documentation
- **GITHUB_DEPLOYMENT_GUIDE.md** - Upload to GitHub
- **QUICK_START.md** - This file
- **demo.html** - Design demo (no PHP needed)

---

## 🌐 Next Steps

### Short Term
1. ✅ Run the application locally
2. ✅ Test all features
3. ✅ Customize colors/branding
4. ✅ Modify content/text

### Medium Term
1. 🔄 Set up GitHub repository
2. 📱 Test on mobile devices
3. 🔒 Implement security improvements
4. 💾 Set up regular backups

### Long Term
1. ☁️ Deploy to cloud hosting
2. 💳 Add payment gateway
3. 📧 Implement email notifications
4. 📊 Add analytics dashboard

---

## 📞 Quick Support

### Common Questions

**Q: Can I use this on Windows?**
A: Yes! Use XAMPP (https://www.apachefriends.org/) or WAMP

**Q: Do I need to modify the code?**
A: No, but it's recommended to customize it for your brand

**Q: Can I deploy to shared hosting?**
A: Yes! Use FTP to upload and import database via cpanel

**Q: How do I add new features?**
A: See the code structure and follow existing patterns

---

## ✨ Features At a Glance

### For Users 👤
- Browse website marketplace
- Purchase/download websites
- Manage profile
- View purchase history

### For Developers 👨‍💻
- List websites for sale
- Upload preview images
- Set competitive prices
- Track sales metrics

### For Admins 👨‍💼
- Manage all users
- Approve developer accounts
- Moderate website listings
- Handle disputes

### For Everyone 🌍
- Dark/Light theme toggle
- Responsive design
- Fast performance
- Secure transactions

---

## 🎓 Learning Resources

- **PHP Tutorial:** https://www.php.net/manual/
- **MySQL Guide:** https://dev.mysql.com/doc/
- **Bootstrap Docs:** https://getbootstrap.com/docs
- **CSS Reference:** https://developer.mozilla.org/en-US/docs/Web/CSS

---

## ✅ Success Checklist

- [ ] PHP and MySQL installed
- [ ] Project files extracted
- [ ] Database created and imported
- [ ] config.php configured
- [ ] Server running on localhost:8000
- [ ] Homepage accessible
- [ ] Theme toggle working
- [ ] Login page accessible
- [ ] Upload directories writable
- [ ] All links functional

---

## 🚀 Ready to Launch?

```bash
# Start development server
php -S localhost:8000

# For production, see GITHUB_DEPLOYMENT_GUIDE.md
```

Visit: **http://localhost:8000**

---

**Congratulations! Your DreamWebsites platform is ready! 🎉**

For full documentation, see **README.md**
For GitHub setup, see **GITHUB_DEPLOYMENT_GUIDE.md**
