<template>
  <div class="dashboard-page">
    <Navigation />

    <div class="dashboard-content">
      <!-- Hero Section -->
      <section class="hero-section">
        <div class="hero-content">
          <div class="hero-text">
            <h1 class="hero-title">
              <span class="hero-greeting">Selamat datang di</span>
              <span class="hero-family-name">{{ familyName }}</span>
            </h1>
            <p class="hero-subtitle">Halo {{ userName }}, kelola data keluarga Anda dengan mudah dan teratur</p>
          </div>
          <div class="hero-visual">
            <div class="family-icon">👨‍👩‍👧‍👦</div>
          </div>
        </div>
      </section>

      <!-- Quick Stats -->
      <section class="stats-section">
        <div class="stats-container">
          <div class="stat-card">
            <div class="stat-icon">👨‍👩‍👧‍👦</div>
            <div class="stat-content">
              <h3 class="stat-number">{{ totalMembers }}</h3>
              <p class="stat-label">Total Anggota</p>
            </div>
          </div>
          <div v-if="isAdmin" class="stat-card">
            <div class="stat-icon">⏳</div>
            <div class="stat-content">
              <h3 class="stat-number">{{ pendingRequests }}</h3>
              <p class="stat-label">Request Pending</p>
            </div>
          </div>
          <div class="stat-card">
            <div class="stat-icon">🌳</div>
            <div class="stat-content">
              <h3 class="stat-number">{{ maxGeneration }}</h3>
              <p class="stat-label">Generasi</p>
            </div>
          </div>
        </div>
      </section>

      <!-- Main Features -->
      <section class="features-section">
        <div class="section-header">
          <h2>Fitur Utama</h2>
          <p>Akses cepat ke semua fitur aplikasi</p>
        </div>

        <div class="features-grid">
          <!-- Primary Features -->
          <div class="feature-card primary" @click="$router.push('/family-tree')">
            <div class="feature-icon">🌳</div>
            <div class="feature-content">
              <h3>Pohon Keluarga</h3>
              <p>Tampilkan pohon keluarga lengkap dengan visualisasi interaktif</p>
            </div>
            <div class="feature-arrow">→</div>
          </div>

          <div class="feature-card primary" @click="goToProfile">
            <div class="feature-icon">👤</div>
            <div class="feature-content">
              <h3>Profil Saya</h3>
              <p>Lihat dan kelola profil pribadi Anda</p>
            </div>
            <div class="feature-arrow">→</div>
          </div>

          <!-- Member Features -->
          <div v-if="isMember" class="feature-card secondary" @click="$router.push('/request-update')">
            <div class="feature-icon">📝</div>
            <div class="feature-content">
              <h3>Ajukan Perubahan</h3>
              <p>Kirim permintaan update data keluarga</p>
            </div>
            <div class="feature-arrow">→</div>
          </div>

          <div v-if="isMember" class="feature-card secondary" @click="openAddMemberModal">
            <div class="feature-icon">👶</div>
            <div class="feature-content">
              <h3>Tambah Anggota</h3>
              <p>Ajukan penambahan anak atau menantu baru</p>
            </div>
            <div class="feature-arrow">→</div>
          </div>

          <!-- Admin Features -->
          <div v-if="isAdmin" class="feature-card admin" @click="$router.push('/admin')">
            <div class="feature-icon">⚙️</div>
            <div class="feature-content">
              <h3>Panel Admin</h3>
              <p>Kelola data keluarga dan approve requests</p>
            </div>
            <div class="feature-arrow">→</div>
          </div>
        </div>
      </section>

      <!-- Add Member Modal -->
      <div v-if="showAddMemberModal" class="modal-overlay" @click="closeAddMemberModal">
        <div class="modal-content" @click.stop>
          <h3>Tambah Anggota Keluarga Baru</h3>
          <form @submit.prevent="submitAddMemberRequest" class="add-member-form">
            <div class="form-section">
              <h4 class="section-title">👤 Informasi Dasar</h4>
              <div class="form-row">
                <div class="form-group">
                  <label for="memberName">Nama Lengkap</label>
                  <input
                    id="memberName"
                    v-model="addMemberForm.name"
                    type="text"
                    required
                    class="form-input"
                    placeholder="Masukkan nama lengkap"
                  >
                </div>
                <div class="form-group">
                  <label for="memberEmail">Email (opsional)</label>
                  <input
                    id="memberEmail"
                    v-model="addMemberForm.email"
                    type="email"
                    class="form-input"
                    placeholder="contoh@email.com"
                  >
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label for="memberPhone">Nomor Telepon (opsional)</label>
                  <input
                    id="memberPhone"
                    v-model="addMemberForm.phone"
                    type="tel"
                    class="form-input"
                    placeholder="+628xxxxxxxxx"
                  >
                </div>
                <div class="form-group">
                  <label for="memberGender">Jenis Kelamin</label>
                  <select id="memberGender" v-model="addMemberForm.gender" required class="form-select">
                    <option value="male">Laki-laki</option>
                    <option value="female">Perempuan</option>
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label for="memberBirthDate">Tanggal Lahir (opsional)</label>
                  <input
                    id="memberBirthDate"
                    v-model="addMemberForm.birth_date"
                    type="date"
                    class="form-input"
                  >
                </div>
                <div class="form-group">
                  <label for="memberDeathDate">Tanggal Meninggal (opsional)</label>
                  <input
                    id="memberDeathDate"
                    v-model="addMemberForm.death_date"
                    type="date"
                    class="form-input"
                  >
                </div>
              </div>
            </div>

            <div class="form-section">
              <h4 class="section-title">👨‍👩‍👧‍👦 Hubungan Keluarga</h4>
              <div class="form-row">
                <div class="form-group">
                  <label for="relationshipType">Jenis Hubungan</label>
                  <select id="relationshipType" v-model="addMemberForm.relationship_type" required class="form-select">
                    <option value="child">Anak</option>
                    <option value="spouse">Pasangan (Menantu)</option>
                  </select>
                </div>
                <div v-if="addMemberForm.relationship_type === 'spouse'" class="form-group">
                  <label for="relatedMember">Hubungkan dengan</label>
                  <select id="relatedMember" v-model="addMemberForm.related_member_id" required class="form-select">
                    <option value="">Pilih anggota keluarga...</option>
                    <option v-for="member in userChildren" :key="member.id" :value="member.id">
                      {{ member.name }} ({{ member.gender === 'male' ? 'Ayah' : 'Ibu' }})
                    </option>
                  </select>
                </div>
              </div>
              <div v-if="addMemberForm.relationship_type === 'spouse' && userChildren.length === 0" class="form-note">
                <small style="color: #666; font-style: italic;">
                  Tidak ada anak yang terdaftar. Anda perlu menambahkan anak terlebih dahulu sebelum menambahkan pasangan.
                </small>
              </div>
            </div>

            <div class="form-section">
              <h4 class="section-title">📎 Foto Profil</h4>
              <div class="photo-upload-section">
                <div class="current-photo">
                  <img v-if="photoPreview" :src="photoPreview" :alt="'Foto ' + addMemberForm.name" class="photo-preview">
                  <div v-else class="photo-placeholder">
                    {{ addMemberForm.gender === 'male' ? '👨' : addMemberForm.gender === 'female' ? '👩' : '👤' }}
                  </div>
                </div>
                <div class="photo-controls">
                  <input
                    type="file"
                    id="memberPhoto"
                    @change="handlePhotoChange"
                    accept="image/*"
                    class="photo-input"
                  >
                  <label for="memberPhoto" class="photo-upload-btn">
                    <span class="btn-icon">📷</span>
                    <span>Pilih Foto</span>
                  </label>
                  <button type="button" @click="removePhoto" class="photo-remove-btn" v-if="photoPreview">
                    <span class="btn-icon">🗑️</span>
                    <span>Hapus Foto</span>
                  </button>
                </div>
              </div>
            </div>

            <div class="form-section">
              <h4 class="section-title">📝 Catatan Tambahan</h4>
              <div class="form-group">
                <label for="memberNotes">Catatan (opsional)</label>
                <textarea
                  id="memberNotes"
                  v-model="addMemberForm.notes"
                  class="form-textarea"
                  rows="3"
                  placeholder="Tambahkan catatan tentang anggota keluarga ini..."
                ></textarea>
              </div>
            </div>

            <div class="form-actions">
              <button type="button" @click="closeAddMemberModal" class="cancel-btn">
                <span class="btn-icon">❌</span>
                <span>Batal</span>
              </button>
              <button type="submit" :disabled="submittingRequest" class="submit-btn">
                <span class="btn-icon">{{ submittingRequest ? '⏳' : '📤' }}</span>
                <span>{{ submittingRequest ? 'Mengirim...' : 'Kirim Permintaan' }}</span>
              </button>
            </div>
          </form>
        </div>
      </div>


    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import Navigation from '../components/Navigation.vue'
