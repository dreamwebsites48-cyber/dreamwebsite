# 🌐 DreamWebsites - Premium Web Platform

A modern, full-featured web platform for buying, selling, and customizing premium websites. Connect developers with users and manage a complete marketplace ecosystem.

![Version](https://img.shields.io/badge/version-2.0-blue)
![PHP](https://img.shields.io/badge/php-7.4+-green)
![Bootstrap](https://img.shields.io/badge/bootstrap-5.3-purple)
![License](https://img.shields.io/badge/license-MIT-green)

---

## 📋 Table of Contents

- [Features](#features)
- [Project Structure](#project-structure)
- [Technology Stack](#technology-stack)
- [Installation](#installation)
- [Configuration](#configuration)
- [Database Setup](#database-setup)
- [User Roles](#user-roles)
- [File Descriptions](#file-descriptions)
- [API Endpoints](#api-endpoints)
- [Customization](#customization)
- [Support](#support)

---

## ✨ Features

### 🎯 Core Features
- **Multi-Role System**: Admin, Developer, and User accounts
- **Website Marketplace**: Browse, buy, and sell premium websites
- **Developer Portfolio**: Showcase websites with image galleries
- **Admin Dashboard**: Comprehensive management system
- **Authentication System**: Secure login/registration
- **Password Recovery**: Forgot password functionality
- **Profile Management**: User and developer profiles
- **Website Customization**: Edit website details and prices

### 🎨 UI/UX Features
- **Dark/Light Theme Toggle**: User preference persistence
- **Responsive Design**: Mobile-first approach
- **Glassmorphism Design**: Modern aesthetic
- **Smooth Animations**: Engaging fade-up transitions
- **Image Carousels**: Multi-image galleries for websites
- **Lightbox Modal**: Image preview system
- **Toast Notifications**: Real-time user feedback
- **Professional Color Scheme**: Gradient accents and premium styling

### 🔐 Security Features
- **Session Management**: PHP session handling
- **Input Validation**: Secure form processing
- **HTML Escaping**: XSS protection
- **Database Security**: Prepared statements ready

---

## 📁 Project Structure

```
dreamwebsitegpt/
├── index.php                    # Homepage
├── login.php                    # User login
├── register.php                 # User registration
├── about.php                    # About page
├── help.php                     # Help/FAQ page
├── websites.php                 # Website marketplace
├── logout.php                   # Logout handler
├── forgot.php                   # Password recovery
├── config.php                   # Database configuration
│
├── admin/                       # Admin panel
│   ├── dashboard.php            # Admin dashboard
│   ├── login.php                # Admin login
│   ├── logout.php               # Admin logout
│   ├── manage_users.php         # User management
│   ├── manage_developers.php     # Developer management
│   ├── manage_offers.php         # Website/offer management
│   ├── manage_websites.php       # Website details management
│   ├── edit_offer.php            # Edit website listing
│   ├── developer_requests.php    # Manage developer applications
│   └── password_requests.php     # Handle password reset requests
│
├── developer/                   # Developer panel
│   ├── dashboard.php            # Developer dashboard
│   ├── add_website.php          # Add new website
│   ├── edit.php                 # Edit website
│   ├── delete.php               # Delete website
│   └── upload_offer.php         # Upload website details
│
├── user/                        # User dashboard
│   ├── dashboard.php            # User dashboard
│   ├── profile.php              # User profile
│   ├── websites.php             # My websites
│   ├── contact.php              # Contact support
│   ├── about.php                # About section
│   └── help.php                 # Help section
│
├── includes/                    # Reusable components
│   ├── header.php               # HTML head section
│   ├── navbar.php               # Navigation bar
│   └── footer.php               # Footer section
│
├── assets/                      # Static files
│   ├── css/
│   │   ├── theme.css            # Main stylesheet
│   │   └── style.css            # Additional styles
│   └── js/
│       └── theme.js             # Theme toggle & animations
│
├── uploads/                     # File upload directory
│   ├── images/                  # Website preview images
│   └── zips/                    # Website file archives
│
├── database/
│   ├── LocalSQL.sql             # Local development database
│   └── InfinityFreeSQL.sql      # Production database schema
│
└── test_credentials.txt         # Sample login credentials

```

---

## 🛠️ Technology Stack

### Backend
- **PHP 7.4+** - Server-side logic
- **MySQL 5.7+** - Database management
- **Session Management** - User authentication

### Frontend
- **HTML5** - Semantic markup
- **CSS3** - Advanced styling with CSS variables
- **JavaScript (ES6+)** - DOM manipulation
- **Bootstrap 5.3** - Responsive framework
- **FontAwesome 6.4** - Icon library

### External Libraries
- **Bootstrap 5.3.0** - CSS framework
- **FontAwesome 6.4.0** - Icons
- **Google Fonts** - Custom typography (Inter, Outfit)

---

## 🚀 Installation

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7 or higher
- Web server (Apache/Nginx)
- Composer (optional, for dependencies)

### Step 1: Clone/Download the Project
```bash
git clone https://github.com/yourusername/dreamwebsites.git
cd dreamwebsites
```

### Step 2: Create Database
```bash
mysql -u root -p < LocalSQL.sql
```

### Step 3: Configure Environment
Edit `config.php` with your database credentials:
```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'dreamwebsites');
define('DB_USER', 'root');
define('DB_PASS', 'your_password');
```

### Step 4: Set Upload Directory Permissions
```bash
chmod -R 755 uploads/
chmod -R 755 uploads/images/
chmod -R 755 uploads/zips/
```

### Step 5: Start Your Server
```bash
# Using PHP built-in server (development)
php -S localhost:8000

# Or use Apache/Nginx
# Configure virtual host to point to project directory
```

### Step 6: Access the Application
```
http://localhost:8000
```

---

## ⚙️ Configuration

### config.php - Database Setup
```php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'dreamwebsites');
define('DB_USER', 'root');
define('DB_PASS', '');
define('SITE_NAME', 'DreamWebsites');
define('SITE_URL', 'http://localhost:8000');
?>
```

### Database Connection Example
```php
try {
    $conn = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME,
        DB_USER,
        DB_PASS
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die('Connection Error: ' . $e->getMessage());
}
```

---

## 🗄️ Database Setup

### Import Database Schema

**For Local Development:**
```bash
mysql -u root -p < LocalSQL.sql
```

**For InfinityFree Hosting:**
```bash
mysql -u infinityfree_user -p < InfinityFreeSQL.sql
```

### Database Tables

1. **users** - Regular users
   - id, username, email, password, name, role, created_at

2. **developers** - Developer accounts
   - id, user_id, company, bio, portfolio_link, created_at

3. **websites** (offers) - Website listings
   - id, developer_id, title, description, price, category, website_link, status

4. **website_images** - Preview images
   - id, website_id, image_path, order_index

5. **admin** - Administrator accounts
   - id, username, password, email, created_at

---

## 👥 User Roles

### 1. **Admin** 👨‍💼
- Full system access
- Manage all users, developers, and website listings
- Handle password reset requests
- Monitor developer applications
- Access: `/admin/dashboard.php`

### 2. **Developer** 👨‍💻
- Create and manage website listings
- Upload website details and images
- View sales statistics
- Manage portfolio
- Access: `/developer/dashboard.php`

### 3. **User** 👤
- Browse marketplace
- Purchase/download websites
- Manage profile
- View purchased websites
- Access: `/user/dashboard.php`

### 4. **Guest** 🌐
- View public pages
- Browse featured websites
- Register/login
- Access: `/index.php`

---

## 📄 File Descriptions

### Core Pages

#### `index.php`
- Homepage with hero section
- Featured websites carousel
- Platform roles showcase
- Requires: `config.php`, Bootstrap, FontAwesome

#### `login.php`
- User/developer login form
- Session management
- Remember me functionality (optional)
- Admin redirect logic

#### `register.php`
- New user registration
- Form validation
- Email verification (optional)
- Role selection

#### `websites.php`
- Website marketplace
- Search and filter functionality
- Pagination
- Image carousels per website

#### `about.php`
- Company information
- Mission statement
- Team showcase
- Contact information

#### `help.php`
- FAQ section
- Troubleshooting guides
- Contact support form
- Knowledge base

### Admin Panel

#### `admin/dashboard.php`
- Admin overview
- Statistics and metrics
- Quick actions
- User activity feed

#### `admin/manage_users.php`
- List all users
- View user details
- Suspend/activate accounts
- Send messages

#### `admin/manage_developers.php`
- Developer applications
- Approve/reject developers
- View statistics
- Monitor compliance

#### `admin/manage_offers.php`
- Website listing management
- Approve/reject listings
- Set featured status
- Category management

### Developer Panel

#### `developer/dashboard.php`
- Listing statistics
- Sales history
- View traffic
- Quick actions

#### `developer/add_website.php`
- Form to list new website
- Upload preview images
- Set pricing
- Add description

#### `developer/edit.php`
- Modify website details
- Update images
- Change pricing
- Manage availability

### User Panel

#### `user/dashboard.php`
- Owned websites list
- Purchase history
- Downloads
- Upgrade account

---

## 🎨 CSS & Styling

### Theme Variables (theme.css)
```css
:root {
    /* Colors */
    --bg-primary: #0f111a;
    --bg-secondary: #161b22;
    --text-primary: #f0f6fc;
    --accent-primary: #3b82f6;
    
    /* Glassmorphism */
    --glass-bg: rgba(255, 255, 255, 0.03);
    --glass-border: rgba(255, 255, 255, 0.08);
}
```

### Custom Classes
- `.glass-panel` - Glassmorphism effect
- `.btn-premium` - Primary button style
- `.text-gradient` - Gradient text
- `.animate-fade-up` - Fade up animation
- `.sidebar-premium` - Sidebar styling

### Responsive Breakpoints
- Mobile: < 768px
- Tablet: 768px - 1024px
- Desktop: > 1024px

---

## 💻 JavaScript Features

### theme.js

#### Theme Toggle
```javascript
// Dark/Light mode switching
localStorage.setItem('theme', theme);
document.body.setAttribute('data-theme', theme);
```

#### Toast Notifications
```javascript
showToast('Message', 'success'); // success, error, warning, info
```

#### Animations
```javascript
// Automatic fade-up animation delays
.animate-fade-up { animation: fadeUp 0.8s ease; }
```

---

## 🔗 API Endpoints

### Authentication
- `POST /login.php` - User login
- `POST /register.php` - New registration
- `POST /logout.php` - Logout
- `GET /forgot.php` - Password recovery

### User Dashboard
- `GET /user/dashboard.php` - User overview
- `GET /user/profile.php` - User profile
- `GET /user/websites.php` - My websites

### Developer Dashboard
- `GET /developer/dashboard.php` - Developer overview
- `POST /developer/add_website.php` - Add website
- `POST /developer/edit.php` - Update website
- `POST /developer/delete.php` - Delete website

### Admin Dashboard
- `GET /admin/dashboard.php` - Admin overview
- `GET /admin/manage_users.php` - User management
- `GET /admin/manage_developers.php` - Developer management
- `GET /admin/manage_offers.php` - Website management

### Public Pages
- `GET /index.php` - Homepage
- `GET /websites.php` - Marketplace
- `GET /about.php` - About page
- `GET /help.php` - Help page

---

## 🎯 Customization

### Change Theme Colors
Edit `assets/css/theme.css`:
```css
:root {
    --accent-primary: #3b82f6; /* Change primary color */
    --accent-secondary: #8b5cf6; /* Change secondary color */
}
```

### Modify Site Name
Edit `config.php`:
```php
define('SITE_NAME', 'Your Site Name');
```

### Customize Fonts
Edit `theme.css` Google Fonts import:
```css
@import url('https://fonts.googleapis.com/css2?family=YourFont:wght@300;400;600;700&display=swap');
```

### Add Custom Pages
1. Create new `.php` file in root directory
2. Include navbar/header:
```php
<?php require_once 'config.php'; ?>
<?php include 'includes/navbar.php'; ?>
```
3. Include footer:
```php
<?php include 'includes/footer.php'; ?>
```

---

## 📱 Mobile Optimization

The platform is fully responsive with:
- Mobile-first design approach
- Touch-friendly buttons (min 44x44px)
- Optimized navigation menus
- Flexible image galleries
- Readable text sizes (16px+ base)
- Proper spacing and padding

---

## 🔒 Security Considerations

### Currently Implemented
- PHP session management
- HTML entity escaping
- Input validation

### Recommended Additions
- HTTPS/SSL certificates
- CSRF token validation
- Password hashing (bcrypt)
- Rate limiting
- SQL prepared statements
- Content Security Policy headers
- Two-factor authentication

---

## 🐛 Troubleshooting

### Database Connection Issues
```
Error: SQLSTATE[HY000]: General error
Solution: Check config.php credentials and MySQL service status
```

### Upload Directory Permissions
```
Error: Unable to save uploaded files
Solution: chmod -R 755 uploads/
```

### Session Not Working
```
Error: $_SESSION variables not persisting
Solution: Ensure session_start() called at top of files
```

### Theme Not Loading
```
Error: CSS not applying
Solution: Check if assets/css/theme.css path is correct
```

---

## 📚 Documentation

### For Developers
- See `DEVELOPER.md` for API documentation
- See `DATABASE.md` for schema details

### For Users
- See `USER_GUIDE.md` for feature documentation

### For Admins
- See `ADMIN_GUIDE.md` for management tools

---

## 🤝 Contributing

1. Fork the repository
2. Create feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit changes (`git commit -m 'Add AmazingFeature'`)
4. Push to branch (`git push origin feature/AmazingFeature`)
5. Open Pull Request

---

## 📄 License

This project is licensed under the MIT License - see LICENSE.md file for details.

---

## 👥 Support

### Getting Help
- Email: support@dreamwebsites.com
- Issues: GitHub Issues
- Discord: [Join Server]
- Documentation: https://docs.dreamwebsites.com

### FAQs
1. **How do I reset my password?** → Use forgot.php link on login page
2. **How do I become a developer?** → Register as user, apply in dashboard
3. **How do I list my website?** → Use developer/add_website.php
4. **Is my data secure?** → Yes, we use session management and input validation

---

## 🎉 Credits

- **Bootstrap Team** - CSS Framework
- **FontAwesome** - Icon Library
- **Google Fonts** - Typography
- **Community Contributors** - Bug reports and suggestions

---

## 📈 Roadmap

### v2.1 (Upcoming)
- [ ] Payment gateway integration
- [ ] Email notifications
- [ ] Advanced search filters
- [ ] User ratings and reviews
- [ ] Developer badges/certifications

### v3.0 (Planned)
- [ ] REST API
- [ ] Mobile app (iOS/Android)
- [ ] Real-time notifications
- [ ] Advanced analytics
- [ ] AI-powered recommendations

---

## ⭐ Show Your Support

Give this project a ⭐️ if you found it helpful!

---

**Last Updated:** May 2024
**Version:** 2.0
**Status:** Active Development
