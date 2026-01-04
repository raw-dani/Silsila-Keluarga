# 📚 Panduan Instalasi Aplikasi Silsila Keluarga di CyberPanel

Panduan lengkap untuk menginstall aplikasi **Silsila Keluarga** (Laravel + Vue.js) di CyberPanel.

## 🎯 **Persyaratan Sistem**

### **Minimum Requirements:**
- **OS:** CentOS 7/8, Ubuntu 18.04/20.04/22.04
- **RAM:** 1GB (Recommended: 2GB+)
- **Storage:** 5GB free space
- **PHP:** 8.1 atau 8.2
- **MySQL/MariaDB:** 5.7+ / 10.3+
- **Node.js:** 16+ (untuk build frontend)
- **Composer:** Latest version

### **CyberPanel Requirements:**
- CyberPanel v2.3+
- Website created dengan PHP 8.1/8.2
- SSL certificate (recommended)
- Database MySQL/MariaDB

---

## 🚀 **Langkah Instalasi**

### **Step 1: Persiapan di CyberPanel**

#### **1.1 Buat Website Baru**
```
1. Login ke CyberPanel
2. Navigate ke: Websites → Create Website
3. Isi informasi:
   - Domain: silsilakeluarga.com (atau domain Anda)
   - PHP Version: 8.1 atau 8.2
   - Package: Default atau sesuai kebutuhan
4. Klik Create Website
```

#### **1.2 Setup Database**
```
1. Navigate ke: Databases → Create Database
2. Isi informasi:
   - Database Name: silsila_keluarga
   - Database User: silsila_user
   - Password: [password_kuat_anda]
3. Klik Create Database
4. Catat informasi database untuk konfigurasi .env
```

#### **1.3 Setup SSL Certificate (Recommended)**
```
1. Navigate ke: Websites → List Websites
2. Klik Manage → SSL → Issue SSL
3. Pilih: Issue Let's Encrypt SSL
4. Tunggu proses selesai (biasanya 5-10 menit)
```

### **Step 2: Upload dan Setup File Aplikasi**

#### **2.1 Upload Project Files**
```
1. Download project dari GitHub:
   git clone https://github.com/raw-dani/Silsila-Keluarga.git

2. Zip project menjadi silsila-keluarga.zip

3. Upload via CyberPanel:
   - Navigate ke: File Manager
   - Upload silsila-keluarga.zip ke /home/[domain]/public_html/

4. Ekstrak file:
   - Klik kanan pada silsila-keluarga.zip
   - Extract Here

5. Pindahkan isi folder ke public_html:
   - Move semua file dari silsila-keluarga/ ke public_html/
   - Delete folder kosong silsila-keluarga/

   **Catatan:** Pastikan Laravel di-root public_html, bukan di subfolder backend/
```

#### **2.2 Setup File Permissions**
```bash
# Via SSH atau Terminal di CyberPanel:
cd /home/[domain]/public_html

# Set proper ownership
chown -R [username]:[username] .

# Set proper permissions
find . -type f -exec chmod 644 {} \;
find . -type d -exec chmod 755 {} \;

# Storage permissions for Laravel
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/

# Make artisan executable
chmod +x artisan
```

### **Step 3: Setup Backend (Laravel)**

#### **3.1 Install Composer Dependencies**
```bash
# Via SSH (Terminal di CyberPanel):
cd /home/[domain]/public_html

# Install Composer jika belum ada
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

# Install dependencies
composer install --no-dev --optimize-autoloader
```

#### **3.2 Konfigurasi Environment**
```bash
# Copy .env example
cp .env.example .env

# Edit .env file
nano .env

# Pastikan APP_URL sesuai dengan domain Anda
# Contoh untuk domain tamin-supirah.googo.my.id:
APP_URL=https://tamin-supirah.googo.my.id
```

**Isi .env file:**
```env
APP_NAME="Silsila Keluarga"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://[domain-anda]

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=silsila_keluarga
DB_USERNAME=silsila_user
DB_PASSWORD=[password-database-anda]

# Cache & Session
CACHE_STORE=database
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Mail Configuration (opsional)
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@[domain-anda]"
MAIL_FROM_NAME="${APP_NAME}"

# Vite Configuration
VITE_APP_NAME="${APP_NAME}"
```