import api from '../services/api'

const router = useRouter()
const userName = ref('')
const userId = ref('')
const isAdmin = ref(false)
const isMember = ref(false)
const totalMembers = ref(0)
const pendingRequests = ref(0)
const maxGeneration = ref(0)
const showAddMemberModal = ref(false)
const submittingRequest = ref(false)
const familyMembers = ref([])
const userInfo = ref(null)
const photoPreview = ref(null)
const familyName = ref('Keluarga Besar')

const addMemberForm = ref({
  name: '',
  email: '',
  phone: '',
  gender: 'male',
  birth_date: '',
  death_date: '',
  relationship_type: 'child',
  related_member_id: '',
  notes: '',
  photo: null
})

const userChildren = computed(() => {
  // Get current user info from the stored user data
  const currentUser = userInfo.value

  if (!currentUser || !currentUser.email) {
    return []
  }

  // Find the current user's family member record
  const currentUserMember = familyMembers.value.find(member => member.email === currentUser.email)

  if (!currentUserMember) {
    return []
  }

  // Filter family members to only show children of the current user
  const children = familyMembers.value.filter(member =>
    member.father_id == currentUserMember.id || member.mother_id == currentUserMember.id
  )

  return children
})

onMounted(async () => {
  // Get user info from API (same as Profile.vue)
  try {
    const userResponse = await api.get('/user')
    userInfo.value = userResponse.data
    userName.value = userResponse.data.name || 'User'
    userId.value = userResponse.data.id || '1'
  } catch (error) {
    console.error('Error loading user info:', error)
    userName.value = 'User'
    userId.value = '1'
  }

  // Get family name from API
  try {
    console.log('Dashboard: Loading family name from API...')
    const familyResponse = await api.get('/family-name')
    console.log('Dashboard: Family API response:', familyResponse.data)
    familyName.value = familyResponse.data.family_name || 'Keluarga Besar'
    console.log('Dashboard: Set family name to:', familyName.value)
  } catch (error) {
    console.error('Dashboard: Error loading family name:', error)
    console.error('Dashboard: Error details:', error.response)
    // Keep default value
  }

  const role = localStorage.getItem('role')
  isAdmin.value = role === 'admin'
  isMember.value = role === 'member'

  // Load stats
  await loadStats()
})

