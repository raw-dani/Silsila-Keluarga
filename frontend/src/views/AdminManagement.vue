<template>
  <div class="admin-management-page">
    <Navigation />

    <div class="admin-content">
      <div class="admin-header">
        <h1>Kelola Admin</h1>
        <p>Kelola pengguna admin yang dapat mengakses sistem manajemen aplikasi</p>
      </div>

      <!-- Add New Admin Section -->
      <div class="admin-form-container">
        <div class="form-card">
          <h3 class="card-title">➕ Tambah Admin Baru</h3>
          <form @submit.prevent="createAdmin" class="admin-form">
            <div class="form-row">
              <div class="form-group">
                <label for="adminName">Nama Lengkap</label>
                <input
                  id="adminName"
                  v-model="newAdmin.name"
                  type="text"
                  required
                  class="form-input"
                  placeholder="Masukkan nama lengkap"
                >
              </div>
              <div class="form-group">
                <label for="adminEmail">Email</label>
                <input
                  id="adminEmail"
                  v-model="newAdmin.email"
                  type="email"
                  required
                  class="form-input"
                  placeholder="admin@example.com"
                >
              </div>
            </div>

            <div class="form-row">
              <div class="form-group">
                <label for="adminPassword">Password</label>
                <input
                  id="adminPassword"
                  v-model="newAdmin.password"
                  type="password"
                  required
                  class="form-input"
                  placeholder="Minimal 8 karakter"
                  minlength="8"
                >
              </div>
              <div class="form-group">
                <label for="adminPasswordConfirm">Konfirmasi Password</label>
                <input
                  id="adminPasswordConfirm"
                  v-model="newAdmin.password_confirmation"
                  type="password"
                  required
                  class="form-input"
                  placeholder="Ulangi password"
                  minlength="8"
                >
              </div>
            </div>

            <div class="form-actions">
              <button type="button" @click="resetForm" class="cancel-btn">
                <span class="btn-icon">🔄</span>
                <span>Reset</span>
              </button>
              <button type="submit" :disabled="loading" class="submit-btn">
                <span class="btn-icon">{{ loading ? '⏳' : '👑' }}</span>
                <span>{{ loading ? 'Membuat...' : 'Buat Admin' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Admin List Section -->
      <div class="admin-list-container">
        <div class="list-card">
          <div class="list-header">
            <h3 class="card-title">👥 Daftar Admin</h3>
            <div class="admin-count">
              <span class="count-badge">{{ admins.length }}</span>
              <span>Admin Aktif</span>
            </div>
          </div>

          <div v-if="admins.length === 0" class="empty-state">
            <div class="empty-icon">👑</div>
            <h4>Belum ada admin</h4>
            <p>Gunakan form di atas untuk menambah admin baru</p>
          </div>

          <div v-else class="admin-grid">
            <div v-for="admin in admins" :key="admin.id" class="admin-card">
              <div class="admin-avatar">
                <span class="avatar-icon">👑</span>
              </div>
              <div class="admin-info">
                <h4 class="admin-name">{{ admin.name }}</h4>
                <p class="admin-email">{{ admin.email }}</p>
                <small class="admin-created">
                  Dibuat: {{ formatDate(admin.created_at) }}
                </small>
              </div>
              <div class="admin-actions">
                <button
                  @click="deleteAdmin(admin.id)"
                  class="delete-btn"
                  :disabled="admins.length === 1"
                  title="Hapus admin"
                >
                  <span>🗑️</span>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Slider Management Section -->
      <div class="slider-management-container">
        <div class="form-card">
          <h3 class="card-title">🖼️ Kelola Slider Halaman Login</h3>
          <p class="card-description">Update gambar dan konten slider yang ditampilkan di halaman login</p>

          <form class="slider-form">
            <div v-for="(slide, index) in loginSlides" :key="index" class="slide-editor">
              <div class="slide-header-section">
                <h4 class="slide-title">Slide {{ index + 1 }}</h4>
                <div class="slide-controls">                  
                  <span v-if="slide.image" class="status-active">✅ Ada Gambar</span>
                  <span v-else class="status-inactive">❌ Tidak Ada Gambar</span>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label :for="'title-' + index">Judul Slide</label>
                  <input
                    :id="'title-' + index"
                    v-model="slide.title"
                    type="text"
                    class="form-input"
                    placeholder="Masukkan judul slide"
                    @input="updateSlide(index)"
                  >
                </div>
                <div class="form-group">
                  <label :for="'desc-' + index">Deskripsi</label>
                  <input
                    :id="'desc-' + index"
                    v-model="slide.description"
                    type="text"
                    class="form-input"
                    placeholder="Masukkan deskripsi slide"
                    @input="updateSlide(index)"
                  >
                </div>
              </div>

              <div class="image-upload-section">
                <div class="current-image">
                  <img v-if="slide.image" :src="slide.image" :alt="slide.title" class="slide-preview">
                  <div v-else class="no-image">
                    <div class="no-image-icon">📷</div>
                    <p>Belum ada gambar</p>
                  </div>
                </div>
                <div class="image-controls">
                  <input
                    type="file"
                    :id="'image-' + index"
                    @change="(e) => handleImageUpload(e, index)"
                    accept="image/*"
                    class="image-input"
                  >
                  <label :for="'image-' + index" class="upload-btn">
                    <span class="btn-icon">📤</span>
                    <span>Upload Gambar</span>
                  </label>
                  <button
                    v-if="slide.image"
                    @click="removeImage(index)"
                    class="remove-btn"
                  >
                    <span class="btn-icon">🗑️</span>
                    <span>Hapus</span>
                  </button>
                </div>
              </div>
              <div class="visibility-toggle">
                <label class="toggle-label">
                  <input
                    type="checkbox"
                    v-model="slide.is_visible"
                    @change="updateSlide(index)"
                    class="visibility-checkbox"
                  >
                  <span class="toggle-slider"></span>
                  <span class="toggle-text">{{ slide.is_visible ? 'Visible' : 'Hidden' }}</span>
                </label>
              </div>
            </div>

            <div class="form-actions">
              <!-- <button type="button" @click="testSlides" class="btn btn-secondary">
                <span class="btn-icon">🔍</span>
                <span>Test Slides</span>
              </button> -->
              <button type="button" @click="resetSlides" class="btn btn-outline">
                <span class="btn-icon">🔄</span>
                <span>Reset ke Default</span>
              </button>
              <button type="button" @click="saveSlides" class="btn btn-primary">
                <span class="btn-icon">💾</span>
                <span>Simpan Perubahan</span>
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Message Display -->
      <div v-if="message" class="message-container" :class="messageType">
        <div class="message-icon">{{ messageType === 'success' ? '✅' : '❌' }}</div>
        <div class="message-content">
          <h4>{{ messageType === 'success' ? 'Berhasil!' : 'Error' }}</h4>
          <p>{{ message }}</p>
        </div>
        <button @click="message = ''" class="message-close">✕</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import api from '../services/api'
import Navigation from '../components/Navigation.vue'

const newAdmin = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: ''
})