#### **3.3 Generate Application Key**
```bash
# Generate app key
php artisan key:generate
```

#### **3.4 Setup Database**
```bash
# Jalankan migrations
php artisan migrate --force

# Jika ada error dengan migration tertentu, jalankan satu per satu:
php artisan migrate:status  # Cek status migrations
php artisan migrate --step   # Jalankan satu per satu jika ada masalah

# (Opsional) Seed data awal
php artisan db:seed
```

#### **3.5 Setup Storage Link**
```bash
# Create storage symlink
php artisan storage:link
```

#### **3.6 Clear & Cache Configuration**
```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Cache untuk production
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### **Step 4: Setup Frontend (Vue.js)**

#### **4.1 Install Node.js Dependencies**
```bash
# Install Node.js jika belum ada (via CyberPanel atau manual)
# CyberPanel biasanya sudah include Node.js

# Install dependencies
npm install

# Build untuk production
npm run build
```

#### **4.2 Verify Build**
```
# Check if dist/ folder created
ls -la dist/

# Should contain:
# - assets/
# - index.html
# - etc.
```

### **Step 5: Konfigurasi Web Server**

#### **5.1 Setup Document Root**
```
Di CyberPanel:
1. Websites → List Websites → [domain-anda]
2. Manage → Configurations → vHost Conf
3. Edit nginx.conf atau openlitespeed.conf:

# Pastikan document root menunjuk ke public/
root /home/[domain]/public_html/public;

# Setup index files
index index.php index.html index.htm;

# Setup PHP
location ~ \.php$ {
    try_files $uri =404;
    fastcgi_pass unix:/var/run/php-fpm.sock;
    fastcgi_index index.php;
    fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
    include fastcgi_params;
}
```

#### **5.2 Setup URL Rewrite**
```
# Tambahkan di vHost configuration:

# Laravel URL Rewrite
location / {
    try_files $uri $uri/ /index.php?$query_string;
}

# Exclude public assets from Laravel routing
location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
    expires 1y;
    add_header Cache-Control "public, immutable";
}
```

#### **5.3 Restart Web Server**
```
# Via CyberPanel:
1. Navigate ke: Services → Services Status
2. Restart OpenLiteSpeed/LiteSpeed
3. Restart PHP-FPM
```

### **Step 6: Setup Cron Jobs (Opsional)**

#### **6.1 Setup Laravel Scheduler**
```bash
# Via SSH:
crontab -e

# Tambahkan line berikut:
* * * * * cd /home/[domain]/public_html && php artisan schedule:run >> /dev/null 2>&1
```

#### **6.2 Setup Queue Worker (jika menggunakan queue)**
```bash
# Install Supervisor atau gunakan cron untuk queue
* * * * * cd /home/[domain]/public_html && php artisan queue:work --sleep=3 --tries=3 >> /dev/null 2>&1
```

### **Step 7: Testing Instalasi**

#### **7.1 Test Database Connection**
```bash
# Via SSH:
php artisan tinker

# Di dalam tinker:
DB::connection()->getPdo();
exit;
```

#### **7.2 Test Application**
```
1. Buka browser: https://[domain-anda]
2. Harus muncul halaman login aplikasi
3. Coba login dengan akun admin default (jika ada seeder)
```

#### **7.3 Test API Endpoints**
```
# Test via browser atau curl:
https://[domain-anda]/api/family-name
https://[domain-anda]/api/user (setelah login)
```

### **Step 8: Setup Admin User**

#### **8.1 Buat Admin User Pertama**
```bash
# Via SSH:
php artisan tinker

# Di dalam tinker:
use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Administrator',
    'email' => 'admin@[domain-anda]',
    'password' => Hash::make('password123'),
    'role' => 'admin'
]);

exit;
```

#### **8.2 Login Admin**
```
1. Buka: https://[domain-anda]/login
2. Login dengan:
   - Email: admin@[domain-anda]
   - Password: password123