const loadStats = async () => {
  try {
    // Load family members count and list
    const membersResponse = await api.get('/family-members')
    familyMembers.value = membersResponse.data
    totalMembers.value = membersResponse.data.length

    // Calculate max generation
    if (membersResponse.data.length > 0) {
      maxGeneration.value = Math.max(...membersResponse.data.map(m => m.generation_level))
    }

    // Load pending requests count (only for admin)
    if (isAdmin.value) {
      const requestsResponse = await api.get('/update-requests?status=pending')
      pendingRequests.value = requestsResponse.data.length
    }
  } catch (error) {
    console.error('Error loading stats:', error)
  }
}

const goToProfile = () => {
  // Navigate to own profile - Profile component will handle finding the correct member
  router.push('/profile')
}

const openAddMemberModal = () => {
  // Reset form and open modal
  addMemberForm.value = {
    name: '',
    email: '',
    phone: '',
    gender: 'male',
    birth_date: '',
    death_date: '',
    relationship_type: 'child',
    related_member_id: '',
    notes: '',
    photo: null
  }
  photoPreview.value = null
  showAddMemberModal.value = true
}

const submitAddMemberRequest = async () => {
  submittingRequest.value = true

  try {
    // Find current user's family member ID
    const currentUserEmail = userInfo.value?.email
    const currentUserMember = familyMembers.value.find(member => member.email === currentUserEmail)

    if (!currentUserMember) {
      alert('Tidak dapat menemukan data anggota Anda. Silakan refresh halaman.')
      return
    }

    // Prepare request data
    const requestData = {
      change_type: 'add_member',
      member_data: { ...addMemberForm.value },
      target_member_id: null, // Will be set based on relationship type
      notes: `Permintaan penambahan anggota keluarga: ${addMemberForm.value.name}`
    }

    // Set target_member_id based on relationship type
    if (addMemberForm.value.relationship_type === 'child') {
      // For child: target is the current user (parent)
      requestData.target_member_id = currentUserMember.id
      delete requestData.member_data.related_member_id // Not needed for child
    } else if (addMemberForm.value.relationship_type === 'spouse') {
      // For spouse: target is the selected child
      if (!addMemberForm.value.related_member_id) {
        alert('Silakan pilih anak yang akan dihubungkan dengan pasangan.')
        return
      }
      requestData.target_member_id = addMemberForm.value.related_member_id
      delete requestData.member_data.related_member_id // Use target_member_id instead
    }

    await api.post('/update-requests', requestData)

    alert('Permintaan penambahan anggota berhasil dikirim! Admin akan meninjau permintaan Anda.')

    closeAddMemberModal()

  } catch (error) {
    console.error('Error submitting add member request:', error)
    alert('Gagal mengirim permintaan: ' + (error.response?.data?.message || error.message))
  } finally {
    submittingRequest.value = false
  }
}