const admins = ref([])
const loading = ref(false)
const message = ref('')
const messageType = ref('')

// Slider management - Load from API or use defaults
const loginSlides = ref([
  {
    title: "Kelola Data Keluarga",
    description: "Pantau dan kelola informasi lengkap anggota keluarga Anda dengan mudah",
    image: null,
    is_visible: true
  },
  {
    title: "Sistem Approval Modern",
    description: "Permintaan perubahan data melalui sistem approval yang aman dan terstruktur",
    image: null,
    is_visible: true
  },
  {
    title: "Visualisasi Pohon Keluarga",
    description: "Lihat struktur keluarga dalam bentuk pohon yang mudah dipahami",
    image: null,
    is_visible: true
  },
  {
    title: "Keamanan & Privasi",
    description: "Data keluarga Anda aman dengan sistem keamanan modern",
    image: null,
    is_visible: true
  }
])

// Load slides from API on mount
const loadSlidesFromAPI = async () => {
  try {
    console.log('🔄 AdminManagement: Loading slides from API...')
    const response = await api.get('/slider-data')
    const slidesData = response.data || []

    console.log('📦 Admin loading slides from API:', slidesData)

    // Update existing slides with API data
    slidesData.forEach((slide, index) => {
      if (loginSlides.value[index]) {
        loginSlides.value[index] = {
          title: slide.title || loginSlides.value[index].title,
          description: slide.description || loginSlides.value[index].description,
          image: slide.image || null,
          is_visible: slide.is_visible === 1 || slide.is_visible === true // Handle both 0/1 and true/false
        }
      }
    })

    console.log('✅ Admin slides loaded:', loginSlides.value)
  } catch (error) {
    console.error('❌ Error loading slides in admin:', error)
    console.log('Keeping default values due to API error')

    // If API fails, keep the default values that are already set
    // Don't modify loginSlides.value to preserve defaults
    message.value = 'Gagal memuat data slider. Menggunakan nilai default.'
    messageType.value = 'error'
    setTimeout(() => message.value = '', 5000)
  }
}

