# 🔒 Panduan Setup Repository Private untuk Frontend

## 📋 **Situasi Saat Ini**
- Repository utama berisi **backend Laravel** (public)
- Folder `frontend/` di-exclude dari repository utama
- Frontend perlu repository terpisah yang **private**

## 🎯 **Tujuan**
- ✅ Backend Laravel tetap public
- ✅ Frontend Vue.js private & secure
- ✅ File sensitif (.env) tidak pernah di-commit
- ✅ Kemudahan development & deployment

---

## 📝 **Langkah-Langkah Setup**

### **1. Persiapan Repository**
```bash
# 1. Backup semua perubahan yang belum di-commit
git add .
git commit -m "Backup before splitting repositories"

# 2. Buat repository baru di GitHub untuk frontend
# Pergi ke https://github.com/new
# Repository name: silsila-keluarga-frontend
# Visibility: Private
# Jangan initialize dengan README, .gitignore, atau license
```

### **2. Setup Repository Frontend (Private)**
```bash
# 1. Inisialisasi git di folder frontend
cd frontend
git init

# 2. Rename branch utama ke main
git branch -M main

# 3. Add remote untuk repository private
git remote add origin https://github.com/YOUR_USERNAME/silsila-keluarga-frontend.git

# 4. Commit semua file frontend
git add .
git commit -m "Initial commit: Vue.js frontend for Silsila Keluarga"

# 5. Push ke repository private
git push -u origin main
```

### **3. Cleanup Repository Utama (Backend)**
```bash
# 1. Kembali ke root project
cd ..

# 2. Remove frontend folder dari git tracking
git rm -r --cached frontend/

# 3. Commit perubahan (exclude frontend)
git add .
git commit -m "Remove frontend folder - moved to private repository"

# 4. Push perubahan ke repository public
git push origin main
```

### **4. Setup Development Environment**
```bash
# 1. Clone repository backend (public)
git clone https://github.com/YOUR_USERNAME/silsila-keluarga.git backend-repo
cd backend-repo

# 2. Clone repository frontend (private) sebagai submodule atau folder terpisah
git clone https://github.com/YOUR_USERNAME/silsila-keluarga-frontend.git frontend

# ATAU menggunakan git submodule
git submodule add https://github.com/YOUR_USERNAME/silsila-keluarga-frontend.git frontend
```

---

## 🔐 **File Yang Di-Protect**

### **Frontend (.gitignore)**
```
# Environment variables (CRITICAL - NEVER COMMIT)
.env
.env.local
.env.development.local
.env.test.local
.env.production.local
.env.staging.local

# API keys and secrets
config/keys.js
config/secrets.js
src/config/api-keys.js

# User uploaded files
public/uploads/
storage/

# Production builds
dist/
build/

# Dependencies
node_modules/
```

### **Backend (.gitignore)**
```
# Environment files
.env
.env.local
.env.*

# Database files
*.sqlite
*.sqlite3

# Storage
storage/app/
storage/framework/
storage/logs/

# Vendor
/vendor/
```

---

## 🚀 **Workflow Development**

### **Untuk Development Lokal**
```bash
# 1. Setup backend
cd backend
composer install
cp .env.example .env
php artisan key:generate

# 2. Setup frontend
cd ../frontend
npm install
cp .env.example .env
# Edit .env untuk API endpoint

# 3. Jalankan kedua aplikasi
# Terminal 1 - Backend
cd backend && php artisan serve --host=0.0.0.0 --port=8000

# Terminal 2 - Frontend
cd frontend && npm run dev -- --host 0.0.0.0
```

### **Untuk Production Deployment**
```bash
# 1. Deploy backend (dari repo public)
git clone https://github.com/YOUR_USERNAME/silsila-keluarga.git
cd silsila-keluarga/backend
composer install --no-dev
# Setup .env production

# 2. Deploy frontend (dari repo private)
git clone https://github.com/YOUR_USERNAME/silsila-keluarga-frontend.git frontend
cd frontend
npm install
npm run build
# Copy dist/ ke public_html atau web server directory
```

---

## 🔄 **Sync Antar Repository**

### **Update Frontend dari Repository Private**
```bash
cd frontend
git pull origin main
npm install
```

### **Update Backend dari Repository Public**
```bash
cd backend
git pull origin main
composer install
```

---

## ⚠️ **Peringatan Keamanan**

### **JANGAN PERNAH commit:**
- ❌ `.env` files
- ❌ API keys
- ❌ Database credentials
- ❌ Private keys
- ❌ Upload files dari user

### **SELALU pastikan:**
- ✅ Repository frontend adalah **private**
- ✅ `.gitignore` berfungsi dengan baik
- ✅ Environment variables tidak di-commit
- ✅ Sensitive data di-encrypt

---

## 📞 **Troubleshooting**

### **Frontend folder masih ter-track di repository utama?**
```bash
# Check git status
git status

# Jika masih ada, force remove
git rm -r --cached frontend/
git commit -m "Force remove frontend folder"
```

### **Submodule bermasalah?**
```bash
# Remove submodule
git submodule deinit frontend
git rm frontend
git commit -m "Remove submodule"

# Re-add sebagai folder biasa
git clone https://github.com/YOUR_USERNAME/silsila-keluarga-frontend.git frontend
```

### **Permission denied untuk repository private?**
```bash
# Setup SSH key atau personal access token
# Pastikan Anda adalah collaborator di repository private
git remote set-url origin https://YOUR_USERNAME:YOUR_TOKEN@github.com/YOUR_USERNAME/silsila-keluarga-frontend.git
```

---

## 🎯 **Keuntungan Setup Ini**

### **✅ Keamanan**
- Frontend code private & secure
- Environment variables tidak ter-expose
- Sensitive configuration aman

### **✅ Fleksibilitas**
- Backend & frontend bisa di-deploy terpisah
- Version control terpisah untuk setiap bagian
- Development workflow lebih terstruktur

### **✅ Scalability**
- Bisa add lebih banyak frontend (mobile app, admin panel)
- Masing-masing bisa pakai teknologi berbeda
- Deployment strategy yang berbeda

---

## 📞 **Butuh Bantuan?**

Jika ada masalah dengan setup repository private, pastikan:

1. ✅ Repository GitHub sudah dibuat dengan visibility **Private**
2. ✅ Anda memiliki akses write ke repository tersebut
3. ✅ `.gitignore` sudah benar untuk kedua repository
4. ✅ Tidak ada file sensitif yang ter-commit

**🎊 Setup selesai! Frontend sekarang aman di repository private.**