const closeAddMemberModal = () => {
  showAddMemberModal.value = false
}

const handlePhotoChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    addMemberForm.value.photo = file

    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      photoPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const removePhoto = () => {
  addMemberForm.value.photo = null
  photoPreview.value = null
  // Reset file input
  const fileInput = document.getElementById('memberPhoto')
  if (fileInput) {
    fileInput.value = ''
  }
}
</script>

<style scoped>
.dashboard-page {
  min-height: 100vh;
  background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
  position: relative;
}

.dashboard-page::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background:
    radial-gradient(circle at 20% 80%, rgba(107, 79, 63, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(166, 124, 82, 0.1) 0%, transparent 50%),
    radial-gradient(circle at 40% 40%, rgba(66, 184, 131, 0.05) 0%, transparent 50%);
  pointer-events: none;
}

.dashboard-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 20px;
  position: relative;
  z-index: 1;
}

/* Hero Section */
.hero-section {
  margin-bottom: 60px;
}

.hero-content {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(249, 243, 237, 0.9));
  backdrop-filter: blur(20px);
  border-radius: 24px;
  padding: 60px 40px;
  box-shadow: 0 20px 60px rgba(107, 79, 63, 0.1);
  border: 1px solid rgba(107, 79, 63, 0.08);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 40px;
  position: relative;
  overflow: hidden;
}

.hero-content::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(107, 79, 63, 0.03), transparent);
  animation: shimmer 3s infinite;
}

@keyframes shimmer {
  0% { left: -100%; }
  100% { left: 100%; }
}

.hero-text {
  flex: 1;
  max-width: 600px;
}