// Default SVG illustrations for fallback
const defaultSlides = [
  {
    title: "Kelola Data Keluarga",
    description: "Pantau dan kelola informasi lengkap anggota keluarga Anda dengan mudah",
    svg: `
      <svg viewBox="0 0 200 150" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <style>
            .family-circle { fill: #D9B382; stroke: #6B4F3F; stroke-width: 2; }
            .family-outline { fill: none; stroke: #A67C52; stroke-width: 1.5; }
            .tree { fill: #8B5A3C; }
          </style>
        </defs>

        <rect x="95" y="80" width="10" height="40" class="tree" rx="2"/>
        <path d="M90 120 L100 110 L110 120 L105 125 L95 125 Z" class="tree"/>

        <circle cx="85" cy="50" r="8" class="family-circle"/>
        <circle cx="115" cy="50" r="8" class="family-circle"/>
        <circle cx="75" cy="75" r="6" class="family-circle"/>
        <circle cx="100" cy="80" r="6" class="family-circle"/>
        <circle cx="125" cy="75" r="6" class="family-circle"/>

        <line x1="85" y1="58" x2="115" y2="58" class="family-outline"/>
        <line x1="100" y1="58" x2="100" y2="74" class="family-outline"/>
        <line x1="100" y1="74" x2="75" y2="69" class="family-outline"/>
        <line x1="100" y1="74" x2="100" y2="74" class="family-outline"/>
        <line x1="100" y1="74" x2="125" y2="69" class="family-outline"/>
      </svg>
    `
  },
  {
    title: "Sistem Approval Modern",
    description: "Permintaan perubahan data melalui sistem approval yang aman dan terstruktur",
    svg: `
      <svg viewBox="0 0 200 150" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <style>
            .document { fill: #FAF7F2; stroke: #6B4F3F; stroke-width: 1; }
            .check { fill: #4CAF50; }
            .pending { fill: #FF9800; }
            .person { fill: #A67C52; }
          </style>
        </defs>

        <rect x="70" y="50" width="25" height="35" class="document" rx="2"/>
        <rect x="105" y="45" width="25" height="35" class="document" rx="2"/>
        <rect x="75" y="55" width="20" height="30" class="document" rx="2"/>
        <rect x="110" y="50" width="20" height="30" class="document" rx="2"/>

        <circle cx="82" cy="42" r="4" class="check"/>
        <circle cx="117" cy="37" r="4" class="pending"/>
        <circle cx="85" cy="47" r="4" class="pending"/>
        <circle cx="120" cy="42" r="4" class="pending"/>

        <path d="M79 40 L81 42 L86 37" stroke="white" stroke-width="1.5" fill="none"/>
        <path d="M114 35 L116 37 L121 32" stroke="white" stroke-width="1.5" fill="none"/>

        <circle cx="150" cy="70" r="12" class="person"/>
        <rect x="145" y="82" width="10" height="15" class="person" rx="2"/>
        <rect x="143" y="82" width="14" height="6" class="person" rx="2"/>

        <circle cx="50" cy="70" r="10" class="person"/>
        <rect x="46" y="80" width="8" height="12" class="person" rx="2"/>
        <rect x="45" y="80" width="10" height="5" class="person" rx="2"/>

        <path d="M60 75 L130 75 L125 70 M130 75 L125 80" stroke="#6B4F3F" stroke-width="2" fill="none"/>
        <polygon points="135,75 125,70 125,80" fill="#6B4F3F"/>
      </svg>
    `
  },
  {
    title: "Visualisasi Pohon Keluarga",
    description: "Lihat struktur keluarga dalam bentuk pohon yang mudah dipahami",
    svg: `
      <svg viewBox="0 0 200 150" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <style>
            .node { fill: #D9B382; stroke: #6B4F3F; stroke-width: 2; }
            .connection { stroke: #A67C52; stroke-width: 2; fill: none; }
            .highlight { fill: #E8D5B7; stroke: #8B5A3C; stroke-width: 2; }
          </style>
        </defs>

        <circle cx="100" cy="30" r="8" class="node"/>
        <text x="100" y="35" text-anchor="middle" font-size="8" fill="#6B4F3F">1</text>

        <circle cx="80" cy="60" r="7" class="node"/>
        <circle cx="120" cy="60" r="7" class="node"/>
        <text x="80" y="65" text-anchor="middle" font-size="7" fill="#6B4F3F">2</text>
        <text x="120" y="65" text-anchor="middle" font-size="7" fill="#6B4F3F">3</text>

        <circle cx="60" cy="90" r="6" class="highlight"/>
        <circle cx="100" cy="95" r="6" class="node"/>
        <circle cx="140" cy="90" r="6" class="node"/>
        <text x="60" y="95" text-anchor="middle" font-size="6" fill="#6B4F3F">4</text>
        <text x="100" y="100" text-anchor="middle" font-size="6" fill="#6B4F3F">5</text>
        <text x="140" y="95" text-anchor="middle" font-size="6" fill="#6B4F3F">6</text>

        <circle cx="100" cy="120" r="5" class="node"/>
        <text x="100" y="125" text-anchor="middle" font-size="5" fill="#6B4F3F">7</text>

        <line x1="100" y1="38" x2="80" y2="53" class="connection"/>
        <line x1="100" y1="38" x2="120" y2="53" class="connection"/>
        <line x1="80" y1="67" x2="60" y2="84" class="connection"/>
        <line x1="80" y1="67" x2="100" y2="89" class="connection"/>
        <line x1="120" y1="67" x2="100" y2="89" class="connection"/>
        <line x1="120" y1="67" x2="140" y2="84" class="connection"/>
        <line x1="100" y1="101" x2="100" y2="115" class="connection"/>
      </svg>
    `
  },
  {
    title: "Keamanan & Privasi",
    description: "Data keluarga Anda aman dengan sistem keamanan modern",
    svg: `
      <svg viewBox="0 0 200 150" xmlns="http://www.w3.org/2000/svg">
        <defs>
          <style>
            .shield { fill: #6B4F3F; }
            .shield-outline { fill: none; stroke: #A67C52; stroke-width: 2; }
            .lock { fill: #D9B382; stroke: #6B4F3F; stroke-width: 1; }
            .secure-icon { fill: #4CAF50; }
          </style>
        </defs>

        <path d="M100 25 L130 45 L130 85 L100 105 L70 85 L70 45 Z" class="shield"/>
        <path d="M100 25 L130 45 L130 85 L100 105 L70 85 L70 45 Z" class="shield-outline"/>

        <rect x="88" y="55" width="24" height="16" class="lock" rx="2"/>
        <rect x="95" y="65" width="10" height="6" fill="none" stroke="#6B4F3F" stroke-width="1"/>
        <circle cx="100" cy="62" r="3" fill="none" stroke="#6B4F3F" stroke-width="1"/>
        <rect x="99" y="59" width="2" height="6" fill="#6B4F3F"/>
        <circle cx="100" cy="70" r="2" fill="#6B4F3F"/>

        <circle cx="85" cy="75" r="4" class="secure-icon"/>
        <circle cx="115" cy="75" r="4" class="secure-icon"/>
        <circle cx="100" cy="85" r="4" class="secure-icon"/>

        <path d="M82 73 L84 75 L89 70" stroke="white" stroke-width="1" fill="none"/>
        <path d="M112 73 L114 75 L119 70" stroke="white" stroke-width="1" fill="none"/>
        <path d="M97 83 L99 85 L104 80" stroke="white" stroke-width="1" fill="none"/>
      </svg>
    `
  }
]

