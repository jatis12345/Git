# PANDUAN KEAMANAN (SECURITY GUIDE)

## ⚠️ PENTING: Apakah Website Ini Aman di cPanel?

**Jawaban Singkat**: Website HTML/CSS/JS seperti ini **RELATIF AMAN** namun ada **KELEMAHAN** yang perlu diperbaiki untuk produksi.

---

## 🔴 KELEMAHAN SAAT INI (Current Vulnerabilities)

### 1. **Contact Form Tidak Aman - GAMPANG DIBOBOL!**

**Masalah:**
- Form contact hanya menggunakan JavaScript di browser
- **Bisa di-bypass dengan mudah** oleh attacker
- CAPTCHA bisa dilewati dengan disable JavaScript
- Email tidak benar-benar terkirim secara otomatis

**Solusi:** Gunakan backend PHP (lihat bagian "Solusi Keamanan" di bawah)

### 2. **Rate Limiting Hanya di Client-Side**

**Masalah:**
- Limit 3x percobaan CAPTCHA hanya di browser
- Attacker bisa refresh halaman atau clear cookies untuk bypass
- Tidak ada perlindungan dari spam bot

**Solusi:** Implementasi rate limiting di server-side

### 3. **Email Exposure**

**Masalah:**
- Email `info@infobit.co.id` terlihat langsung di HTML
- Bisa di-scrape oleh spam bots

**Solusi:** Gunakan contact form dengan PHP (email hidden di server)

---

## ✅ SOLUSI KEAMANAN UNTUK cPANEL

Saya telah membuat file `contact_handler.php` yang AMAN untuk production. File ini includes:

### ✅ Fitur Keamanan:

1. **Server-Side Validation**
   - Validasi email format
   - Validasi phone number
   - Sanitasi semua input untuk prevent XSS/SQL Injection

2. **Rate Limiting (Server-Side)**
   - Track IP address
   - Maximum 5 requests per 15 menit
   - Automatic block jika spam terdeteksi

3. **Session-Based CAPTCHA**
   - CAPTCHA tersimpan di server session
   - Tidak bisa di-bypass dari client
   - 3x attempts per session

4. **CSRF Protection**
   - Token-based validation
   - Prevent cross-site request forgery

5. **Email Security**
   - Email header injection prevention
   - HTML email dengan sanitasi
   - SPF/DKIM validation support

6. **IP Logging & Monitoring**
   - Log semua submission attempts
   - Track suspicious activity
   - Blacklist repeat offenders

---

## 📋 CHECKLIST KEAMANAN UNTUK CPANEL

### Sebelum Upload ke cPanel:

- [ ] Install SSL Certificate (HTTPS)
- [ ] Setup file `contact_handler.php`
- [ ] Set correct file permissions (644 untuk PHP, 755 untuk folder)
- [ ] Buat folder `logs/` untuk security logging
- [ ] Enable PHP sessions di cPanel
- [ ] Setup email account di cPanel untuk SMTP

### File Permissions (PENTING!):

```bash
chmod 644 index.html
chmod 644 css/style.css
chmod 644 js/script.js
chmod 644 contact_handler.php
chmod 755 logs/
chmod 644 logs/.htaccess  # Protect logs from web access
```

### Di cPanel:

1. **Enable ModSecurity** (Web Application Firewall)
   - cPanel → Security → ModSecurity

2. **Setup Hotlink Protection**
   - Prevent bandwidth theft

3. **Enable IP Blocker**
   - Block known malicious IPs

4. **Install Let's Encrypt SSL**
   - cPanel → Security → SSL/TLS Status
   - Enable HTTPS Redirect

5. **Configure PHP Settings**
   - Disable dangerous functions: `exec`, `shell_exec`, `system`
   - Set `upload_max_filesize` limit
   - Enable `open_basedir` restriction

6. **Setup Email SPF/DKIM**
   - Prevent email spoofing
   - cPanel → Email → Authentication

---

## 🛡️ ADDITIONAL SECURITY LAYERS

### 1. Add .htaccess Protection

File: `.htaccess` (di root folder)

```apache
# Prevent directory listing
Options -Indexes

# Protect PHP files
<FilesMatch "\.php$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Allow only contact_handler.php to be accessed
<Files "contact_handler.php">
    Allow from all
</Files>

# Block suspicious requests
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{QUERY_STRING} (eval\(|base64_|shell_exec) [NC,OR]
    RewriteCond %{QUERY_STRING} (<|%3C).*script.*(>|%3E) [NC,OR]
    RewriteCond %{QUERY_STRING} GLOBALS(=|\[|\%[0-9A-Z]{0,2}) [OR]
    RewriteCond %{QUERY_STRING} _REQUEST(=|\[|\%[0-9A-Z]{0,2})
    RewriteRule .* - [F]
</IfModule>

# Force HTTPS
RewriteCond %{HTTPS} off
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
```

### 2. Setup Google reCAPTCHA v3

Untuk security lebih kuat, ganti CAPTCHA matematika dengan Google reCAPTCHA:

1. Daftar di https://www.google.com/recaptcha/admin
2. Dapatkan Site Key dan Secret Key
3. Implementasi di contact form

### 3. Enable CloudFlare (GRATIS!)

CloudFlare provides:
- DDoS Protection
- Bot Management
- Rate Limiting
- Web Application Firewall (WAF)
- CDN untuk speed

Setup:
1. Daftar di cloudflare.com
2. Add your domain
3. Update nameservers di domain registrar
4. Enable "Under Attack Mode" jika ada spam

---

## 🚨 MONITORING & MAINTENANCE

### 1. Regular Security Checks

```bash
# Check logs untuk suspicious activity
tail -f logs/contact_form.log

# Monitor failed attempts
grep "CAPTCHA failed" logs/contact_form.log | wc -l
```

### 2. Update Checklist

- [ ] Update PHP version regularly
- [ ] Monitor security logs weekly
- [ ] Backup website files monthly
- [ ] Check SSL certificate expiry
- [ ] Review blocked IPs quarterly

### 3. Emergency Response

Jika website diserang:
1. Enable "Maintenance Mode" di cPanel
2. Check security logs
3. Block attacker IPs
4. Report ke hosting support
5. Update security measures

---

## 📊 TINGKAT KEAMANAN

### Current (HTML/JS Only):
🔴 **Rating: 3/10** - Mudah di-bypass, no real protection

### Dengan PHP Backend:
🟡 **Rating: 6/10** - Basic protection, suitable for small business

### Dengan PHP + reCAPTCHA + CloudFlare:
🟢 **Rating: 9/10** - Enterprise-level protection

---

## 💡 REKOMENDASI

Untuk PT INFOBIT CIPTA MANDIRI, saya recommend:

1. **Minimal**: Gunakan `contact_handler.php` (sudah included)
2. **Better**: Tambah Google reCAPTCHA v3
3. **Best**: CloudFlare + reCAPTCHA + Email service (SendGrid/Mailgun)

---

## 📞 TECHNICAL SUPPORT

Jika ada pertanyaan tentang security implementation, hubungi:
- Hosting provider support (untuk cPanel issues)
- Web developer untuk custom security features

---

**Note**: File ini berisi informasi sensitif tentang security. **JANGAN upload ke public repository!**
