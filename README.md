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

---

## ☝🏼 - Program Description

**Obsidian Panel** is a modernised continuation of the original [**MCHostPanel**](https://github.com/Alanaktion/MCHostPanel) project by Alan Hardman, first introduced in 2016. It provides a sleek, efficient, and self-hosted platform for managing Minecraft servers. Featuring a redesigned web interface focused on usability and performance, it allows administrators to manage files, monitor server performance, and configure game settings with ease, all within a secure and streamlined environment.

---

## 📔 - Version Information

Current version: **0.1.3.0** Release date: **08/04/2025**

Latest Updates:

- First Beta release to the public

---

## ⚠️ - Software Requirements

- Web Server (Apache2, Nginx, XAMMP or Lighttpd)
- PHP 8.2 (or higher)
- GNU Screen (Terminal Multiplexer)
- Java Runtime Environment (OpenJDK or Adoptium)

---

## 🚀 - Installation Guide

> [!NOTE]  
> This Installation Guide is based on Debian 12 (Bookworm) and the APT-Package Manager.

### Update your Linux Server

Refresh your repository list and upgrade installed packages:

```bash
sudo apt update; \
sudo apt upgrade -y
```

### Install Web Server

> [!NOTE]  
> This Guide uses Apache2 as Web Server but feel free to choose another option.

Install and enable the Apache2 Web Server:

```bash
sudo apt install apache2 -y; \
sudo systemctl enable apache2; \
sudo systemctl start apache2
```

### Enable required Apache2 modules

> [!TIP]
> The SSL Module is optional and only required if you want your Obsidian Panel Instance to be accessed via HTTPS.

Activate necessary modules:

```bash
a2enmod rewrite headers ssl
```

Restart the Apache2 Web Server to enable all Modules:

```bash
systemctl restart apache2
```

### Install PHP and required extensions

Install PHP 8.2 and its required extensions:

```bash
sudo apt install php8.2 php8.2-gd -y
```

### Install and configure Server Firewall

> [!NOTE]  
> This Guide uses UFW as Firewall, feel free to choose a different Firewall Service.

Install UFW (Uncomplicated Firewall):

```bash
sudo apt install ufw -y
```

Allow the communication to the following ports:

> [!WARNING]  
> Please replace `<NwA>` with your local network address e.g. `192.168.178.0` and replace `<SM>` with your subnet prefix e.g. `/24` for `255.255.255.0`

```bash
sudo ufw allow from <NwA>/<SM> to any port 22 comment 'SSH'; \
sudo ufw allow in 80/tcp comment 'Webpanel HTTP'; \
sudo ufw allow in 443/tcp comment 'Webpanel HTTPS'
```

Enable and reload your Firewall:

```bash
sudo ufw enable; \
sudo ufw reload
```

### Setup Apache2 Web Server

> [!WARNING]  
> Please replace the following placeholder:
>
> `<domain>` = your hostname or domain name
>
> `<rootMail>` = E-Mail address of your root account
>
> `<sslCert>` = SSL Certificate File (\*.crt or \*.pem)
>
> `<sslKey>` = SSL Key File (\*.key or \*.pem)


Create directory structure:

```bash
mkdir -p /var/www/<domain>/public_html; \
mkdir /var/www/<domain>/logs; \
mkdir /var/www/<domain>/public_server
```

Create virtual host configuration:

```bash
vim /etc/apache2/sites-available/obsidian-panel.conf
```

Insert the following configuration:

> [!TIP]
> This configuration template rewrites http requests on port 80 to https requests on port 443.

```bash
<VirtualHost *:80>
    ServerName <domain>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^/?(.*) https://%{SERVER_NAME}/$1 [R=301,L]
</VirtualHost>

<VirtualHost *:443>
    ServerAdmin <rootMail>
    ServerName <domain>
    DocumentRoot /var/www/<domain>/public_html/

    SSLEngine on
    SSLCertificateFile <sslCert>
    SSLCertificateKeyFile <sslKey>

    ErrorLog /var/www/<domain>/logs/error.log
    CustomLog /var/www/<domain>/logs/access.log combined

    <Directory /var/www/<domain>/public_html/>
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

Enable your configuration and reload the Apache2 Web Server:

```bash
a2ensite obsidian-panel.conf; \
systemctl reload apache2
```

### Download and install Obsidian Panel

> [!WARNING]  
> Please replace `<domain>` with your hostname or domain name e.g. `panel.pinoleekz.de`

Download the latest Obsidian Panel version from my GitHub, extract all files and delete unnecessary files:

```bash
WEBROOT="/var/www/<domain>/public_html"; \
wget -O "$WEBROOT/obsidian-panel.zip" "https://github.com/PIN0L33KZ/obsidian-panel/releases/download/v.1.0.2/obsidian-panel_initial-release-1.0.2.zip" && \
unzip "$WEBROOT/obsidian-panel.zip" -d "$WEBROOT" && \
rm "$WEBROOT/obsidian-panel.zip" && \
mv "$WEBROOT/obsidian-panel-main/"* "$WEBROOT" && \
rm -r "$WEBROOT/obsidian-panel-main"; \
unset WEBROOT
```

Set directory permissions:

```bash
chown www-data /var/www/<domain> -R; \
chmod 755 /var/www/<domain> -R
```

### Configure your Obsidian Panel

Copy the sample config file:

```bash
cp data/config-sample.php data/config.php
```

Edit the following line in `data/config.php` to reflect your Server’s IP-Address:


> [!WARNING]  
> Use `127.0.0.1` if the Panel and the Minecraft Server are running on the same Machine

```bash
define('KT_LOCAL_IP', '127.0.0.1');
```

Open your Obsidian Panel instance via your Browser:

```bash
https://<domain>/install.php
```

Follow the setup wizard in your Browser to create your Administrator Account.

### Clean up

Remove the installation wizard to enhance security:

```bash
rm /var/www/<domain>/public_html_install.php
```