.hero-title {
  font-size: 3.2rem;
  font-weight: 800;
  margin-bottom: 16px;
  line-height: 1.1;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.hero-greeting {
  display: block;
  font-size: 1.8rem;
  font-weight: 600;
  color: var(--text-secondary);
  margin-bottom: 8px;
}

.hero-name {
  display: block;
  font-size: 2.8rem;
  font-weight: 900;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-shadow: 0 2px 4px rgba(107, 79, 63, 0.1);
}

.hero-family-name {
  display: block;
  font-size: 3.2rem;
  font-weight: 900;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  text-shadow: 0 2px 4px rgba(107, 79, 63, 0.1);
}

.hero-subtitle {
  font-size: 1.3rem;
  color: var(--text-secondary);
  line-height: 1.6;
  margin: 0;
  font-weight: 500;
}

.hero-visual {
  flex-shrink: 0;
}

.family-icon {
  font-size: 8rem;
  filter: drop-shadow(0 8px 24px rgba(107, 79, 63, 0.2));
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

/* Quick Stats */
.stats-section {
  margin-bottom: 80px;
}

.stats-container {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
  gap: 30px;
  max-width: 1200px;
  margin: 0 auto;
}

.stat-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(249, 243, 237, 0.9));
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 32px;
  display: flex;
  align-items: center;
  gap: 24px;
  box-shadow: 0 12px 40px rgba(107, 79, 63, 0.08);
  border: 1px solid rgba(107, 79, 63, 0.06);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
}

.stat-card:hover {
  transform: translateY(-8px) scale(1.02);
  box-shadow: 0 20px 60px rgba(107, 79, 63, 0.12);
}

.stat-icon {
  font-size: 3.5rem;
  opacity: 0.85;
  filter: drop-shadow(0 2px 8px rgba(107, 79, 63, 0.1));
  transition: transform 0.3s ease;
}

.stat-card:hover .stat-icon {
  transform: scale(1.1);
}

.stat-content {
  flex: 1;
}

.stat-content h3 {
  font-size: 2.8rem;
  font-weight: 900;
  margin: 0 0 8px 0;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  line-height: 1;
}

.stat-label {
  font-size: 1rem;
  font-weight: 600;
  color: var(--text-secondary);
  margin: 0;
  text-transform: uppercase;
  letter-spacing: 1px;
}

/* Main Features */
.features-section {
  margin-bottom: 80px;
}

.section-header {
  text-align: center;
  margin-bottom: 50px;
}

.section-header h2 {
  font-size: 2.5rem;
  font-weight: 800;
  color: var(--text-primary);
  margin: 0 0 12px 0;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.section-header p {
  font-size: 1.2rem;
  color: var(--text-secondary);
  margin: 0;
  max-width: 600px;
  margin: 0 auto;
  font-weight: 500;
}

.features-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
  gap: 30px;
  max-width: 1400px;
  margin: 0 auto;
}

.feature-card {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(249, 243, 237, 0.9));
  backdrop-filter: blur(20px);
  border-radius: 20px;
  padding: 40px 32px;
  box-shadow: 0 12px 40px rgba(107, 79, 63, 0.08);
  border: 1px solid rgba(107, 79, 63, 0.06);
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  min-height: 220px;
}

.feature-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(107, 79, 63, 0.04), transparent);
  transition: left 0.6s ease;
}

.feature-card:hover::before {
  left: 100%;
}

.feature-card:hover {
  transform: translateY(-10px) scale(1.02);
  box-shadow: 0 25px 70px rgba(107, 79, 63, 0.12);
}

.feature-card.primary {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(217, 177, 130, 0.05));
}

.feature-card.secondary {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(166, 124, 82, 0.03));
}

.feature-card.admin {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(107, 79, 63, 0.04));
}

.feature-icon {
  font-size: 3.5rem;
  margin-bottom: 24px;
  filter: drop-shadow(0 3px 8px rgba(107, 79, 63, 0.15));
  transition: transform 0.3s ease;
}

.feature-card:hover .feature-icon {
  transform: scale(1.1) rotate(5deg);
}

.feature-content {
  flex: 1;
  margin-bottom: 24px;
}

.feature-content h3 {
  font-size: 1.6rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0 0 12px 0;
  line-height: 1.3;
}

.feature-content p {
  font-size: 1rem;
  color: var(--text-secondary);
  margin: 0;
  line-height: 1.6;
}