const formatDate = (dateString) => {
  if (!dateString) return 'Tidak diketahui'
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

const resetForm = () => {
  newAdmin.value = {
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
  }
}

const createAdmin = async () => {
  if (newAdmin.value.password !== newAdmin.value.password_confirmation) {
    message.value = 'Konfirmasi password tidak cocok'
    messageType.value = 'error'
    return
  }

  loading.value = true
  message.value = ''

  try {
    const response = await api.post('/admin/users', {
      ...newAdmin.value,
      role: 'admin'
    })

    message.value = `Admin "${response.data.user.name}" berhasil dibuat!`
    messageType.value = 'success'

    resetForm()
    loadAdmins()

    // Auto-hide message after 5 seconds
    setTimeout(() => {
      message.value = ''
    }, 5000)
  } catch (error) {
    message.value = error.response?.data?.message || 'Gagal membuat admin. Silakan coba lagi.'
    messageType.value = 'error'
  } finally {
    loading.value = false
  }
}

const loadAdmins = async () => {
  try {
    const response = await api.get('/admin/users')
    admins.value = response.data
  } catch (error) {
    console.error('Error loading admins:', error)
    // Fallback to empty array
    admins.value = []
  }
}

const deleteAdmin = async (adminId) => {
  if (admins.value.length === 1) {
    message.value = 'Tidak dapat menghapus admin terakhir. Minimal harus ada 1 admin aktif.'
    messageType.value = 'error'
    return
  }

  if (!confirm('Apakah Anda yakin ingin menghapus admin ini?')) {
    return
  }

  try {
    await api.delete(`/admin/users/${adminId}`)
    message.value = 'Admin berhasil dihapus'
    messageType.value = 'success'
    loadAdmins()

    setTimeout(() => {
      message.value = ''
    }, 3000)
  } catch (error) {
    message.value = error.response?.data?.message || 'Gagal menghapus admin'
    messageType.value = 'error'
  }
}

const updateSlide = (index) => {
  // Auto-save to localStorage when user types
  const slidesToSave = loginSlides.value.map(slide => ({
    title: slide.title,
    description: slide.description,
    image: slide.image
  }))
  localStorage.setItem('loginSlides', JSON.stringify(slidesToSave))
}

const handleImageUpload = async (event, index) => {
  const file = event.target.files[0]
  if (!file) return

  // Validate file type
  if (!file.type.startsWith('image/')) {
    message.value = 'File harus berupa gambar (JPG, PNG, GIF, dll.)'
    messageType.value = 'error'
    return
  }

  // Validate file size (max 5MB)
  if (file.size > 5 * 1024 * 1024) {
    message.value = 'Ukuran file maksimal 5MB'
    messageType.value = 'error'
    return
  }

  // Show loading
  message.value = 'Mengupload gambar...'
  messageType.value = 'success'

  try {
    // Create FormData for file upload
    const formData = new FormData()
    formData.append('image', file)
    formData.append('slide_index', index)

    // Upload to backend
    const response = await api.post('/admin/upload-slider-image', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    // Update slide with image path
    loginSlides.value[index].image = response.data.image_path
    updateSlide(index)

    message.value = `Gambar slide ${index + 1} berhasil diupload!`
    messageType.value = 'success'
    setTimeout(() => message.value = '', 3000)

  } catch (error) {
    console.error('Upload error:', error)
    message.value = error.response?.data?.message || 'Gagal mengupload gambar'
    messageType.value = 'error'
    setTimeout(() => message.value = '', 5000)
  }
}

const removeImage = (index) => {
  if (confirm('Apakah Anda yakin ingin menghapus gambar ini?')) {
    loginSlides.value[index].image = null
    updateSlide(index)

    message.value = `Gambar slide ${index + 1} berhasil dihapus!`
    messageType.value = 'success'
    setTimeout(() => message.value = '', 3000)
  }
}

const saveSlides = async () => {
  console.log('Saving slides to database...')

  const slidesToSave = loginSlides.value.map(slide => ({
    title: slide.title,
    description: slide.description,
    image: slide.image,
    is_visible: slide.is_visible
  }))

  console.log('Slides to save:', slidesToSave)

  try {
    const response = await api.post('/admin/save-slider-data', {
      slides: slidesToSave
    })

    console.log('Slides saved successfully!', response.data)

    message.value = 'Pengaturan slider berhasil disimpan!'
    messageType.value = 'success'
    setTimeout(() => message.value = '', 3000)
  } catch (error) {
    console.error('Error saving slides:', error)
    message.value = error.response?.data?.message || 'Gagal menyimpan pengaturan slider!'
    messageType.value = 'error'
    setTimeout(() => message.value = '', 5000)
  }
}

const testSlides = () => {
  console.log('🧪 Testing Slides...')

  // Check current localStorage
  const saved = localStorage.getItem('loginSlides')
  console.log('📦 Current localStorage data:', saved)

  // Check current slides state
  console.log('🎯 Current loginSlides state:', loginSlides.value)

  // Test save functionality
  const slidesToSave = loginSlides.value.map(slide => ({
    title: slide.title,
    description: slide.description,
    image: slide.image
  }))

  console.log('💾 Data to save:', slidesToSave)

  try {
    localStorage.setItem('loginSlides', JSON.stringify(slidesToSave))
    console.log('✅ Save successful')

    // Verify
    const verified = localStorage.getItem('loginSlides')
    console.log('🔍 Verification - saved data:', verified)

    message.value = 'Test selesai! Cek console untuk detail.'
    messageType.value = 'success'
    setTimeout(() => message.value = '', 5000)
  } catch (error) {
    console.error('❌ Save failed:', error)
    message.value = 'Test gagal! Cek console.'
    messageType.value = 'error'
  }
}

const resetSlides = () => {
  if (confirm('Apakah Anda yakin ingin mereset semua slide ke pengaturan default? Semua perubahan akan hilang.')) {
    // Reset to default SVG illustrations
    loginSlides.value = defaultSlides.map(slide => ({
      title: slide.title,
      description: slide.description,
      image: null // Keep images null, will use SVG fallback
    }))

    localStorage.removeItem('loginSlides')

    message.value = 'Slider berhasil direset ke pengaturan default!'
    messageType.value = 'success'
    setTimeout(() => message.value = '', 3000)
  }
}

const loadSlides = () => {
  const saved = localStorage.getItem('loginSlides')
  if (saved) {
    try {
      const parsedSlides = JSON.parse(saved)
      loginSlides.value = parsedSlides.map((slide, index) => ({
        title: slide.title || defaultSlides[index].title,
        description: slide.description || defaultSlides[index].description,
        image: slide.image || null
      }))
    } catch (error) {
      console.error('Error loading saved slides:', error)
      // Use default if parsing fails
      loginSlides.value = defaultSlides.map(slide => ({
        title: slide.title,
        description: slide.description,
        image: null
      }))
    }
  }
}

onMounted(() => {
  loadAdmins()
  loadSlidesFromAPI() // Load slides from API
})
</script>

<style scoped>
.admin-management-page {
  min-height: 100vh;
  background: var(--bg-main);
}

.admin-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 30px 20px;
}

.admin-header {
  text-align: center;
  margin-bottom: 40px;
}

.admin-header h1 {
  color: var(--text-primary);
  font-size: 2.5rem;
  margin-bottom: 10px;
  font-weight: 700;
}

.admin-header p {
  color: var(--text-secondary);
  font-size: 1.1rem;
}

.admin-form-container, .admin-list-container {
  margin-bottom: 32px;
}

.form-card, .list-card {
  background: var(--bg-card);
  border-radius: 16px;
  padding: 32px;
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.1);
  border: 1px solid rgba(107, 79, 63, 0.1);
}