3. Ganti password setelah login pertama
```

### **Step 9: Backup & Maintenance**

#### **9.1 Setup Backup Database**
```bash
# Buat script backup di /home/[domain]/backup.sh
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
mysqldump -u [db_user] -p[db_password] silsila_keluarga > /home/[domain]/backups/db_$DATE.sql
```

#### **9.2 Setup Cron untuk Backup**
```bash
# Tambahkan ke crontab:
0 2 * * * /home/[domain]/backup.sh  # Backup setiap hari jam 2 pagi
```

---

## 🔧 **Troubleshooting**

### **Error: 500 Internal Server Error**
```bash
# Check Laravel logs:
tail -f storage/logs/laravel.log

# Check PHP-FPM error:
tail -f /usr/local/lsws/logs/php-fpm.log

# Check file permissions:
ls -la storage/
ls -la bootstrap/cache/

# Check .env file:
cat .env | grep -E "(APP_KEY|DB_|CACHE_)"

# Test database connection:
php artisan tinker
DB::connection()->getPdo();
exit;

# Clear all caches:
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# Check PHP version and extensions:
php -v
php -m | grep -E "(pdo|mysql|mbstring|openssl)"
```

### **Error: Database Connection Failed**
```bash
# Test database connection:
php artisan tinker
DB::connection()->getPdo();

# Check .env database settings
cat .env | grep DB_
```

### **Error: Assets Not Loading**
```bash
# Clear cache and rebuild:
php artisan cache:clear
php artisan config:clear
npm run build

# Check file permissions:
chmod -R 755 public/
```

### **Error: SSL Certificate Issues**
```
# Via CyberPanel:
1. Websites → [domain] → SSL
2. Re-issue SSL certificate
3. Check DNS settings
```

### **Error: Migration ENUM Column Modification**
```
Error: SQLSTATE[HY000]: General error: 1 near "MODIFY": syntax error
```

**Penyebab:** Migration menggunakan syntax MySQL tapi Laravel mendeteksi SQLite.

**Solusi:**
```bash
# 1. Pastikan .env database configuration benar:
cat .env | grep DB_

# 2. Test koneksi database:
php artisan tinker
DB::connection()->getPdo();
exit;

# 3. Jika masih error, reset migrations dan jalankan ulang:
php artisan migrate:reset
php artisan migrate

# 4. Atau jalankan manual SQL dari backend/create_table.sql
```

### **Error: Duplicate Column Name**
```
Error: SQLSTATE[HY000]: General error: 1 duplicate column name: [column_name]
```

**Penyebab:** Migration mencoba menambah kolom yang sudah ada.

**Solusi:**
```bash
# 1. Cek status migrations:
php artisan migrate:status

# 2. Jika migration sudah dijalankan sebelumnya, skip atau hapus file migration yang duplikat
# 3. Atau rollback migration spesifik:
php artisan migrate:rollback --step=1

# 4. Jalankan ulang migrations:
php artisan migrate
```

### **Error: Database Connection Failed**
```bash
# Pastikan database credentials di .env benar
# Pastikan database user punya permissions penuh
# Test koneksi:
php artisan tinker
DB::connection()->getPdo();
exit;
```

### **Performance Issues**
```bash
# Enable OPcache:
php -m | grep opcache

# Enable caching:
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 📞 **Support & Help**

Jika mengalami masalah instalasi:

1. **Cek Logs:**
   - Laravel: `storage/logs/laravel.log`
   - Web Server: `/usr/local/lsws/logs/error.log`
   - PHP-FPM: `/usr/local/lsws/logs/php-fpm.log`

2. **Common Issues:**
   - Pastikan PHP version 8.1+
   - Pastikan database credentials benar
   - Pastikan file permissions tepat
   - Pastikan SSL certificate valid

3. **Contact Support:**
   - Email: support@silsilakeluarga.com
   - GitHub Issues: https://github.com/raw-dani/Silsila-Keluarga/issues

---

## 🎉 **Post-Installation Checklist**

- [ ] Website dapat diakses via HTTPS
- [ ] Halaman login muncul dengan benar
- [ ] Admin user dapat login
- [ ] Dashboard menampilkan data
- [ ] Family Tree page berfungsi
- [ ] Nama keluarga dapat diubah via Admin panel
- [ ] Database migrations berhasil
- [ ] File uploads berfungsi
- [ ] SSL certificate aktif
- [ ] Backup script terjadwal

---

**🎊 Selamat! Aplikasi Silsila Keluarga telah berhasil diinstall di CyberPanel!**
