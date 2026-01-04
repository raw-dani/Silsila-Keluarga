# 🧪 Panduan Testing Produksi Aplikasi Silsila Keluarga

Panduan lengkap untuk testing aplikasi dalam environment production menggunakan WAMP Server dengan virtual domain sebelum deploy ke CyberPanel.

## 🎯 **Prerequisites**

### **Software Required:**
- **WAMP Server** (64-bit) - https://www.wampserver.com/
- **Node.js** 16+ - https://nodejs.org/
- **Composer** - https://getcomposer.org/
- **Git** - https://git-scm.com/

### **System Requirements:**
- Windows 10/11
- RAM: 4GB+ recommended
- Disk space: 2GB+ free

---

## 🚀 **Setup WAMP Server**

### **Step 1: Install WAMP Server**

1. **Download WAMP Server 64-bit** dari situs resmi
2. **Install dengan default settings**
3. **Start WAMP Server** (icon hijau di system tray)
4. **Verify installation:**
   - PHP: `http://localhost/?phpinfo=1`
   - MySQL: Via phpMyAdmin `http://localhost/phpmyadmin`

### **Step 2: Setup Virtual Domain**

#### **2.1 Edit hosts file**
```bash
# Buka Notepad sebagai Administrator
# Edit file: C:\Windows\System32\drivers\etc\hosts

# Tambahkan line berikut di akhir file:
127.0.0.1    tamin-supirah.googo.my.id
```

#### **2.2 Configure Virtual Host di Apache**

1. **Buka WAMP Server**
2. **Klik icon WAMP** → Apache → httpd-vhosts.conf

3. **Tambahkan konfigurasi virtual host:**
```apache
# Tambahkan di akhir file httpd-vhosts.conf:

<VirtualHost *:80>
    ServerName tamin-supirah.googo.my.id
    DocumentRoot "c:/Users/EIKON/Documents/PWA/silsila-keluarga/backend/public"

    <Directory "c:/Users/EIKON/Documents/PWA/silsila-keluarga/backend/public">
        Options Indexes FollowSymLinks
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog "logs/tamin-supirah.googo.my.id_error.log"
    CustomLog "logs/tamin-supirah.googo.my.id_access.log" common
</VirtualHost>
```

4. **Edit httpd.conf utama:**
   - Klik WAMP icon → Apache → httpd.conf
   - Uncomment line: `Include conf/extra/httpd-vhosts.conf`

5. **Restart WAMP Server** (icon hijau → Restart All Services)

6. **Test virtual domain:**
   - Buka browser: `http://tamin-supirah.googo.my.id`
   - Harus muncul halaman login aplikasi Vue.js (bukan Laravel default)

### **Step 3: Setup Database MySQL**

#### **3.1 Create Database**
```sql
-- Via phpMyAdmin (http://localhost/phpmyadmin)
-- Atau via MySQL command line:

CREATE DATABASE silsila_keluarga CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'silsila_user'@'localhost' IDENTIFIED BY 'password123';
GRANT ALL PRIVILEGES ON silsila_keluarga.* TO 'silsila_user'@'localhost';
FLUSH PRIVILEGES;
```

---

## 📦 **Setup Aplikasi**

### **Step 1: Setup Project (Current Folder)**

**🚀 Quick Setup dengan Script Otomatis:**

```bash
# Jalankan script setup otomatis (recommended)
# Double-click file: setup-wamp-test.bat
# Atau jalankan via command line:
setup-wamp-test.bat

# Script akan:
# ✅ Check prerequisites (PHP, Composer, Node.js, npm)
# ✅ Setup Laravel environment (.env)
# ✅ Install PHP & Node.js dependencies
# ✅ Generate Laravel app key
# ✅ Build Vue.js frontend
# ✅ Copy build files ke Laravel public
# ✅ Clear Laravel cache
```

**📋 Atau Setup Manual:**

```bash
# Kita akan menggunakan folder saat ini
# Folder project: C:\Users\EIKON\Documents\PWA\silsila-keluarga

# Setup backend (Laravel)
copy backend\.env.example backend\.env
```

### **Step 2: Configure Environment**

#### **2.1 Edit backend/.env**
```env
APP_NAME="Silsila Keluarga"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://tamin-supirah.googo.my.id

# Database Configuration
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=silsila_keluarga
DB_USERNAME=silsila_user
DB_PASSWORD=password123

# Cache Configuration
CACHE_STORE=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync

# Mail Configuration (untuk testing)
MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@tamin-supirah.googo.my.id"
MAIL_FROM_NAME="${APP_NAME}"

# Vite Configuration (for production builds)
VITE_APP_NAME="${APP_NAME}"
VITE_APP_ENV="${APP_ENV}"
VITE_APP_URL="${APP_URL}"
```

#### **2.2 Setup Frontend Environment (Optional)**
```bash
# Copy frontend environment example
copy frontend\.env.example frontend\.env

# Edit frontend\.env jika perlu custom configuration
# NOTE: Frontend menggunakan dynamic domain detection
# VITE_APP_URL di-comment out karena API calls menggunakan window.location
# Tidak perlu rebuild jika ganti domain
```

