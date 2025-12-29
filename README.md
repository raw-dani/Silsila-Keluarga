# Silsila Keluarga - Aplikasi Manajemen Pohon Keluarga Digital

Aplikasi web modern untuk mengelola data keluarga dengan sistem approval, autentikasi berbasis token, dan visualisasi pohon keluarga yang user-friendly.

## 🚀 Fitur Utama

### ✅ Authentication & User Management
- Login/Register dengan email dan password
- Role-based access control (Admin/Member)
- Laravel Sanctum token authentication
- User profile management
- Password change functionality

### ✅ Manajemen Data Keluarga
- **CRUD lengkap** anggota keluarga
- **Relasi kompleks**: ayah, ibu, anak, pasangan
- **Data lengkap**: nama, email, telepon, jenis kelamin, tanggal lahir/meninggal
- **Auto-generation calculation** berdasarkan relasi keluarga
- **Upload foto** dengan storage management
- **Search & filtering** berdasarkan nama, generasi, jenis kelamin

### ✅ Sistem Approval & Update Requests
- **Member request system**: Ajukan perubahan data keluarga
- **Admin approval workflow**: Approve/reject requests
- **Multiple change types**: Biodata, hubungan keluarga, foto profil
- **Tambah anggota baru** melalui sistem request
- **Audit trail** untuk tracking perubahan

### ✅ Admin Dashboard & Management
- **Comprehensive admin panel** untuk kelola semua data
- **Bulk operations** untuk hapus multiple members
- **Statistics dashboard** dengan real-time data
- **User management** dan role assignment
- **Request management** dengan status tracking

### ✅ User Dashboard & Features
- **Personal dashboard** dengan family statistics
- **Add member requests** untuk menambah anggota keluarga
- **Profile management** dan data viewing
- **Family tree preview** dengan navigasi intuitif

## 🛠️ Tech Stack

### Frontend
- **Vue.js 3** - Composition API dengan `<script setup>`
- **Vite** - Build tool ultra-cepat dengan HMR
- **Vue Router 4** - SPA routing dengan navigation guards
- **Axios** - HTTP client dengan interceptors
- **CSS3 Grid & Flexbox** - Modern responsive layout
- **Emoji & SVG icons** - Native icon system

### Backend
- **Laravel 11/12** - Modern PHP framework
- **Laravel Sanctum** - API token authentication
- **Eloquent ORM** - Database relationships & querying
- **MySQL 8.0+** - Relational database
- **File Storage** - Image upload & management
- **Migration system** - Database schema management

### Development & Deployment
- **Composer** - PHP dependency management
- **NPM** - Node.js package management
- **Git** - Distributed version control
- **Windows batch scripts** - Easy development setup

## 📁 Struktur Proyek

```
silsila-keluarga/
├── frontend/                 # Vue.js SPA
│   ├── src/
│   │   ├── components/       # Reusable components
│   │   ├── views/           # Page components
│   │   ├── router/          # Vue Router config
│   │   ├── services/        # API services
│   │   └── assets/          # Static assets
│   ├── package.json
│   └── vite.config.js
├── backend/                  # Laravel API
│   ├── app/
│   │   ├── Models/          # Eloquent models
│   │   ├── Http/Controllers/ # API controllers
│   │   └── Middleware/      # Custom middleware
│   ├── database/
│   │   ├── migrations/      # Database migrations
│   │   └── seeders/         # Database seeders
│   ├── routes/
│   │   └── api.php          # API routes
│   └── composer.json
├── run.bat                   # Windows startup script
└── README.md                # Documentation
```

## 🗄️ Database Schema

### Users Table
```sql
- id (Primary Key)
- name
- email (Unique)
- password
- role (admin/member)
- email_verified_at
- created_at, updated_at
```

### Family Members Table
```sql
- id (Primary Key)
- name
- gender (male/female)
- birth_date
- death_date (nullable)
- father_id (Foreign Key)
- mother_id (Foreign Key)
- spouse_id (Foreign Key)
- photo (nullable)
- generation_level
- notes (nullable)
- created_at, updated_at
```