.feature-arrow {
  font-size: 1.5rem;
  color: var(--primary);
  font-weight: bold;
  transition: transform 0.3s ease;
}

.feature-card:hover .feature-arrow {
  transform: translateX(8px);
}

/* Responsive Design */

/* Desktop Large */
@media (min-width: 1400px) {
  .dashboard-content {
    padding: 40px 40px;
  }

  .hero-content {
    padding: 80px 60px;
  }

  .hero-title {
    font-size: 3.8rem;
  }

  .hero-name {
    font-size: 3.4rem;
  }

  .hero-subtitle {
    font-size: 1.4rem;
  }

  .family-icon {
    font-size: 10rem;
  }

  .features-grid {
    grid-template-columns: repeat(3, 1fr);
    gap: 40px;
  }

  .feature-card {
    padding: 50px 40px;
    min-height: 260px;
  }

  .section-header h2 {
    font-size: 3rem;
  }

  .stats-container {
    grid-template-columns: repeat(3, 1fr);
    gap: 40px;
  }
}

/* Desktop */
@media (min-width: 1024px) and (max-width: 1399px) {
  .dashboard-content {
    padding: 40px 30px;
  }

  .hero-content {
    padding: 60px 40px;
    flex-direction: column;
    text-align: center;
    gap: 40px;
  }

  .hero-title {
    font-size: 3rem;
  }

  .hero-name {
    font-size: 2.6rem;
  }

  .family-icon {
    font-size: 7rem;
  }

  .features-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 35px;
  }

  .feature-card {
    padding: 40px 32px;
  }

  .stats-container {
    grid-template-columns: repeat(3, 1fr);
    gap: 30px;
  }

  .section-header h2 {
    font-size: 2.8rem;
  }
}

/* Tablet */
@media (min-width: 768px) and (max-width: 1023px) {
  .dashboard-content {
    padding: 30px 20px;
  }

  .hero-content {
    padding: 50px 30px;
    flex-direction: column;
    text-align: center;
    gap: 30px;
  }

  .hero-title {
    font-size: 2.6rem;
  }

  .hero-name {
    font-size: 2.2rem;
  }

  .hero-subtitle {
    font-size: 1.2rem;
  }

  .family-icon {
    font-size: 6rem;
  }

  .stats-section {
    margin-bottom: 60px;
  }

  .stats-container {
    grid-template-columns: repeat(2, 1fr);
    gap: 25px;
  }

  .stat-card {
    padding: 28px;
  }

  .stat-content h3 {
    font-size: 2.4rem;
  }

  .features-section {
    margin-bottom: 60px;
  }

  .section-header {
    margin-bottom: 40px;
  }

  .section-header h2 {
    font-size: 2.4rem;
  }

  .features-grid {
    grid-template-columns: repeat(2, 1fr);
    gap: 25px;
  }

  .feature-card {
    padding: 32px 24px;
    min-height: 200px;
  }

  .feature-content h3 {
    font-size: 1.4rem;
  }
}

