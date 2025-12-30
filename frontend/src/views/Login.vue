<template>
  <div class="login-page">
    <!-- Image Slider Section -->
    <div class="image-slider-section" :style="{
      backgroundImage: currentSlideImage ? `url('${currentSlideImage}')` : 'linear-gradient(135deg, var(--primary) 0%, var(--secondary) 50%, var(--accent) 100%)'
    }">
      <div class="slider-container">
        <div class="slide" v-for="(slide, index) in visibleSlides" :key="index"
             :class="{ active: getVisibleSlideIndex(slide) === currentVisibleSlide }">
          <div class="slide-content">
            <div class="slide-image">
              <div class="family-illustration" v-html="slide.svg"></div>
            </div>
            <div class="slide-text">
              <h3>{{ slide.title }}</h3>
              <p>{{ slide.description }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Slider Navigation -->
      <div class="slider-nav">
        <button
          v-for="(slide, index) in visibleSlides"
          :key="index"
          :class="{ active: getVisibleSlideIndex(slide) === currentVisibleSlide }"
          @click="goToVisibleSlide(getVisibleSlideIndex(slide))"
          class="nav-dot"
        ></button>
      </div>

      <!-- Debug/Refresh Button (Hidden in production) -->
      <!-- <div class="debug-controls" style="position: absolute; top: 10px; right: 10px; opacity: 0.3;">
        <button @click="forceReloadSlides" class="debug-btn" title="Refresh Slides">
          🔄
        </button>
      </div> -->
    </div>

    <!-- Login Form Section -->
    <div class="login-section">
      <div class="login-container">
        <div class="login-header">
          <div class="logo">
            <div class="logo-icon">👨‍👩‍👧‍👦</div>
          </div>
          <h1>Selamat Datang</h1>
          <p>Masuk ke akun Anda untuk mengelola data keluarga</p>
        </div>

        <form @submit.prevent="login" class="login-form">
          <div class="form-group">
            <label for="email">Email</label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/>
              </svg>
              <input
                type="email"
                id="email"
                v-model="email"
                placeholder="Masukkan email Anda"
                required
              >
            </div>
          </div>

          <div class="form-group">
            <label for="password">Password</label>
            <div class="input-wrapper">
              <svg class="input-icon" viewBox="0 0 24 24" fill="currentColor">
                <path d="M18 8h-1V6c0-2.76-2.24-5-5-5S7 3.24 7 6v2H6c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2h12c1.1 0 2-.9 2-2V10c0-1.1-.9-2-2-2zM9 6c0-1.66 1.34-3 3-3s3 1.34 3 3v2H9V6zm3 9c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2z"/>
              </svg>
              <input
                type="password"
                id="password"
                v-model="password"
                placeholder="Masukkan password Anda"
                required
              >
            </div>
          </div>

          <button type="submit" :disabled="loading" class="login-btn">
            <span v-if="loading" class="loading-spinner"></span>
            <span>{{ loading ? 'Sedang Masuk...' : 'Masuk ke Akun' }}</span>
          </button>
        </form>

        <div v-if="error" class="error-message">
          <svg class="error-icon" viewBox="0 0 24 24" fill="currentColor">
            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
          </svg>
          <span>{{ error }}</span>
        </div>

        <div class="login-footer">
          <p>
            Belum punya akun?
            <a href="#" @click.prevent="showRegisterHint">Hubungi Admin</a>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

// Slider functionality
const currentSlide = ref(0)
const slides = ref([])

// Computed property for current slide image
const currentSlideImage = computed(() => {
  if (slides.value.length > 0 && slides.value[currentSlide.value]) {
    return slides.value[currentSlide.value].image
  }
  return null
})

// Computed property for visible slides only
const visibleSlides = computed(() => {
  return slides.value.filter(slide => {
    // Handle both boolean and integer (0/1) values from database
    const isVisible = slide.is_visible
    return isVisible === true || isVisible === 1
  })
})

// Computed property for current visible slide index
const currentVisibleSlide = computed(() => {
  const currentSlideData = slides.value[currentSlide.value]
  if (!currentSlideData) return 0
  return visibleSlides.value.findIndex(slide => slide === currentSlideData)
})

// Function to get visible slide index
const getVisibleSlideIndex = (slide) => {
  return visibleSlides.value.findIndex(s => s === slide)
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

        <!-- Tree -->
        <rect x="95" y="80" width="10" height="40" class="tree" rx="2"/>
        <path d="M90 120 L100 110 L110 120 L105 125 L95 125 Z" class="tree"/>

        <!-- Family members -->
        <circle cx="85" cy="50" r="8" class="family-circle"/>
        <circle cx="115" cy="50" r="8" class="family-circle"/>
        <circle cx="75" cy="75" r="6" class="family-circle"/>
        <circle cx="100" cy="80" r="6" class="family-circle"/>
        <circle cx="125" cy="75" r="6" class="family-circle"/>

        <!-- Connection lines -->
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

        <!-- Documents -->
        <rect x="70" y="50" width="25" height="35" class="document" rx="2"/>
        <rect x="105" y="45" width="25" height="35" class="document" rx="2"/>
        <rect x="75" y="55" width="20" height="30" class="document" rx="2"/>
        <rect x="110" y="50" width="20" height="30" class="document" rx="2"/>

        <!-- Status indicators -->
        <circle cx="82" cy="42" r="4" class="check"/>
        <circle cx="117" cy="37" r="4" class="pending"/>
        <circle cx="85" cy="47" r="4" class="pending"/>
        <circle cx="120" cy="42" r="4" class="pending"/>

        <!-- Check marks -->
        <path d="M79 40 L81 42 L86 37" stroke="white" stroke-width="1.5" fill="none"/>
        <path d="M114 35 L116 37 L121 32" stroke="white" stroke-width="1.5" fill="none"/>

        <!-- Admin person -->
        <circle cx="150" cy="70" r="12" class="person"/>
        <rect x="145" y="82" width="10" height="15" class="person" rx="2"/>
        <rect x="143" y="82" width="14" height="6" class="person" rx="2"/>

        <!-- User person -->
        <circle cx="50" cy="70" r="10" class="person"/>
        <rect x="46" y="80" width="8" height="12" class="person" rx="2"/>
        <rect x="45" y="80" width="10" height="5" class="person" rx="2"/>

        <!-- Arrow -->
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

        <!-- Root (Grandparents) -->
        <circle cx="100" cy="30" r="8" class="node"/>
        <text x="100" y="35" text-anchor="middle" font-size="8" fill="#6B4F3F">1</text>

        <!-- Parents -->
        <circle cx="80" cy="60" r="7" class="node"/>
        <circle cx="120" cy="60" r="7" class="node"/>
        <text x="80" y="65" text-anchor="middle" font-size="7" fill="#6B4F3F">2</text>
        <text x="120" y="65" text-anchor="middle" font-size="7" fill="#6B4F3F">3</text>

        <!-- Children -->
        <circle cx="60" cy="90" r="6" class="highlight"/>
        <circle cx="100" cy="95" r="6" class="node"/>
        <circle cx="140" cy="90" r="6" class="node"/>
        <text x="60" y="95" text-anchor="middle" font-size="6" fill="#6B4F3F">4</text>
        <text x="100" y="100" text-anchor="middle" font-size="6" fill="#6B4F3F">5</text>
        <text x="140" y="95" text-anchor="middle" font-size="6" fill="#6B4F3F">6</text>

        <!-- Grandchildren -->
        <circle cx="100" cy="120" r="5" class="node"/>
        <text x="100" y="125" text-anchor="middle" font-size="5" fill="#6B4F3F">7</text>

        <!-- Connections -->
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

        <!-- Shield -->
        <path d="M100 25 L130 45 L130 85 L100 105 L70 85 L70 45 Z" class="shield"/>
        <path d="M100 25 L130 45 L130 85 L100 105 L70 85 L70 45 Z" class="shield-outline"/>

        <!-- Lock -->
        <rect x="88" y="55" width="24" height="16" class="lock" rx="2"/>
        <rect x="95" y="65" width="10" height="6" fill="none" stroke="#6B4F3F" stroke-width="1"/>
        <circle cx="100" cy="62" r="3" fill="none" stroke="#6B4F3F" stroke-width="1"/>
        <rect x="99" y="59" width="2" height="6" fill="#6B4F3F"/>
        <circle cx="100" cy="70" r="2" fill="#6B4F3F"/>

        <!-- Security icons -->
        <circle cx="85" cy="75" r="4" class="secure-icon"/>
        <circle cx="115" cy="75" r="4" class="secure-icon"/>
        <circle cx="100" cy="85" r="4" class="secure-icon"/>

        <!-- Check marks -->
        <path d="M82 73 L84 75 L89 70" stroke="white" stroke-width="1" fill="none"/>
        <path d="M112 73 L114 75 L119 70" stroke="white" stroke-width="1" fill="none"/>
        <path d="M97 83 L99 85 L104 80" stroke="white" stroke-width="1" fill="none"/>
      </svg>
    `
  }
]

// Load slides from API
const loadSlides = async () => {
  console.log('🔄 Login.vue - Loading slides from API...')

  try {
    const response = await api.get('/slider-data')
    const slidesData = response.data

    console.log('📦 API response:', slidesData)

    slides.value = slidesData.map((slide, index) => {
      console.log(`🖼️ Slide ${index} (${slide.title}):`, {
        hasImage: !!slide.image,
        isVisible: slide.is_visible,
        imageLength: slide.image ? slide.image.length : 0,
        imagePreview: slide.image ? slide.image.substring(0, 50) + '...' : 'NO IMAGE'
      })

      return {
        title: slide.title,
        description: slide.description,
        image: slide.image || null,
        is_visible: slide.is_visible,
        svg: defaultSlides[index].svg // Always keep default SVG as fallback
      }
    })

    console.log('✅ Final slides array loaded from API:', slides.value.map(s => ({
      title: s.title,
      hasImage: !!s.image,
      imageType: s.image ? 'URL' : null
    })))

  } catch (error) {
    console.error('❌ Error loading slides from API:', error)

    // Fallback to default slides
    console.log('📭 Using default slides as fallback')
    slides.value = defaultSlides.map(slide => ({
      title: slide.title,
      description: slide.description,
      image: null,
      svg: slide.svg
    }))
  }

  console.log('🎯 Current slides state:', slides.value)
}

let slideInterval = null

const login = async () => {
  loading.value = true
  error.value = ''
  try {
    const response = await api.post('/login', { email: email.value, password: password.value })
    localStorage.setItem('token', response.data.token)
    localStorage.setItem('role', response.data.user.role)
    localStorage.setItem('user_email', response.data.user.email)
    router.push('/dashboard')
  } catch (err) {
    error.value = 'Login gagal. Periksa email dan password.'
  } finally {
    loading.value = false
  }
}

const goToSlide = (index) => {
  currentSlide.value = index
}

const goToVisibleSlide = (visibleIndex) => {
  if (visibleSlides.value[visibleIndex]) {
    const slideIndex = slides.value.findIndex(slide => slide === visibleSlides.value[visibleIndex])
    if (slideIndex !== -1) {
      currentSlide.value = slideIndex
    }
  }
}

const nextSlide = () => {
  if (visibleSlides.value.length === 0) return

  // Find the next visible slide
  let nextVisibleIndex = (currentVisibleSlide.value + 1) % visibleSlides.value.length
  let nextSlideData = visibleSlides.value[nextVisibleIndex]

  // Find the index of this slide in the original slides array
  const originalIndex = slides.value.findIndex(slide => slide === nextSlideData)
  if (originalIndex !== -1) {
    currentSlide.value = originalIndex
  }
}

const showRegisterHint = () => {
  alert('Untuk membuat akun baru, silakan hubungi administrator sistem.')
}

const forceReloadSlides = () => {
  console.log('Force reloading slides...')
  loadSlides()
}

// Listen for storage changes (when admin updates slides)
const handleStorageChange = (event) => {
  console.log('🔄 Storage event detected:', {
    key: event.key,
    newValue: event.newValue,
    oldValue: event.oldValue,
    storageArea: event.storageArea
  })

  if (event.key === 'loginSlides') {
    console.log('localStorage loginSlides changed, reloading slides...')
    loadSlides()
  } else {
    console.log('Storage event ignored (not loginSlides)')
  }
}

onMounted(() => {
  loadSlides()
  // Auto-slide every 4 seconds
  slideInterval = setInterval(nextSlide, 4000)

  // Listen for localStorage changes
  window.addEventListener('storage', handleStorageChange)
})

onUnmounted(() => {
  if (slideInterval) {
    clearInterval(slideInterval)
  }
  window.removeEventListener('storage', handleStorageChange)
})
</script>

<style scoped>
.login-page {
  display: flex;
  min-height: 100vh;
  background: linear-gradient(135deg, var(--bg-main) 0%, var(--bg-card) 100%);
}

/* Image Slider Section */
.image-slider-section {
  flex: 1.8;
  background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 50%, var(--accent) 100%);
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  padding: 40px 20px;
}

.slider-container {
  position: relative;
  width: 100%;
  max-width: 400px;
  height: 400px;
  overflow: hidden;
  border-radius: 20px;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.slide {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  opacity: 0;
  transition: opacity 0.8s ease-in-out;
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(20px);
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-radius: 20px;
  background-size: cover;
  background-position: center;
  background-repeat: no-repeat;
}

.slide.active {
  opacity: 1;
}

.slide-content {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  padding: 30px;
  text-align: center;
}

.slide-image {
  margin-bottom: 30px;
  width: 180px;
  height: 120px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.family-illustration {
  width: 100%;
  height: 100%;
}

.family-illustration svg {
  width: 100%;
  height: 100%;
}

.slide-image-uploaded {
  width: 100%;
  height: 100%;
  object-fit: cover;
  border-radius: 12px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.slide-text h3 {
  color: var(--text-primary);
  font-size: 1.5rem;
  font-weight: 700;
  margin: 0 0 15px 0;
  line-height: 1.3;
}

.slide-text p {
  color: var(--text-secondary);
  font-size: 1rem;
  margin: 0;
  line-height: 1.5;
  max-width: 280px;
}

.slider-nav {
  display: flex;
  gap: 12px;
  margin-top: 30px;
}

.nav-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  border: none;
  background: rgba(255, 255, 255, 0.4);
  cursor: pointer;
  transition: all 0.3s ease;
}

.nav-dot:hover {
  background: rgba(255, 255, 255, 0.7);
  transform: scale(1.2);
}

.nav-dot.active {
  background: white;
  transform: scale(1.1);
}

/* Login Form Section */
.login-section {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 40px 20px;
  background: var(--bg-card);
}

.login-container {
  width: 100%;
  max-width: 420px;
  background: white;
  border-radius: 24px;
  padding: 50px 40px;
  box-shadow: 0 20px 60px rgba(107, 79, 63, 0.1);
  border: 1px solid rgba(217, 177, 130, 0.1);
}

.login-header {
  text-align: center;
  margin-bottom: 40px;
}

.logo {
  margin-bottom: 24px;
}

.logo-icon {
  font-size: 4rem;
  filter: drop-shadow(0 4px 12px rgba(107, 79, 63, 0.2));
}

.login-header h1 {
  color: var(--text-primary);
  font-size: 2.2rem;
  font-weight: 700;
  margin: 0 0 12px 0;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.login-header p {
  color: var(--text-secondary);
  font-size: 1.1rem;
  margin: 0;
  line-height: 1.5;
}

.login-form {
  margin-bottom: 30px;
}

.form-group {
  margin-bottom: 24px;
}

.form-group label {
  display: block;
  color: var(--text-secondary);
  font-size: 0.95rem;
  font-weight: 600;
  margin-bottom: 8px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.input-wrapper {
  position: relative;
}

.input-icon {
  position: absolute;
  left: 16px;
  top: 50%;
  transform: translateY(-50%);
  width: 20px;
  height: 20px;
  color: var(--text-muted);
  z-index: 1;
}

.input-wrapper input {
  width: 100%;
  padding: 16px 16px 16px 52px;
  border: 2px solid rgba(107, 79, 63, 0.2);
  border-radius: 12px;
  font-size: 1rem;
  background: var(--bg-main);
  transition: all 0.3s ease;
  box-sizing: border-box;
}

.input-wrapper input:focus {
  outline: none;
  border-color: var(--primary);
  background: white;
  box-shadow: 0 0 0 4px rgba(107, 79, 63, 0.1);
}

.input-wrapper input::placeholder {
  color: var(--text-muted);
}

.login-btn {
  width: 100%;
  padding: 16px 24px;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  border: none;
  border-radius: 12px;
  font-size: 1.1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  box-shadow: 0 4px 16px rgba(107, 79, 63, 0.3);
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
}

.login-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 8px 24px rgba(107, 79, 63, 0.4);
}

.login-btn:disabled {
  background: #ccc;
  box-shadow: none;
  transform: none;
  cursor: not-allowed;
}

.loading-spinner {
  width: 20px;
  height: 20px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top: 2px solid white;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.error-message {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: #fef2f2;
  border: 1px solid #fecaca;
  border-radius: 12px;
  color: #dc2626;
  font-size: 0.95rem;
  margin-bottom: 24px;
}

.error-icon {
  width: 20px;
  height: 20px;
  flex-shrink: 0;
}

.login-footer {
  text-align: center;
  padding-top: 24px;
  border-top: 1px solid rgba(107, 79, 63, 0.1);
}

.login-footer p {
  color: var(--text-secondary);
  font-size: 0.95rem;
  margin: 0 0 8px 0;
}

.login-footer a {
  color: var(--primary);
  text-decoration: none;
  font-weight: 600;
  transition: color 0.3s ease;
}

.login-footer a:hover {
  color: var(--secondary);
}

/* Responsive Design */
@media (max-width: 1024px) {
  .login-page {
    flex-direction: column;
  }

  .image-slider-section {
    flex: none;
    min-height: 300px;
    padding: 40px 20px;
  }

  .slider-container {
    max-width: 350px;
    height: 280px;
  }

  .login-section {
    flex: none;
    padding: 40px 20px;
  }

  .login-container {
    padding: 40px 30px;
  }
}

@media (max-width: 768px) {
  .login-page {
    min-height: auto;
  }

  .image-slider-section {
    padding: 30px 15px;
    min-height: 250px;
  }

  .slider-container {
    max-width: 320px;
    height: 240px;
  }

  .slide-content {
    padding: 20px;
  }

  .slide-image {
    width: 150px;
    height: 100px;
    margin-bottom: 20px;
  }

  .slide-text h3 {
    font-size: 1.3rem;
  }

  .slide-text p {
    font-size: 0.95rem;
  }

  .login-section {
    padding: 30px 15px;
  }

  .login-container {
    padding: 30px 25px;
  }

  .login-header h1 {
    font-size: 1.8rem;
  }

  .login-header p {
    font-size: 1rem;
  }

  .form-group {
    margin-bottom: 20px;
  }

  .input-wrapper input {
    padding: 14px 14px 14px 48px;
    font-size: 16px; /* Prevent zoom on iOS */
  }

  .input-icon {
    left: 14px;
    width: 18px;
    height: 18px;
  }
}

@media (max-width: 480px) {
  .image-slider-section {
    padding: 20px 10px;
    min-height: 200px;
  }

  .slider-container {
    max-width: 280px;
    height: 200px;
  }

  .slide-content {
    padding: 15px;
  }

  .slide-image {
    width: 120px;
    height: 80px;
    margin-bottom: 15px;
  }

  .slide-text h3 {
    font-size: 1.1rem;
  }

  .slide-text p {
    font-size: 0.9rem;
  }

  .slider-nav {
    margin-top: 20px;
  }

  .nav-dot {
    width: 10px;
    height: 10px;
  }

  .login-section {
    padding: 20px 10px;
  }

  .login-container {
    padding: 25px 20px;
  }

  .login-header {
    margin-bottom: 30px;
  }

  .logo-icon {
    font-size: 3rem;
  }

  .login-header h1 {
    font-size: 1.6rem;
  }

  .login-header p {
    font-size: 0.95rem;
  }
}
</style>
