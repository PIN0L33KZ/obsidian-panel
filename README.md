<p align="center">
  <img src="https://github.com/PIN0L33KZ/obsidian-panel/blob/main/img/logo.svg" alt="Logo" width="200"/>
</p>
<h1 align="center">Obsidian Panel</h1>
<p align="center">
  A modern, lightweight, and self-hosted Minecraft server management panel.
</p>
<p align="center">
  <img src="https://img.shields.io/github/v/release/PIN0L33KZ/obsidian-panel" alt="Release Version"/>
</p>

## 📚 Table of Contents
- [🔍 Project Overview](#project-overview)
- [🖼️ Screenshots](#screenshots)
- [📦 Prerequisites](#prerequisites)
- [🚀 Installation Guide](#installation-guide)
  - [1. Update your server](#update-your-server)
  - [2. Install Apache2](#install-apache2)
  - [3. Enable required Apache modules](#enable-required-apache-modules)
  - [4. Install PHP and required extensions](#install-php-and-required-extensions)
  - [5. Configure firewall (UFW)](#configure-firewall-ufw)
  - [6. Create virtual host](#create-virtual-host)
  - [7. Download and install Obsidian Panel](#download-and-install-obsidian-panel)
  - [8. Configuration](#configuration)
  - [9. Web-based setup](#web-based-setup)
  - [10. Clean up](#clean-up)
- [✅ All set](#all-set)
- [📬 Support](#support)

## 🔍 Project Overview
**Obsidian Panel** is a sleek, efficient, and self-hosted solution for managing Minecraft servers. It provides a modern web interface, designed for simplicity and functionality, empowering administrators to easily manage server files, monitor performance, and configure game settings — all through a secure and user-friendly interface.

## 🖼️ Screenshots
> Login screen  
![Login](https://github.com/user-attachments/assets/8b7e052d-bfde-4d6b-9d21-bb14e683600c)  

> Dashboard  
![Dashboard](https://github.com/user-attachments/assets/2fb62f95-7960-4e0b-bdd3-36773aa939ea)  

> File manager  
![File Manager](https://github.com/user-attachments/assets/2728e271-cafd-492f-9fa4-ac49203d570c)  

> Admin center  
![Admin Center](https://github.com/user-attachments/assets/2792d486-32d4-42aa-af63-ce4d29d43615)  

## 📦 Prerequisites
Ensure the following dependencies are installed before proceeding:

- A web server: Apache2, Nginx, or Lighttpd
- PHP version 8.2 or higher
- GNU Screen (for managing terminal sessions)
- Java Runtime Environment (OpenJDK / Adoptium), compatible with your Minecraft server version

## 🚀 Installation Guide

### 1. Update your server
Refresh your repository list:
```bash
apt-get update
```
Upgrade installed packages:
```bash
apt-get upgrade -y
```

### 2. Install Apache2
Although this guide uses **Apache2**, feel free to choose another supported web server.

Install and enable Apache2:
```bash
sudo apt install apache2 -y
sudo systemctl enable apache2
sudo systemctl start apache2
```

### 3. Enable required Apache modules
Activate necessary modules:
```bash
a2enmod rewrite headers
```

> [!IMPORTANT]
> For SSL (HTTPS) support, enable the SSL module as well:
```bash
a2enmod ssl
```

Restart Apache:
```bash
systemctl restart apache2
```

### 4. Install PHP and required extensions
Install PHP 8.2 and its required extensions:
```bash
apt-get install php8.2 php8.2-gd -y
```

### 5. Configure firewall (UFW)
Install **Uncomplicated Firewall (UFW)**:
```bash
apt-get install ufw -y
```

Allow SSH (port 22):
```bash
ufw allow in 22/tcp comment 'SSH'
```

Allow HTTP/HTTPS traffic for the panel:
```bash
ufw allow in 80/tcp comment 'Obsidian Panel (HTTP)'
ufw allow in 443/tcp comment 'Obsidian Panel (HTTPS)'
```

Example rule list:
```bash
[ 1] 22/tcp     ALLOW IN    Anywhere    # SSH
[ 2] 80/tcp     ALLOW IN    Anywhere    # Webpanel (HTTP)
[ 3] 443/tcp    ALLOW IN    Anywhere    # Webpanel (HTTPS)
```

Enable and reload UFW:
```bash
ufw enable && ufw reload
```

### 6. Create virtual host
Create directory structure:
```bash
mkdir -p /var/www/<yourDomain>/public_html/
mkdir /var/www/<yourDomain>/logs/
mkdir /var/www/<yourDomain>/public_servers/
```

Set permissions:
```bash
chown www-data /var/www/<yourDomain> -R
chmod 755 /var/www/<yourDomain> -R
```

Create virtual host configuration:
```bash
vim /etc/apache2/sites-available/obsidian-panel.conf
```

Insert:
```bash
<VirtualHost *:80>
    ServerName <yourDomain>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^/?(.*) https://%{SERVER_NAME}/$1 [R=301,L]
</VirtualHost>

<VirtualHost *:443>
    ServerAdmin <rootMail>
    ServerName <yourDomain>
    DocumentRoot /var/www/<yourDomain>/public_html/

    SSLEngine on
    SSLCertificateFile <yourSslCertificate (.crt or .pem)>
    SSLCertificateKeyFile <yourSslKey (.key or .pem)>

    ErrorLog /var/www/<yourDomain>/logs/error.log
    CustomLog /var/www/<yourDomain>/logs/access.log combined

    <Directory /var/www/<yourDomain>/public_html/>
        Options +Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
    </Directory>

    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000"
</VirtualHost>
```

Enable the site and reload Apache:
```bash
a2ensite obsidian-panel.conf
systemctl reload apache2
```

### 7. Download and install Obsidian Panel
Download the latest version from:
```bash
https://github.com/PIN0L33KZ/obsidian-panel/releases
```

Extract the files into your webroot:
```bash
unzip obsidian-panel.zip -d /var/www/<yourDomain>/public_html/
```

Adjust permissions:
```bash
chown www-data /var/www/<yourDomain> -R
chmod 755 /var/www/<yourDomain> -R
```

### 8. Configuration
Copy the sample config:
```bash
cp data/config-sample.php data/config.php
```

Edit the following line to reflect your server IP:
```bash
vim data/config.php
define('KT_LOCAL_IP', '127.0.0.1');
```

> [!IMPORTANT]
> Use `127.0.0.1` if the panel and server are on the same machine.

### 9. Web-based setup
Visit:
```bash
http://<your_domain_or_IP>/install.php
```
Follow the setup wizard to create your administrator account.

> [!TIP]
> If SSL is configured, you will be redirected automatically to HTTPS.

### 10. Clean up
Remove the installation file for security:
```bash
rm /var/www/<yourDomain>/public_html/install.php
```

## ✅ All set!
Your Obsidian Panel is now fully installed and accessible at:
```bash
http://<your_domain_or_IP>
```

## 📬 Support
For support or inquiries, please contact me via email at `contact@pinoleekz.de`  
or through the web form at: `https://www.pinoleekz.de/contact`