/* Mobile Large */
@media (min-width: 480px) and (max-width: 767px) {
  .dashboard-content {
    padding: 20px 15px;
  }

  .hero-section {
    margin-bottom: 40px;
  }

  .hero-content {
    padding: 40px 25px;
    flex-direction: column;
    text-align: center;
    gap: 25px;
  }

  .hero-title {
    font-size: 2.2rem;
  }

  .hero-greeting {
    font-size: 1.4rem;
  }

  .hero-name {
    font-size: 2rem;
  }

  .hero-subtitle {
    font-size: 1.1rem;
  }

  .family-icon {
    font-size: 5rem;
  }

  .stats-section {
    margin-bottom: 50px;
  }

  .stats-container {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .stat-card {
    padding: 24px;
    flex-direction: column;
    text-align: center;
    gap: 16px;
  }

  .stat-icon {
    font-size: 3rem;
  }

  .stat-content h3 {
    font-size: 2.2rem;
  }

  .stat-label {
    font-size: 0.95rem;
  }

  .features-section {
    margin-bottom: 50px;
  }

  .section-header {
    margin-bottom: 35px;
  }

  .section-header h2 {
    font-size: 2rem;
  }

  .section-header p {
    font-size: 1rem;
  }

  .features-grid {
    grid-template-columns: 1fr;
    gap: 20px;
  }

  .feature-card {
    padding: 28px 24px;
    min-height: 180px;
  }

  .feature-icon {
    font-size: 3rem;
    margin-bottom: 20px;
  }

  .feature-content h3 {
    font-size: 1.3rem;
  }

  .feature-content p {
    font-size: 0.95rem;
  }
}

/* Mobile Small */
@media (max-width: 479px) {
  .dashboard-page {
    padding-top: 60px;
  }

  .dashboard-content {
    padding: 15px 10px;
  }

  .hero-section {
    margin-bottom: 30px;
  }

  .hero-content {
    padding: 30px 20px;
    flex-direction: column;
    text-align: center;
    gap: 20px;
  }

  .hero-title {
    font-size: 1.8rem;
  }

  .hero-greeting {
    font-size: 1.2rem;
  }

  .hero-name {
    font-size: 1.6rem;
  }

  .hero-subtitle {
    font-size: 1rem;
  }

  .family-icon {
    font-size: 4rem;
  }

  .stats-section {
    margin-bottom: 40px;
  }

  .stats-container {
    grid-template-columns: 1fr;
    gap: 15px;
  }

  .stat-card {
    padding: 20px;
    flex-direction: column;
    text-align: center;
    gap: 12px;
  }

  .stat-icon {
    font-size: 2.5rem;
  }

  .stat-content h3 {
    font-size: 1.8rem;
  }

  .stat-label {
    font-size: 0.9rem;
  }

  .features-section {
    margin-bottom: 40px;
  }

  .section-header {
    margin-bottom: 25px;
  }

  .section-header h2 {
    font-size: 1.6rem;
  }

  .section-header p {
    font-size: 0.95rem;
  }

  .features-grid {
    grid-template-columns: 1fr;
    gap: 15px;
  }

  .feature-card {
    padding: 24px 20px;
    min-height: 160px;
  }

  .feature-icon {
    font-size: 2.5rem;
    margin-bottom: 16px;
  }

  .feature-content h3 {
    font-size: 1.2rem;
  }

  .feature-content p {
    font-size: 0.9rem;
  }

  .feature-arrow {
    font-size: 1.3rem;
  }
}

/* Add Member Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(5px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
  animation: modalFadeIn 0.3s ease;
}

.modal-content {
  background: white;
  border-radius: 16px;
  padding: 32px;
  max-width: 700px;
  width: 90%;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
  animation: modalSlideIn 0.3s ease;
}

.modal-content h3 {
  color: var(--text-primary);
  font-size: 1.8rem;
  font-weight: 600;
  margin-bottom: 24px;
  text-align: center;
}

.add-member-form {
  display: grid;
  gap: 32px;
}

.form-section {
  border-bottom: 1px solid rgba(107, 79, 63, 0.1);
  padding-bottom: 24px;
}

.form-section:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.section-title {
  color: var(--text-primary);
  font-size: 1.2rem;
  font-weight: 600;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.form-row {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 16px;
  margin-bottom: 16px;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.form-group label {
  color: var(--text-secondary);
  font-size: 0.9rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.form-input, .form-select, .form-textarea {
  padding: 12px 16px;
  border: 2px solid rgba(107, 79, 63, 0.2);
  border-radius: 8px;
  background: var(--bg-main);
  color: var(--text-primary);
  font-size: 1rem;
  transition: all 0.3s ease;
}

.form-input:focus, .form-select:focus, .form-textarea:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(107, 79, 63, 0.1);
}

.form-textarea {
  resize: vertical;
  min-height: 80px;
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

@keyframes modalFadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

@keyframes modalSlideIn {
  from {
    opacity: 0;
    transform: translateY(-20px) scale(0.95);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}
</style>
