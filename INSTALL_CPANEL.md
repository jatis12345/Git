# PANDUAN INSTALL KE CPANEL

## 📋 Prerequisites

Sebelum upload, pastikan Anda memiliki:
- ✅ Akses ke cPanel hosting
- ✅ Domain sudah pointing ke hosting
- ✅ SSL Certificate (Let's Encrypt - gratis di cPanel)
- ✅ Email account sudah dibuat di cPanel

---

## 🚀 LANGKAH INSTALASI

### Step 1: Upload Files ke cPanel

1. **Login ke cPanel**
   - Buka: `https://yourdomain.com/cpanel`
   - Masukkan username dan password

2. **Buka File Manager**
   - cPanel → Files → File Manager
   - Navigate ke folder `public_html`

3. **Upload Semua Files**

   Upload file-file berikut ke `public_html`:
   ```
   public_html/
   ├── index.html
   ├── contact_handler.php
   ├── .htaccess
   ├── css/
   │   └── style.css
   ├── js/
   │   └── script.js
   └── logs/
       └── .htaccess
   ```

   **Cara Upload:**
   - Klik tombol "Upload" di File Manager
   - Select semua files atau buat ZIP dulu lalu extract
   - Tunggu sampai upload selesai

### Step 2: Set File Permissions

**PENTING!** Set permissions yang benar untuk keamanan:

1. Di File Manager, select file/folder
2. Klik "Permissions" atau klik kanan → "Change Permissions"

**Permissions yang benar:**
```
File/Folder                Permission      Numeric
─────────────────────────────────────────────────
index.html                 644             rw-r--r--
contact_handler.php        644             rw-r--r--
.htaccess                  644             rw-r--r--
css/                       755             rwxr-xr-x
css/style.css              644             rw-r--r--
js/                        755             rwxr-xr-x
js/script.js               644             rw-r--r--
logs/                      755             rwxr-xr-x
logs/.htaccess             644             rw-r--r--
```

**Cara set permission:**
- 644 untuk files = ☑ Read Owner, ☑ Write Owner, ☑ Read Group, ☑ Read World
- 755 untuk folders = ☑ Read Owner, ☑ Write Owner, ☑ Execute Owner, ☑ Read Group, ☑ Execute Group, ☑ Read World, ☑ Execute World

### Step 3: Configure Email di contact_handler.php

1. **Edit file contact_handler.php**
   - Di File Manager, klik kanan `contact_handler.php`
   - Pilih "Edit"

2. **Update Email Configuration** (Line 16):
   ```php
   define('RECIPIENT_EMAIL', 'info@infobit.co.id');
   ```
   Pastikan email ini sudah dibuat di cPanel!

3. **Save file** (Ctrl + S atau tombol Save)

### Step 4: Setup Email Account

1. **Create Email Account**
   - cPanel → Email → Email Accounts
   - Klik "Create"
   - Email: `info@infobit.co.id`
   - Password: (buat password yang kuat)
   - Mailbox Quota: Unlimited atau sesuai kebutuhan
   - Klik "Create"

2. **Setup SPF Record** (untuk prevent spam)
   - cPanel → Email → Email Deliverability
   - Klik "Manage" pada domain Anda
   - Pastikan SPF dan DKIM status: ✅ Valid

### Step 5: Install SSL Certificate (HTTPS)

1. **Install Let's Encrypt SSL** (GRATIS!)
   - cPanel → Security → SSL/TLS Status
   - Find your domain
   - Klik "Run AutoSSL"
   - Tunggu sampai selesai

2. **Verify SSL Installed**
   - Buka: `https://yourdomain.com`
   - Harus ada icon gembok 🔒 di browser

3. **Force HTTPS Redirect**
   - Sudah otomatis via `.htaccess`
   - Test: buka `http://yourdomain.com` (tanpa S)
   - Harus redirect otomatis ke `https://`

### Step 6: Test Website

1. **Test Homepage**
   - Buka: `https://yourdomain.com`
   - Cek semua section loading dengan baik
   - Test scroll animations
   - Test responsive (mobile view)

2. **Test Contact Form**

   **Test CAPTCHA:**
   - Scroll ke Contact Form
   - Isi semua field
   - **Test 1:** Jawab CAPTCHA SALAH → harus muncul "Sisa 2 percobaan"
   - **Test 2:** Jawab CAPTCHA SALAH lagi → harus muncul "Sisa 1 percobaan"
   - **Test 3:** Jawab CAPTCHA SALAH lagi → Form HARUS DIKUNCI!
   - **Test 4:** Refresh halaman, coba lagi dengan CAPTCHA BENAR

   **Test Email:**
   - Isi form dengan data valid
   - Jawab CAPTCHA dengan BENAR
   - Klik "Kirim Pesan"
   - Cek inbox di `info@infobit.co.id`
   - Email harus masuk dalam 1-2 menit

3. **Test Rate Limiting**
   - Submit form 6x dalam waktu cepat
   - Submit ke-6 harus ditolak dengan pesan rate limit

### Step 7: Enable Security Features

1. **Enable ModSecurity (Web Application Firewall)**
   - cPanel → Security → ModSecurity
   - Toggle ON
   - Klik "Enable"

2. **Setup IP Blocker** (Optional)
   - cPanel → Security → IP Blocker
   - Add IP addresses yang mencurigakan

3. **Enable Hotlink Protection**
   - cPanel → Security → Hotlink Protection
   - Enable untuk prevent bandwidth theft

### Step 8: Monitor Logs

1. **Access Security Logs**
   - cPanel → File Manager
   - Navigate ke `public_html/logs/`
   - Download `contact_form.log` untuk review

2. **Review Logs Regularly**
   ```
   Format log:
   timestamp|IP_address|status|message|date

   Status types:
   - SUCCESS: Email terkirim
   - RATE_LIMIT_EXCEEDED: Terlalu banyak request
   - CAPTCHA_FAILED: CAPTCHA salah
   - SPAM_DETECTED: Pesan spam
   - EMAIL_FAILED: Gagal kirim email
   ```

3. **Check Error Logs**
   - cPanel → Metrics → Errors
   - Review jika ada masalah

---

## 🔧 TROUBLESHOOTING

### ❌ Problem: Email tidak masuk

**Solution:**
1. Cek spam folder
2. Verify email account sudah dibuat di cPanel
3. Test PHP mail():
   - cPanel → Advanced → PHP Info
   - Cari "sendmail_path" - harus ada value
4. Check error logs di cPanel
5. Hubungi hosting support untuk enable mail function

### ❌ Problem: Form tidak submit / error 500

**Solution:**
1. Check file permissions (harus 644 untuk PHP)
2. Check PHP version:
   - cPanel → Software → Select PHP Version
   - Recommend: PHP 7.4 atau 8.0+
3. Check error logs
4. Pastikan folder `logs/` ada dan writable (755)

### ❌ Problem: Rate limiting tidak bekerja

**Solution:**
1. Pastikan folder `logs/` exists dan writable
2. Check permissions logs/ folder (755)
3. Check .htaccess di logs/ untuk protect access

### ❌ Problem: CAPTCHA selalu salah

**Solution:**
1. Clear browser cookies
2. Check PHP sessions enabled:
   - cPanel → Software → Select PHP Version
   - Extensions → enable "session"
3. Restart browser

### ❌ Problem: 403 Forbidden Error

**Solution:**
1. Check .htaccess syntax
2. Check file permissions
3. Check ModSecurity - might be blocking
4. Temporarily rename .htaccess to .htaccess.bak untuk test

### ❌ Problem: Images tidak muncul (Fortinet/APC)

**Solution:**
1. Images menggunakan external URLs (Wikipedia)
2. Check internet connection di server
3. Ganti dengan local images jika perlu:
   - Upload logo ke folder `images/`
   - Update src di index.html

---

## 🛡️ RECOMMENDED: Setup CloudFlare (GRATIS!)

CloudFlare provides FREE:
- ✅ DDoS Protection
- ✅ Bot Protection
- ✅ CDN (faster loading)
- ✅ SSL
- ✅ Analytics

**Setup Steps:**

1. **Sign up at cloudflare.com**
   - Create free account

2. **Add Your Website**
   - Enter domain: `infobit.co.id`
   - Select FREE plan

3. **Update Nameservers**
   - CloudFlare will give you 2 nameservers
   - Go to your domain registrar
   - Update nameservers to CloudFlare's

4. **Configure CloudFlare**
   - SSL/TLS → Full (Strict)
   - Security → Medium
   - Under Attack Mode (jika ada spam attack)

5. **Wait for DNS Propagation**
   - Usually 24-48 hours
   - Check status di CloudFlare dashboard

---

## 📊 POST-INSTALLATION CHECKLIST

Setelah install, verify semua ini:

- [ ] Website accessible via HTTPS
- [ ] HTTP redirect to HTTPS works
- [ ] All images loading
- [ ] Animations working
- [ ] Contact form submitting
- [ ] Email receiving at info@infobit.co.id
- [ ] CAPTCHA validation working
- [ ] Rate limiting working (test 6x submit)
- [ ] 3-attempt CAPTCHA lock working
- [ ] Mobile responsive view working
- [ ] SSL certificate valid (🔒 icon)
- [ ] ModSecurity enabled
- [ ] Logs folder protected (try access yourdomain.com/logs/)
- [ ] SPF/DKIM configured

---

## 📞 SUPPORT

**Technical Issues:**
- Hosting Support: Contact your hosting provider
- Email Issues: cPanel → Email → Email Deliverability
- Security Issues: Review SECURITY.md

**Website Issues:**
- Check error logs in cPanel
- Review contact_form.log
- Enable display_errors temporarily (only for debugging!)

---

## 🔄 MAINTENANCE

**Weekly:**
- [ ] Check contact_form.log for spam
- [ ] Review failed attempts
- [ ] Check email inbox

**Monthly:**
- [ ] Update file backups
- [ ] Check SSL certificate expiry
- [ ] Review security logs
- [ ] Clear old logs (keep last 30 days)

**Quarterly:**
- [ ] Update PHP version if needed
- [ ] Review and update IP blocklist
- [ ] Test all form validations
- [ ] Backup entire website

---

## ✅ SELESAI!

Website Anda sekarang LIVE dan SECURE! 🎉

**Next Steps:**
1. Share website URL dengan team
2. Test semua functionality
3. Monitor logs for first week
4. Setup Google Analytics (optional)
5. Submit to Google Search Console

**Website URL:** https://yourdomain.com

**Email:** info@infobit.co.id

---

Good luck! 🚀