#### **2.2 Generate Application Key**
```bash
cd backend

# Gunakan PHP dari WAMP (recommended)
"C:\wamp64\bin\php\php8.3.14\php.exe" artisan key:generate

# Atau jika PHP sudah di PATH:
php artisan key:generate
```

### **Step 3: Install Dependencies**

#### **3.1 Install PHP Dependencies**
```bash
cd backend

# Gunakan Composer dengan PHP path lengkap (recommended)
"C:\wamp64\bin\php\php8.3.14\php.exe" composer.phar install --no-dev --optimize-autoloader

# Atau jika Composer sudah di PATH global:
composer install --no-dev --optimize-autoloader

# Atau gunakan composer.phar langsung:
php composer.phar install --no-dev --optimize-autoloader

# Jika composer.phar tidak ada, download dulu:
# curl -sS https://getcomposer.org/installer | php
# php composer.phar install --no-dev --optimize-autoloader
```

#### **3.2 Install Node.js Dependencies**
```bash
cd ../frontend
npm install
```

### **Step 4: Setup Database**

#### **4.1 Run Migrations**
```bash
cd ../backend

# Gunakan PHP dari WAMP (recommended)
"C:\wamp64\bin\php\php8.3.14\php.exe" artisan migrate --force

# Atau jika PHP sudah di PATH:
php artisan migrate --force
```

#### **4.2 Seed Database (Optional)**

# Gunakan PHP dari WAMP (recommended)
"C:\wamp64\bin\php\php8.3.14\php.exe" artisan db:seed

# Atau jika PHP sudah di PATH:
php artisan db:seed
```

#### **4.3 Create Storage Link**
```bash
# Gunakan PHP dari WAMP (recommended)
"C:\wamp64\bin\php\php8.3.14\php.exe" artisan storage:link

# Atau jika PHP sudah di PATH:
php artisan storage:link

# Jika error "link already exists":
# Hapus link yang ada dulu, lalu jalankan ulang:
rmdir backend\public\storage
php artisan storage:link
```

### **Step 5: Build Frontend**

#### **5.1 Build Vue.js untuk Production**
```bash
cd ../frontend

# Build untuk production (menggunakan Vite langsung)
npm run build

# Atau build dengan environment tertentu (Windows compatible):
npm run build:prod    # Production build dengan NODE_ENV=production
npm run build:staging # Staging build dengan NODE_ENV=staging

# Manual environment variable (jika npm run gagal):
set NODE_ENV=production
npm run build

# Copy hasil build ke Laravel public directory
# Hapus assets folder yang ada dulu untuk memastikan copy bersih
if exist "..\backend\public\assets" rmdir /S /Q "..\backend\public\assets"

# Menggunakan xcopy untuk copy semua files termasuk subfolder assets
xcopy /E /I /Y dist\* ..\backend\public\

# Atau jika xcopy gagal, gunakan robocopy:
# robocopy dist ..\backend\public /E /IS /IT
```

#### **5.2 Clear Laravel Cache**
```bash
cd ../backend

# Gunakan PHP dari WAMP (recommended)
"C:\wamp64\bin\php\php8.3.14\php.exe" artisan config:clear
"C:\wamp64\bin\php\php8.3.14\php.exe" artisan route:clear
"C:\wamp64\bin\php\php8.3.14\php.exe" artisan cache:clear
"C:\wamp64\bin\php\php8.3.14\php.exe" artisan view:clear

# Atau jika PHP sudah di PATH:
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
```

---

## 🧪 **Testing Production Setup**

### **Step 1: Basic Functionality Test**

#### **1.1 Test Laravel Installation**
```bash
# Test artisan commands
"C:\wamp64\bin\php\php8.3.14\php.exe" artisan --version
"C:\wamp64\bin\php\php8.3.14\php.exe" artisan route:list | findstr "api/"

# Test database connection
"C:\wamp64\bin\php\php8.3.14\php.exe" artisan tinker
DB::connection()->getPdo();
exit;
```

#### **1.2 Test Web Access**
- Buka browser: `http://tamin-supirah.googo.my.id`
- Harus muncul halaman login aplikasi

#### **1.3 Test API Endpoints**
```bash
# Test public API
curl -X GET "http://tamin-supirah.googo.my.id/api/slider-data" -H "Accept: application/json"

# Test family name API
curl -X GET "http://tamin-supirah.googo.my.id/api/family-name" -H "Accept: application/json"
```

### **Step 2: User Authentication Test**

#### **2.1 Create Admin User**
```bash
# Gunakan PHP dari WAMP (recommended)
"C:\wamp64\bin\php\php8.3.14\php.exe" artisan tinker

use App\Models\User;
use Illuminate\Support\Facades\Hash;

User::create([
    'name' => 'Administrator',
    'email' => 'admin@tamin-supirah.googo.my.id',
    'password' => Hash::make('password123'),
    'role' => 'admin'
]);

exit;
```

#### **2.2 Test Login**
- Buka: `http://tamin-supirah.googo.my.id/login`
- Login dengan:
  - Email: `admin@tamin-supirah.googo.my.id`
  - Password: `password123`