.card-title {
  color: var(--text-primary);
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 24px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.admin-form {
  display: grid;
  gap: 24px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 20px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  color: var(--text-secondary);
  font-size: 0.95rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-input {
  padding: 14px 16px;
  border: 2px solid rgba(107, 79, 63, 0.2);
  border-radius: 8px;
  background: var(--bg-main);
  color: var(--text-primary);
  font-size: 1rem;
  transition: all 0.3s ease;
}

.form-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(107, 79, 63, 0.1);
}

.form-actions {
  display: flex;
  gap: 16px;
  justify-content: center;
  flex-wrap: wrap;
  margin-top: 8px;
}

.cancel-btn, .submit-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 16px 28px;
  border-radius: 25px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  min-width: 160px;
  justify-content: center;
}

.cancel-btn {
  background: rgba(107, 79, 63, 0.1);
  color: var(--text-secondary);
  border: 2px solid rgba(107, 79, 63, 0.2);
}

.cancel-btn:hover {
  background: rgba(107, 79, 63, 0.2);
  color: var(--text-primary);
  transform: translateY(-2px);
}

.test-btn {
  background: linear-gradient(135deg, var(--accent), var(--primary));
  color: white;
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.3);
}

.test-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(107, 79, 63, 0.4);
}

.submit-btn {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.3);
}

