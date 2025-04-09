<p align="center">
  <img src="https://github.com/PIN0L33KZ/obsidian-panel/blob/main/img/logo.svg" alt="Logo" width="200"/>
</p>
<h1 align="center">Obsidian Panel</h1>
<p align="center">
  A modern, lightweight, self-hosted Minecraft server management panel.
</p>
<p align="center">
  <img src="https://img.shields.io/github/workflow/status/PIN0L33KZ/obsidian-panel/CI" alt="Logo"/>
  <img src="https://img.shields.io/github/v/release/PIN0L33KZ/obsidian-panel" alt="Logo"/>
</p>

## 📚 Table of Contents
- [🔍 Project Overview](#project-overview)
- [🖼️ Screenshots](#screenshots)
- [📦 Prerequisites](#prerequisites)
- [🚀 Installation Guide](#installation-guide)
  - [1. Update your server](#update-your-server)
  - [2. Install Apache2](#install-apache2)
  - [3. Enable required Apache modules](#enable-required-apache-modules)
  - [4. Install PHP and extensions](#install-php-and-extensions)
  - [5. Configure firewall (UFW)](#configure-firewall-ufw)
  - [6. Create virtual host](#create-virtual-host)
  - [7. Download and install Obsidian Panel](#download-and-install-obsidian-panel)
  - [8. Configuration](#configuration)
  - [9. Web based setup](#web-based-setup)
  - [10. Clean up](#clean-up)
- [✅ Done](#all-set)
- [Support](#support)

## Project Overview
Obsidian Panel is a modern, lightweight, self-hosted Minecraft server management panel. It allows you to manage your Minecraft server with ease, providing a user-friendly interface and advanced features.

## Screenshots
> Login screen
![grafik](https://github.com/user-attachments/assets/8b7e052d-bfde-4d6b-9d21-bb14e683600c) <br>
> Dashboard
![grafik](https://github.com/user-attachments/assets/2fb62f95-7960-4e0b-bdd3-36773aa939ea) <br>
> File manager
![grafik](https://github.com/user-attachments/assets/2728e271-cafd-492f-9fa4-ac49203d570c) <br>
> Admin center
![grafik](https://github.com/user-attachments/assets/2792d486-32d4-42aa-af63-ce4d29d43615) <br>

## Prerequisites
- Apache2 / Nginx / Lighttpd
- PHP 8.2 or higher
- GNU Screen
- Java / OpenJDK / Adoptium (version depends on your Minecraft version)

## Installation Guide
### Update your server
Update your Repo-lists with the following command:
```bash
apt-get update
```
Upgrade your Packages to the newest available version with the following command:
```bash
apt-get upgrade -y
```

## Install Apache2
This Documentation proceeds with Apache2 but you can choose a different webserver.
Continue your Apache2 installation with the following commands:
```bash
sudo apt install apache2 -y
sudo systemctl enable apache2
sudo systemctl start apache2
```

## Enable required Apache modules
Install the Rewrite and Headers-Module for Apache2 with the following command:
```bash
a2enmod rewrite headers
```
> [!IMPORTANT]
> If you want to use SSL encryption you also need to install the following Apache2 module:
```bash
a2enmod ssl
```

Restart your Apache2 service with the following command:
```bash
systemctl restart apache2
```

## Install PHP and extensions
Install PHP and the requierd extensions with the following command:
```bash
apt-get install php8.2 php8.2-gd -y
```

## Configure firewall (UFW)
In this documentation we'll use UFW as firewall service. To install UFW use the following command:
```bash
apt-get install ufw -y
```
Make sure your SSH Port (22 via TCP) is opened!
```bash
ufw allow in 22/tcp comment 'SSH'
```
Obsidian Panel uses default HTTP/S ports. Open them via the following command:
```bash
ufw allow in 80/tcp comment 'Obsidian panel (HTTP)'
```
and
```bash
ufw allow in 443/tcp comment 'Obsidian panel (HTTPS'
```
> [!TIP]
> Your UFW configuration should look something like this:
```bash
    [ 1] 22/tcp                       ALLOW IN      Anywhere                   # SSH
    [ 2] 80/tcp                       ALLOW IN      Anywhere                   # Webpanel (HTTP)
    [ 3] 443/tcp                      ALLOW IN      Anywhere                   # Webpanel (HTTPS)
```

Enable and reload your firewall by using the following command:
```bash
ufw enable && ufw reload
```

## Create virtual host
Create your desired directory structure for your webserver:
```bash
mkdir /var/www/<yourDomain>/public_html/
mkdir /var/www/<yourDomain>/logs/
mkdir /var/www/<yourDomain>/public_servers/
```
Set the requiered permissions so that the web user can access your files:
```bash
chown www-data /var/www/<yourDomain> -R
chmod 755 /var/www/<yourDomain> -R
```
Create a config file in `/etc/apache2/sites-available/` with the following command:
```bash
vim /etc/apache2/sites-available/obsidian-panel.conf
```
Paste the following template into the config file.
```bash
<VirtualHost *:80>
    ServerName <yourDomain>

    # Rewrite to SSL
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^/?(.*) https://%{SERVER_NAME}/$1 [R=301,L]
</VirtualHost>

<VirtualHost *:443>
    ServerAdmin <rootMail>
    ServerName <yourDomain>
    DocumentRoot /var/www/<yourDomain>/public_html/

    # SSL config
    SSLEngine on
    SSLCertificateFile <yourSslCertificate (*.crt || *.pem)>
    SSLCertificateKeyFile <yourSslKey (*.key || *.pem)>
    # Logs
    ErrorLog /var/www/<yourDomain>/logs/error.log
    CustomLog /var/www/<yourDomain>/logs/access.log combined

    # Security and Performance Settings
    <Directory /var/www/<yourDomain>/public_html/>
        Options +Indexes +FollowSymLinks
        AllowOverride All
        Require all granted
        DirectoryIndex index.php
    </Directory>

    # Security Header Hardening
    Header always set X-Frame-Options "SAMEORIGIN"
    Header always set X-Content-Type-Options "nosniff"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000"
</VirtualHost>
```
Enable your site and reload the Apache service with the following command:
```bash
a2ensite /etc/apache2/sites-available/obsidian-panel.conf
systemctl reload apache2
```

## Download and install Obsidian Panel
Download the latest release from ```https://github.com/PIN0L33KZ/obsidian-panel/releases```
Unzip all files into your webroot (`/var/www/<yourDomain>/public_html/`)
Reset the requiered permissions so that the web user can access all files:
```bash
chown www-data /var/www/<yourDomain> -R
chmod 755 /var/www/<yourDomain> -R
```

## Configuration
Copy the example config by using the following command:
```bash
cp data/config-sample.php data/config.php
```
and update `KT_LOCAL_IP` with your server´s IP Address:
```bash
vim data/config.php
```
edit the followig line: `define('KT_LOCAL_IP', 'your_server_IP');`
> [!IMPORTANT]
> If the webpanel runs on the same server as the minecraft server itself use `127.0.0.1`

## Web based setup
Open your browser and head over to `http://<your_domain_or_IP>/install.php` and follow the instructions to create your admin account.
> [!TIP]
> If you use SSL encryption your webserver will automatically redirect http to https!

## Clean up
You can delete the `install.php` file now:
```bash
rm /var/www/<yourDomain>/public_html/install.php
```

# All set!
You can now access and use your Obsidian Panel via your browser `http://<your_domain_or_IP>`

# Support
You can contact me via E-Mail `contact@pinoleekz.de` or via my web forumlar at `https://www.pinoleekz.de/contact`