### **Step 3: Application Features Test**

#### **3.1 Test Dashboard**
- ✅ Hero section menampilkan nama keluarga
- ✅ Stats cards berfungsi
- ✅ Navigation menu lengkap

#### **3.2 Test Family Tree**
- ✅ Pohon keluarga terlihat
- ✅ Header menampilkan nama keluarga
- ✅ Search functionality berjalan

#### **3.3 Test Admin Panel**
- ✅ Admin Management page accessible
- ✅ Family name form terlihat
- ✅ Update nama keluarga berhasil

#### **3.4 Test API Integration**
- ✅ Tidak ada CORS errors
- ✅ Semua API calls berhasil
- ✅ Frontend-backend communication lancar

### **Step 4: Performance Test**

#### **4.1 Check Load Times**
- Page load time < 3 seconds
- API response time < 1 second
- Images load properly

#### **4.2 Test Error Handling**
- 404 pages handled gracefully
- API errors show proper messages
- Form validation works

---

## 🔧 **Troubleshooting**

### **Error: Virtual Domain Not Working**
```bash
# Check hosts file
type C:\Windows\System32\drivers\etc\hosts | findstr "tamin-supirah"

# Check Apache virtual host config
# Restart WAMP Server
```

### **Error: Database Connection Failed**
```bash
# Test MySQL connection
mysql -u silsila_user -p silsila_keluarga

# Check .env database settings
type backend\.env | findstr "DB_"
```

### **Error: API Returns 404**
```bash
# Check Laravel routes
php artisan route:list | findstr "api/"

# Check Apache rewrite module enabled
# WAMP: Apache modules → rewrite_module enabled
```

### **Error: Frontend Not Loading**
```bash
# Check if dist files copied correctly
dir backend\public\assets\

# Clear browser cache
# Hard refresh: Ctrl+F5
```

### **Error: Permission Issues**
```bash
# Fix storage permissions
icacls backend\storage /grant "Everyone":F /T

# Fix bootstrap cache permissions
icacls backend\bootstrap\cache /grant "Everyone":F /T
```

### **Error: Vite Build Fails (Syntax Error)**
```bash
# Error: SyntaxError: missing ) after argument list
# Penyebab: Vite binary menggunakan bash syntax di Windows

# Solusi: Gunakan npm run build langsung
cd frontend
npm run build

# Jika masih error, clear cache dan reinstall
npm cache clean --force
rmdir node_modules /S /Q
npm install
npm run build

# Atau gunakan npx
npx vite build
```

### **Error: Node.js Version Issues**
```bash
# Jika menggunakan Node.js v22+, mungkin ada compatibility issues

# Check Node.js version
node --version

# Jika v22+, downgrade ke v20 atau v18 untuk stability
# Download dari: https://nodejs.org/

# Atau gunakan nvm-windows untuk multiple versions
# https://github.com/coreybutler/nvm-windows
```

---

## 📋 **Production Readiness Checklist**

### **Laravel Backend:**
- [x] Environment configured
- [x] Database connected
- [x] Migrations run successfully
- [x] Storage link created
- [x] Cache cleared
- [ ] Admin user created
- [ ] API endpoints accessible

### **Vue.js Frontend:**
- [x] Dependencies installed
- [x] Build successful
- [x] Files copied to public directory
- [ ] No console errors
- [ ] All pages load correctly

### **Web Server:**
- [x] Virtual domain configured
- [x] Apache virtual host setup
- [ ] SSL certificate (optional for local)
- [ ] Domain accessible

### **Database:**
- [x] MySQL database created
- [x] User permissions set
- [ ] Data seeded (optional)
- [ ] Backup created

### **Application Features:**
- [ ] Login/logout works
- [ ] Dashboard displays correctly
- [ ] Family tree functional
- [ ] Admin panel accessible
- [ ] Family name update works
- [ ] All CRUD operations functional

---

## 🚀 **Deploy ke CyberPanel**

### **Setelah Testing Berhasil:**

1. **Backup Database:**
```bash
mysqldump -u silsila_user -p silsila_keluarga > silsila_backup.sql
```

2. **Zip Project Files:**
```bash
# Zip seluruh project
# Upload ke CyberPanel via File Manager
```

3. **Ikuti Panduan INSTALLATION.md** untuk setup di CyberPanel

4. **Restore Database:**
```bash
# Upload backup SQL ke CyberPanel database
# Import via phpMyAdmin
```

5. **Update Production Settings:**
- Change APP_ENV to `production`
- Update APP_URL
- Configure SSL
- Set proper CORS origins

---

## 📞 **Support & Monitoring**

### **Logs Location:**
```
Laravel Logs: backend\storage\logs\laravel.log
Apache Error: C:\wamp64\logs\apache_error.log
MySQL Error: C:\wamp64\logs\mysql.log
```

### **Performance Monitoring:**
- Browser DevTools → Network tab
- Laravel Debugbar (jika installed)
- MySQL slow query log

---

**🎊 Setup testing production selesai! Test aplikasi secara menyeluruh sebelum deploy ke CyberPanel.**