.submit-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(107, 79, 63, 0.4);
}

.submit-btn:disabled {
  background: #ccc;
  box-shadow: none;
  cursor: not-allowed;
  transform: none;
}

.btn-icon {
  font-size: 1.2rem;
}

.list-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 24px;
  flex-wrap: wrap;
  gap: 16px;
}

.admin-count {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--text-secondary);
  font-size: 0.9rem;
}

.count-badge {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  padding: 4px 12px;
  border-radius: 20px;
  font-weight: 600;
  font-size: 0.9rem;
}

.empty-state {
  text-align: center;
  padding: 60px 20px;
  color: var(--text-secondary);
}

.empty-icon {
  font-size: 4rem;
  margin-bottom: 16px;
  opacity: 0.5;
}

.empty-state h4 {
  margin: 0 0 8px 0;
  color: var(--text-primary);
  font-size: 1.3rem;
}

.empty-state p {
  margin: 0;
  font-size: 1rem;
}

.admin-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 20px;
}

.admin-card {
  background: rgba(107, 79, 63, 0.02);
  border: 1px solid rgba(107, 79, 63, 0.1);
  border-radius: 12px;
  padding: 20px;
  display: flex;
  align-items: center;
  gap: 16px;
  transition: all 0.3s ease;
}

.admin-card:hover {
  background: rgba(107, 79, 63, 0.05);
  border-color: rgba(107, 79, 63, 0.2);
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.1);
}