### Update Requests Table
```sql
- id (Primary Key)
- member_id (User who requested)
- target_member_id (Member to update)
- change_type (biodata/hubungan/foto)
- old_data (JSON)
- new_data (JSON)
- status (pending/approved/rejected)
- admin_note (nullable)
- created_at, updated_at
```

## 🚀 Instalasi & Setup

### Prerequisites
- **PHP 8.2+** dengan WampServer/XAMPP/MAMP
- **Composer** - PHP dependency manager
- **Node.js 18+** & NPM - JavaScript runtime
- **MySQL 8.0+** - Database server
- **Git** - Version control system

### 1. Clone Repository
```bash
git clone https://github.com/raw-dani/Silsila-Keluarga.git
cd silsila-keluarga
```

### 2. Setup Backend (Laravel)
```bash
cd backend

# Install PHP dependencies
composer install

# Setup environment file
copy .env.example .env
# Edit .env file dengan database credentials

# Generate application key
php artisan key:generate

# Run database migrations
php artisan migrate

# Seed admin user
php artisan db:seed --class=AdminUserSeeder

# Alternative: Jalankan semua sekaligus
# php artisan migrate --seed --seeder=AdminUserSeeder
```

### 3. Setup Frontend (Vue.js)
```bash
cd frontend

# Install Node.js dependencies
npm install

# Start development server
npm run dev
```

### 4. Quick Start (Windows)
```bash
# Jalankan script otomatis (recommended)
run.bat
```

### 5. Akses Aplikasi
- Frontend: http://localhost:5173
- Backend API: http://localhost:8000

## 🔐 Default Credentials

### Admin User
- Email: admin@silsila-keluarga.com
- Password: admin123

### Sample Member
- Email: member@example.com
- Password: member123

## 📡 API Endpoints

### Authentication
```
POST   /api/login
POST   /api/register
POST   /api/logout
GET    /api/user
```

### Family Members
```
GET    /api/family-members
POST   /api/family-members
GET    /api/family-members/{id}
PUT    /api/family-members/{id}
DELETE /api/family-members/{id}
GET    /api/family-tree
```

### Update Requests
```
GET    /api/update-requests
POST   /api/update-requests
PUT    /api/update-requests/{id}/approve
PUT    /api/update-requests/{id}/reject
```

## 🎨 UI/UX Features

### Responsive Design
- Mobile-first approach
- Tablet & desktop optimized
- Touch-friendly controls

### Interactive Tree
- Zoom in/out/reset controls
- Pan dengan mouse/touch
- Click nodes untuk detail
- Smooth animations

### Modern UI
- Clean, professional design
- Gender-based color coding
- Avatar placeholders
- Loading states
- Error handling

## 🧪 Testing

### Unit Tests
```bash
cd backend
php artisan test
```

### API Testing
```bash
# Using Postman or curl
curl -X GET http://localhost:8000/api/family-tree \
  -H "Authorization: Bearer {token}"
```

### Frontend Testing
```bash
cd frontend
npm run test
```

## 🚀 Deployment

### Production Build
```bash
# Frontend
cd frontend
npm run build

# Backend
cd backend
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Web Server Config
- Point web root to `backend/public`
- Copy built frontend to `backend/public/dist`
- Configure virtual host

## 📝 Development Notes

### Code Style
- PSR-12 untuk PHP
- Vue.js Style Guide
- ESLint untuk JavaScript

### Security
- CSRF protection
- Input validation
- SQL injection prevention
- XSS protection

### Performance
- Database indexing
- API caching
- Image optimization
- Code splitting

## 🤝 Contributing

1. Fork repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## 📄 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 📞 Support

For support, email support@silsila-keluarga.com or create an issue in the repository.

---

**Dibuat dengan ❤️ untuk memudahkan pengelolaan silsilah keluarga**