.admin-avatar {
  flex-shrink: 0;
}

.avatar-icon {
  font-size: 2.5rem;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  border-radius: 50%;
  width: 60px;
  height: 60px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.admin-info {
  flex: 1;
  min-width: 0;
}

.admin-name {
  margin: 0 0 4px 0;
  color: var(--text-primary);
  font-size: 1.2rem;
  font-weight: 600;
  word-break: break-word;
}

.admin-email {
  margin: 0 0 4px 0;
  color: var(--text-secondary);
  font-size: 0.9rem;
  word-break: break-all;
}

.admin-created {
  color: var(--text-muted);
  font-size: 0.8rem;
}

.admin-actions {
  flex-shrink: 0;
}

.delete-btn {
  background: linear-gradient(135deg, var(--error), var(--error));
  color: white;
  border: none;
  border-radius: 8px;
  padding: 8px;
  cursor: pointer;
  font-size: 1.2rem;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(229, 57, 53, 0.3);
}

.delete-btn:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(229, 57, 53, 0.4);
}

.delete-btn:disabled {
  background: #ccc;
  cursor: not-allowed;
  box-shadow: none;
  transform: none;
}

.message-container {
  margin-top: 24px;
  padding: 20px;
  border-radius: 12px;
  display: flex;
  align-items: flex-start;
  gap: 16px;
  animation: slideIn 0.3s ease;
  position: relative;
}

.message-container.success {
  background: rgba(76, 175, 80, 0.1);
  border: 1px solid rgba(76, 175, 80, 0.3);
}

.message-container.error {
  background: rgba(244, 67, 54, 0.1);
  border: 1px solid rgba(244, 67, 54, 0.3);
}

.message-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
}

.message-content h4 {
  margin: 0 0 4px 0;
  color: var(--text-primary);
  font-size: 1.1rem;
  font-weight: 600;
}

.message-content p {
  margin: 0;
  color: var(--text-secondary);
  line-height: 1.5;
}

.message-close {
  position: absolute;
  top: 12px;
  right: 12px;
  background: none;
  border: none;
  font-size: 1.2rem;
  cursor: pointer;
  color: var(--text-secondary);
  padding: 4px;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Desktop styles */
@media (min-width: 1024px) {
  .admin-content {
    padding: 40px 30px;
  }

  .admin-header h1 {
    font-size: 3rem;
  }

  .form-card, .list-card {
    padding: 40px;
  }

  .admin-grid {
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 24px;
  }

  .form-actions {
    gap: 20px;
  }

  .cancel-btn, .submit-btn {
    padding: 18px 32px;
    font-size: 1.1rem;
    min-width: 180px;
  }
}

/* Tablet styles */
@media (min-width: 768px) and (max-width: 1023px) {
  .admin-content {
    padding: 30px 20px;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .admin-grid {
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
  }
}

/* Button styles to match main app theme */
.btn {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  padding: 12px 24px;
  border-radius: 8px;
  font-size: 0.95rem;
  font-weight: 600;
  text-decoration: none;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  outline: none;
}

/* Slider form styling */
.slider-form {
  display: grid;
  gap: 32px;
}

.slide-editor {
  background: rgba(107, 79, 63, 0.02);
  border: 1px solid rgba(107, 79, 63, 0.1);
  border-radius: 12px;
  padding: 24px;
  transition: all 0.3s ease;
}

.slide-editor:hover {
  background: rgba(107, 79, 63, 0.05);
  border-color: rgba(107, 79, 63, 0.15);
}

.slide-header-section {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  padding-bottom: 16px;
  border-bottom: 1px solid rgba(107, 79, 63, 0.1);
}

.slide-title {
  margin: 0;
  color: var(--text-primary);
  font-size: 1.2rem;
  font-weight: 600;
}

.slide-controls {
  display: flex;
  align-items: center;
  gap: 16px;
}

.status-active {
  color: #4CAF50;
  font-weight: 500;
  font-size: 0.9rem;
}

.status-inactive {
  color: #FF9800;
  font-weight: 500;
  font-size: 0.9rem;
}

.image-upload-section {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid rgba(107, 79, 63, 0.1);
}

.current-image {
  margin-bottom: 16px;
}

.image-controls {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.upload-btn, .remove-btn {
  padding: 10px 16px;
  border-radius: 8px;
  font-size: 0.9rem;
  cursor: pointer;
  transition: all 0.3s ease;
}

.upload-btn {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  border: none;
}

.upload-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(107, 79, 63, 0.3);
}

.remove-btn {
  background: linear-gradient(135deg, var(--error), var(--error));
  color: white;
  border: none;
}

.remove-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(229, 57, 53, 0.3);
}

.visibility-toggle {
  margin-top: 16px;
  padding-top: 16px;
  border-top: 1px solid rgba(107, 79, 63, 0.1);
}

.toggle-label {
  display: flex;
  align-items: center;
  gap: 12px;
  cursor: pointer;
  font-weight: 500;
  color: var(--text-primary);
}

.visibility-checkbox {
  display: none;
}

.toggle-slider {
  position: relative;
  width: 50px;
  height: 24px;
  background: #ccc;
  border-radius: 24px;
  transition: all 0.3s ease;
}

.toggle-slider::before {
  content: '';
  position: absolute;
  top: 2px;
  left: 2px;
  width: 20px;
  height: 20px;
  background: white;
  border-radius: 50%;
  transition: all 0.3s ease;
}

.visibility-checkbox:checked + .toggle-slider {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
}

.visibility-checkbox:checked + .toggle-slider::before {
  transform: translateX(26px);
}

.toggle-text {
  font-size: 0.9rem;
  transition: color 0.3s ease;
}

/* Slider actions spacing */
.slider-actions {
  gap: 24px !important;
  margin-top: 32px !important;
  padding: 24px 0 !important;
}

.slider-actions button {
  margin: 0 8px !important;
}

.btn-primary {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.3);
}

.btn-primary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(107, 79, 63, 0.4);
}

.btn-secondary {
  background: linear-gradient(135deg, var(--accent), var(--primary));
  color: white;
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.3);
}

.btn-secondary:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(107, 79, 63, 0.4);
}

.btn-outline {
  background: rgba(107, 79, 63, 0.1);
  color: var(--text-secondary);
  border: 2px solid rgba(107, 79, 63, 0.2);
}

.btn-outline:hover {
  background: rgba(107, 79, 63, 0.2);
  color: var(--text-primary);
  transform: translateY(-2px);
}

/* Image preview size */
.slide-preview {
  width: 300px;
  height: 200px;
  object-fit: cover;
  border-radius: 8px;
  border: 2px solid rgba(107, 79, 63, 0.2);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Mobile styles */
@media (max-width: 767px) {
  .admin-management-page {
    padding-top: 60px; /* Account for fixed nav */
  }

  .admin-content {
    padding: 20px 15px;
  }

  .admin-header h1 {
    font-size: 2rem;
  }

  .form-card, .list-card {
    padding: 24px 20px;
  }

  .form-row {
    grid-template-columns: 1fr;
  }

  .admin-grid {
    grid-template-columns: 1fr;
  }

  .admin-card {
    flex-direction: column;
    text-align: center;
    gap: 12px;
  }

  .admin-actions {
    align-self: center;
  }

  .form-actions {
    flex-direction: column;
    gap: 12px;
  }

  .cancel-btn, .submit-btn {
    width: 100%;
    justify-content: center;
  }

  .list-header {
    flex-direction: column;
    align-items: stretch;
    gap: 12px;
  }

  .message-container {
    padding: 16px;
    flex-direction: column;
    gap: 12px;
    text-align: center;
  }

  .message-icon {
    align-self: center;
  }

  .message-close {
    position: static;
    align-self: center;
    margin-top: 8px;
  }
}
</style>
