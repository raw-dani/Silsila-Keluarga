<template>
  <div class="profile-page">
    <Navigation />

    <div class="profile-content">
      <div v-if="!isAdminProfile" class="profile-header">
        <h1>Profil Anggota Keluarga</h1>
        <p>Lihat detail informasi anggota keluarga</p>
      </div>

      <!-- Dynamic Profile Content -->
      <div v-if="isAdminProfile" class="profile-container">
        <div class="profile-header">
          <h1>Profil Administrator</h1>
          <p>Informasi akun dan pengaturan administrator</p>
        </div>

        <!-- Admin Profile Header Card -->
        <div class="profile-card main-profile">
          <div class="profile-avatar">
            <div class="avatar-placeholder">
              <span>👑</span>
            </div>
          </div>

          <div class="profile-info">
            <h2 class="member-name">{{ userInfo?.name || 'Administrator' }}</h2>
            <div class="member-badges">
              <span class="admin-badge">Administrator</span>
            </div>
          </div>
        </div>

        <!-- Admin Account Information Card -->
        <div class="profile-card">
          <h3 class="card-title">Informasi Akun</h3>
          <div class="info-grid">
            <div class="info-item">
              <label>Nama Lengkap</label>
              <span>{{ userInfo?.name || 'Memuat...' }}</span>
            </div>
            <div class="info-item">
              <label>Email</label>
              <span>{{ userInfo?.email || 'Memuat...' }}</span>
            </div>
            <div class="info-item">
              <label>Role</label>
              <span>Administrator</span>
            </div>
            <div class="info-item">
              <label>Tanggal Registrasi</label>
              <span>{{ userInfo?.created_at ? formatDate(userInfo.created_at) : 'Memuat...' }}</span>
            </div>
          </div>
        </div>

        <!-- Admin Actions Card -->
        <div class="profile-card">
          <h3 class="card-title">Aksi Administrator</h3>
          <div class="admin-actions-grid">
            <button @click="$router.push('/admin')" class="admin-action-btn">
              <span class="btn-icon">⚙️</span>
              <span>Panel Admin</span>
            </button>
            <button @click="openPasswordResetModal" class="admin-action-btn">
              <span class="btn-icon">🔑</span>
              <span>Reset Password</span>
            </button>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="profile-actions">
          <button @click="$router.go(-1)" class="back-btn">
            <span class="btn-icon">⬅️</span>
            <span>Kembali</span>
          </button>
        </div>

      </div>

      <!-- Family Member Profile -->
      <div v-else-if="member" class="profile-container">
        <!-- Profile Header Card -->
        <div class="profile-card main-profile">
          <div class="profile-avatar">
            <img v-if="member.photo" :src="getPhotoUrl(member.photo)" :alt="'Foto ' + member.name" class="avatar-image">
            <div v-if="!member.photo" class="avatar-placeholder">
              <span v-if="member.avatar">{{ getAvatarEmoji(member.avatar) }}</span>
              <span v-else>{{ member.gender === 'male' ? '👨' : member.gender === 'female' ? '👩' : '👤' }}</span>
            </div>
          </div>

          <div class="profile-info">
            <h2 class="member-name">{{ member.name }}</h2>
            <div class="member-badges">
              <span class="generation-badge">{{ getGenerationSymbol(member.generation_level) }}</span>
              <span class="gender-badge" :class="member.gender">
                {{ member.gender === 'male' ? '♂ Laki-laki' : member.gender === 'female' ? '♀ Perempuan' : '⚲ Tidak Diketahui' }}
              </span>
            </div>
          </div>
        </div>

        <!-- Personal Information Card -->
        <div class="profile-card">
          <h3 class="card-title">Informasi Pribadi</h3>
          <div class="info-grid">
            <div class="info-item">
              <label>Tanggal Lahir</label>
              <span>{{ formatDate(member.birth_date) || 'Tidak diketahui' }}</span>
            </div>
            <div v-if="member.death_date" class="info-item">
              <label>Tanggal Meninggal</label>
              <span>{{ formatDate(member.death_date) }}</span>
            </div>
            <div class="info-item">
              <label>Generasi</label>
              <span>{{ member.generation_level }}</span>
            </div>
            <div v-if="member.email" class="info-item">
              <label>Email</label>
              <span>{{ member.email }}</span>
            </div>
            <div v-if="member.phone" class="info-item">
              <label>Telepon</label>
              <span>{{ member.phone }}</span>
            </div>
          </div>
          <div v-if="member.notes" class="notes-section">
            <label>Catatan</label>
            <p class="notes-content">{{ member.notes }}</p>
          </div>
        </div>

        <!-- Family Relations Card -->
        <div class="profile-card">
          <h3 class="card-title">Relasi Keluarga</h3>
          <div class="relations-grid">
            <div v-if="member.father" class="relation-item">
              <div class="relation-icon">👨</div>
              <div class="relation-info">
                <label>Ayah</label>
                <span>{{ member.father.name }}</span>
              </div>
            </div>

            <div v-if="member.mother" class="relation-item">
              <div class="relation-icon">👩</div>
              <div class="relation-info">
                <label>Ibu</label>
                <span>{{ member.mother.name }}</span>
              </div>
            </div>

            <div v-if="member.spouse" class="relation-item">
              <div class="relation-icon">{{ member.spouse.gender === 'male' ? '👨' : '👩' }}</div>
              <div class="relation-info">
                <label>Pasangan</label>
                <span>{{ member.spouse.name }}</span>
              </div>
            </div>
          </div>

          <div v-if="member.children && member.children.length > 0" class="children-section">
            <label>Anak ({{ member.children.length }})</label>
            <div class="children-list">
              <div v-for="child in member.children" :key="child.id" class="child-item">
                <router-link :to="'/profile/' + child.id" class="child-link">
                  <span class="child-icon">{{ child.gender === 'male' ? '👦' : '👧' }}</span>
                  <span class="child-name">{{ child.name }}</span>
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="profile-actions">
          <button @click="$router.go(-1)" class="back-btn">
            <span class="btn-icon">⬅️</span>
            <span>Kembali</span>
          </button>
          <button v-if="canEdit && (!route.params.id || route.path === '/profile' || (member && userInfo && member.email === userInfo.email)) && member?.email" @click="openPasswordResetModal" class="reset-password-btn">
            <span class="btn-icon">🔑</span>
            <span>Reset Password</span>
          </button>
          <button v-if="canEdit && isOwnProfile" @click="openEditModal" class="edit-profile-btn">
            <span class="btn-icon">✏️</span>
            <span>Edit Profil</span>
          </button>
          <button v-if="canEdit && !isOwnProfile" @click="$router.push('/request-update')" class="edit-btn">
            <span class="btn-icon">📝</span>
            <span>Ajukan Perubahan</span>
          </button>
        </div>

        <!-- Edit Profile Modal -->
        <div v-if="showEditModal" class="modal-overlay" @click="closeEditModal">
          <div class="modal-content" @click.stop>
            <h3>Edit Profil</h3>
            <form @submit.prevent="updateProfile" class="edit-form">
              <div class="form-section">
                <h4 class="section-title">👤 Informasi Dasar</h4>
                <div class="form-row">
                  <div class="form-group">
                    <label for="editName">Nama Lengkap</label>
                    <input
                      id="editName"
                      v-model="editForm.name"
                      type="text"
                      required
                      class="form-input"
                    >
                  </div>
                  <div class="form-group">
                    <label for="editEmail">Email</label>
                    <input
                      id="editEmail"
                      v-model="editForm.email"
                      type="email"
                      class="form-input"
                    >
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label for="editPhone">Nomor Telepon</label>
                    <input
                      id="editPhone"
                      v-model="editForm.phone"
                      type="tel"
                      class="form-input"
                      placeholder="+628xxxxxxxxx"
                    >
                  </div>
                  <div class="form-group">
                    <label for="editBirthDate">Tanggal Lahir</label>
                    <input
                      id="editBirthDate"
                      v-model="editForm.birth_date"
                      type="date"
                      class="form-input"
                    >
                  </div>
                </div>

                <div class="form-row">
                  <div class="form-group">
                    <label for="editDeathDate">Tanggal Meninggal (Opsional)</label>
                    <input
                      id="editDeathDate"
                      v-model="editForm.death_date"
                      type="date"
                      class="form-input"
                    >
                  </div>
                  <div class="form-group">
                    <label for="editGender">Jenis Kelamin</label>
                    <select id="editGender" v-model="editForm.gender" required class="form-select">
                      <option value="male">Laki-laki</option>
                      <option value="female">Perempuan</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="form-section">
                <h4 class="section-title">📝 Catatan</h4>
                <div class="form-group">
                  <label for="editNotes">Catatan Tambahan</label>
                  <textarea
                    id="editNotes"
                    v-model="editForm.notes"
                    class="form-textarea"
                    rows="4"
                    placeholder="Tambahkan catatan tentang diri Anda..."
                  ></textarea>
                </div>
              </div>

              <div class="form-section">
                <h4 class="section-title">📎 Foto Profil</h4>
                <div class="photo-upload-section">
                  <div class="current-photo">
                    <img v-if="editForm.photoPreview" :src="editForm.photoPreview" :alt="'Foto ' + editForm.name" class="photo-preview">
                    <div v-else class="photo-placeholder">
                      {{ editForm.gender === 'male' ? '👨' : editForm.gender === 'female' ? '👩' : '👤' }}
                    </div>
                  </div>
                  <div class="photo-controls">
                    <input
                      type="file"
                      id="photoInput"
                      @change="handlePhotoChange"
                      accept="image/*"
                      class="photo-input"
                    >
                    <label for="photoInput" class="photo-upload-btn">
                      <span class="btn-icon">📷</span>
                      <span>Ganti Foto</span>
                    </label>
                    <button type="button" @click="removePhoto" class="photo-remove-btn" v-if="editForm.photo">
                      <span class="btn-icon">🗑️</span>
                      <span>Hapus Foto</span>
                    </button>
                  </div>
                </div>
              </div>

              <div class="form-actions">
                <button type="button" @click="closeEditModal" class="cancel-btn">
                  <span class="btn-icon">❌</span>
                  <span>Batal</span>
                </button>
                <button type="submit" :disabled="updating" class="save-btn">
                  <span class="btn-icon">{{ updating ? '⏳' : '💾' }}</span>
                  <span>{{ updating ? 'Menyimpan...' : 'Simpan Perubahan' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div v-else class="loading-state">
        <div class="loading-spinner"></div>
        <p>Memuat profil...</p>
      </div>

      <!-- Password Reset Modal (available for all authenticated users) -->
      <div v-if="showPasswordResetModal" class="modal-overlay" @click="closePasswordResetModal">
        <div class="modal-content" @click.stop>
          <h3>Reset Password</h3>
          <form @submit.prevent="resetPassword" class="reset-form">
            <div class="form-section">
              <h4 class="section-title">🔐 Ubah Password</h4>
              <div class="form-group">
                <label for="currentPassword">Password Lama</label>
                <input
                  id="currentPassword"
                  v-model="passwordForm.current_password"
                  type="password"
                  required
                  class="form-input"
                  placeholder="Masukkan password lama"
                >
              </div>
              <div class="form-group">
                <label for="newPassword">Password Baru</label>
                <input
                  id="newPassword"
                  v-model="passwordForm.password"
                  type="password"
                  required
                  class="form-input"
                  placeholder="Masukkan password baru"
                  minlength="8"
                >
              </div>
              <div class="form-group">
                <label for="confirmPassword">Konfirmasi Password Baru</label>
                <input
                  id="confirmPassword"
                  v-model="passwordForm.password_confirmation"
                  type="password"
                  required
                  class="form-input"
                  placeholder="Konfirmasi password baru"
                  minlength="8"
                >
              </div>
            </div>

            <div class="form-actions">
              <button type="button" @click="closePasswordResetModal" class="cancel-btn">
                <span class="btn-icon">❌</span>
                <span>Batal</span>
              </button>
              <button type="submit" :disabled="resettingPassword" class="save-btn">
                <span class="btn-icon">{{ resettingPassword ? '⏳' : '🔑' }}</span>
                <span>{{ resettingPassword ? 'Mengubah...' : 'Ubah Password' }}</span>
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
import { useRoute } from 'vue-router'
import Navigation from '../components/Navigation.vue'
import api from '../services/api'

const route = useRoute()
const member = ref(null)
const userInfo = ref(null)
const isAdminProfile = ref(false)
const canEdit = ref(false)
const showEditModal = ref(false)
const showPasswordResetModal = ref(false)
const updating = ref(false)
const resettingPassword = ref(false)
const editForm = ref({
  name: '',
  email: '',
  phone: '',
  gender: 'male',
  birth_date: '',
  death_date: '',
  notes: '',
  photo: null,
  photoPreview: null
})
const passwordForm = ref({
  current_password: '',
  password: '',
  password_confirmation: ''
})

const profileImage = computed(() => {
  if (!member.value) return '/default-avatar.png'

  // Priority: photo > avatar > default avatar
  if (member.value.photo) {
    return member.value.photo // Assuming it's a URL or path
  }

  if (member.value.avatar) {
    // If avatar is an ID, construct the avatar URL
    if (member.value.avatar.startsWith('http')) {
      return member.value.avatar
    } else {
      // It's an avatar ID, construct DiceBear URL
      return `https://api.dicebear.com/7.x/avataaars/svg?seed=${member.value.avatar}`
    }
  }

  return '/default-avatar.png'
})

const getPhotoUrl = (photoPath) => {
  if (!photoPath) return '/default-avatar.png'

  // If it's already a full URL, return as is
  if (photoPath.startsWith('http')) {
    return photoPath
  }

  // If it's a relative path (stored in storage), construct the full URL
  // Backend serves storage files on port 8000, frontend is on port 5173
  const backendUrl = window.location.origin.replace('5173', '8000')
  return `${backendUrl}/storage/${photoPath}`
}

const getAvatarEmoji = (avatarValue) => {
  if (!avatarValue) return member.value?.gender === 'male' ? '👨' : member.value?.gender === 'female' ? '👩' : '👤'

  // If avatar is a number or specific value, map to emoji
  // You can customize this mapping based on your avatar system
  const avatarMap = {
    '1': '👨',
    '2': '👩',
    '3': '🧑',
    '4': '👴',
    '5': '👵',
    'male': '👨',
    'female': '👩',
    'default': '👤'
  }

  return avatarMap[avatarValue] || (member.value?.gender === 'male' ? '👨' : member.value?.gender === 'female' ? '👩' : '👤')
}

const formatDate = (dateString) => {
  if (!dateString) return null
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const getGenerationSymbol = (num) => {
  if (num < 1 || num > 3999) return num.toString()

  const romanNumerals = [
    { value: 1000, symbol: 'M' },
    { value: 900, symbol: 'CM' },
    { value: 500, symbol: 'D' },
    { value: 400, symbol: 'CD' },
    { value: 100, symbol: 'C' },
    { value: 90, symbol: 'XC' },
    { value: 50, symbol: 'L' },
    { value: 40, symbol: 'XL' },
    { value: 10, symbol: 'X' },
    { value: 9, symbol: 'IX' },
    { value: 5, symbol: 'V' },
    { value: 4, symbol: 'IV' },
    { value: 1, symbol: 'I' }
  ]

  let result = ''
  for (const { value, symbol } of romanNumerals) {
    while (num >= value) {
      result += symbol
      num -= value
    }
  }

  return result
}

onMounted(async () => {
  const memberId = route.params.id
  const userRole = localStorage.getItem('role')

  // If viewing own profile (no ID or accessing /profile)
  if (!memberId || route.path === '/profile') {
    try {
      // Get current user info
      const userResponse = await api.get('/user')
      userInfo.value = userResponse.data

      // For admins, show user profile instead of family member
      if (userRole === 'admin') {
        member.value = null // Don't show family member data
        isAdminProfile.value = true
      } else {
        // For regular users, find family member with this email
        const userEmail = userResponse.data.email

        if (userEmail) {
          const membersResponse = await api.get('/family-members')
          const userMember = membersResponse.data.find(member => member.email === userEmail)

          if (userMember) {
            member.value = userMember
            isAdminProfile.value = false
          } else {
            // User exists but no corresponding family member
            console.warn('User logged in but no corresponding family member found')
            member.value = null
            isAdminProfile.value = false
          }
        }
      }
    } catch (error) {
      console.error('Error loading user profile:', error)
      member.value = null
      // Don't reset isAdminProfile if we already detected admin
      if (userRole !== 'admin') {
        isAdminProfile.value = false
      }
    }
  } else {
    // Viewing specific member's profile by ID
    try {
      const response = await api.get(`/family-members/${memberId}`)
      member.value = response.data
      isAdminProfile.value = false
    } catch (error) {
      console.error('Error loading member profile:', error)
      member.value = null
      isAdminProfile.value = false
    }
  }

  // Determine edit permissions
  canEdit.value = userRole === 'member' || userRole === 'admin'
})

// Admin profile functions
const openPasswordResetModal = () => {
  passwordForm.value = {
    current_password: '',
    password: '',
    password_confirmation: ''
  }
  showPasswordResetModal.value = true
}

const closePasswordResetModal = () => {
  showPasswordResetModal.value = false
  passwordForm.value = {
    current_password: '',
    password: '',
    password_confirmation: ''
  }
}

const resetPassword = async () => {
  if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
    alert('Password baru dan konfirmasi password tidak cocok!')
    return
  }

  resettingPassword.value = true

  try {
    await api.post('/user/change-password', {
      current_password: passwordForm.value.current_password,
      password: passwordForm.value.password,
      password_confirmation: passwordForm.value.password_confirmation
    })

    alert('Password berhasil diubah!')
    closePasswordResetModal()

  } catch (error) {
    console.error('Error resetting password:', error)
    alert('Gagal mengubah password: ' + (error.response?.data?.message || error.message))
  } finally {
    resettingPassword.value = false
  }
}

// Computed property to check if viewing own profile
const isOwnProfile = computed(() => {
  // If no route ID (own profile), it's always own profile
  if (!route.params.id || route.path === '/profile') return true

  // For specific member profiles, check if user email matches member email
  if (member.value && userInfo.value) {
    return userInfo.value.email === member.value.email
  }

  return false
})

// Edit modal functions
const openEditModal = () => {
  if (!member.value) return

  // Populate form with current data
  editForm.value = {
    name: member.value.name || '',
    email: member.value.email || '',
    phone: member.value.phone || '',
    gender: member.value.gender || 'male',
    birth_date: member.value.birth_date || '',
    death_date: member.value.death_date || '',
    notes: member.value.notes || '',
    photo: null,
    photoPreview: member.value.photo || null
  }

  showEditModal.value = true
}

const closeEditModal = () => {
  showEditModal.value = false
  editForm.value = {
    name: '',
    email: '',
    phone: '',
    gender: 'male',
    birth_date: '',
    death_date: '',
    notes: '',
    photo: null,
    photoPreview: null
  }
}

const handlePhotoChange = (event) => {
  const file = event.target.files[0]
  if (file) {
    editForm.value.photo = file

    // Create preview
    const reader = new FileReader()
    reader.onload = (e) => {
      editForm.value.photoPreview = e.target.result
    }
    reader.readAsDataURL(file)
  }
}

const removePhoto = () => {
  editForm.value.photo = null
  editForm.value.photoPreview = null
}

const updateProfile = async () => {
  updating.value = true

  try {
    const formData = new FormData()

    // Add form data
    Object.keys(editForm.value).forEach(key => {
      if (key !== 'photoPreview' && editForm.value[key] !== null && editForm.value[key] !== '') {
        formData.append(key, editForm.value[key])
      }
    })

    // Update member data using self-update endpoint
    const response = await api.post('/family-members/self?_method=PUT', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    // Update local member data
    member.value = response.data

    closeEditModal()

    // Show success message
    alert('Profil berhasil diperbarui!')

  } catch (error) {
    console.error('Error updating profile:', error)
    alert('Gagal memperbarui profil: ' + (error.response?.data?.message || error.message))
  } finally {
    updating.value = false
  }
}
</script>

<style scoped>
.profile-page {
  min-height: 100vh;
  background: var(--bg-main);
}

.profile-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 30px 20px;
}

.profile-header {
  text-align: center;
  margin-bottom: 40px;
}

.profile-header h1 {
  color: var(--text-primary);
  font-size: 2.5rem;
  margin-bottom: 10px;
  font-weight: 700;
}

.profile-header p {
  color: var(--text-secondary);
  font-size: 1.1rem;
}

.profile-container {
  display: grid;
  gap: 24px;
  max-width: 800px;
  margin: 0 auto;
}

.profile-card {
  background: var(--bg-card);
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.1);
  border: 1px solid rgba(107, 79, 63, 0.1);
  transition: all 0.3s ease;
}

.profile-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(107, 79, 63, 0.15);
}

.profile-card.main-profile {
  display: flex;
  align-items: center;
  gap: 24px;
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(217, 177, 130, 0.05));
}

.profile-avatar {
  position: relative;
  flex-shrink: 0;
}

.avatar-image {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid rgba(107, 79, 63, 0.2);
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.2);
}

.avatar-placeholder {
  width: 120px;
  height: 120px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 48px;
  color: white;
  border: 4px solid rgba(107, 79, 63, 0.2);
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.2);
}

.profile-info {
  flex: 1;
  min-width: 0;
}

.member-name {
  color: var(--text-primary);
  font-size: 2rem;
  font-weight: 600;
  margin-bottom: 12px;
  word-break: break-word;
}

.member-badges {
  display: flex;
  gap: 12px;
  flex-wrap: wrap;
}

.generation-badge, .gender-badge {
  padding: 6px 12px;
  border-radius: 20px;
  font-size: 0.85rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.generation-badge {
  background: rgba(107, 79, 63, 0.1);
  color: var(--primary);
}

.gender-badge.male {
  background: linear-gradient(135deg, var(--secondary), var(--secondary));
  color: white;
}

.gender-badge.female {
  background: linear-gradient(135deg, var(--accent), var(--accent));
  color: white;
}

.card-title {
  color: var(--text-primary);
  font-size: 1.5rem;
  font-weight: 600;
  margin-bottom: 20px;
  padding-bottom: 12px;
  border-bottom: 2px solid rgba(107, 79, 63, 0.1);
}

.info-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 20px;
  margin-bottom: 24px;
}

.info-item {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.info-item label {
  color: var(--text-secondary);
  font-size: 0.9rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.info-item span {
  color: var(--text-primary);
  font-size: 1rem;
  font-weight: 500;
}

.notes-section {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid rgba(107, 79, 63, 0.1);
}

.notes-section label {
  color: var(--text-secondary);
  font-size: 0.9rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 8px;
  display: block;
}

.notes-content {
  color: var(--text-primary);
  font-size: 1rem;
  line-height: 1.6;
  margin: 0;
  padding: 12px 16px;
  background: rgba(107, 79, 63, 0.02);
  border-radius: 8px;
  border-left: 3px solid var(--primary);
}

.relations-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.relation-item {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 16px;
  background: rgba(107, 79, 63, 0.02);
  border-radius: 12px;
  border: 1px solid rgba(107, 79, 63, 0.05);
}

.relation-icon {
  font-size: 2rem;
  flex-shrink: 0;
}

.relation-info {
  flex: 1;
  min-width: 0;
}

.relation-info label {
  color: var(--text-secondary);
  font-size: 0.8rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 4px;
  display: block;
}

.relation-info span {
  color: var(--text-primary);
  font-size: 1rem;
  font-weight: 500;
  word-break: break-word;
}

.children-section {
  margin-top: 20px;
  padding-top: 20px;
  border-top: 1px solid rgba(107, 79, 63, 0.1);
}

.children-section label {
  color: var(--text-secondary);
  font-size: 0.9rem;
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  margin-bottom: 12px;
  display: block;
}

.children-list {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 12px;
}

.child-item {
  background: rgba(107, 79, 63, 0.02);
  border-radius: 8px;
  overflow: hidden;
  transition: all 0.3s ease;
}

.child-item:hover {
  background: rgba(107, 79, 63, 0.05);
  transform: translateY(-1px);
}

.child-link {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 12px 16px;
  text-decoration: none;
  color: var(--text-primary);
  transition: color 0.3s ease;
}

.child-link:hover {
  color: var(--primary);
}

.child-icon {
  font-size: 1.5rem;
  flex-shrink: 0;
}

.child-name {
  font-weight: 500;
  word-break: break-word;
}

.profile-actions {
  display: flex;
  gap: 16px;
  justify-content: center;
  flex-wrap: wrap;
}

.back-btn, .edit-btn, .edit-profile-btn, .reset-password-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 14px 24px;
  border-radius: 25px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  text-decoration: none;
}

.back-btn {
  background: rgba(107, 79, 63, 0.1);
  color: var(--text-secondary);
  border: 2px solid rgba(107, 79, 63, 0.2);
}

.back-btn:hover {
  background: rgba(107, 79, 63, 0.2);
  color: var(--text-primary);
  transform: translateY(-2px);
}

.edit-btn {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.3);
}

.edit-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(107, 79, 63, 0.4);
}

.btn-icon {
  font-size: 1.2rem;
}

.loading-state {
  text-align: center;
  padding: 80px 20px;
  color: var(--text-secondary);
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(107, 79, 63, 0.2);
  border-left: 4px solid var(--primary);
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 20px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Edit Profile Modal Styles */
.edit-profile-btn {
  background: linear-gradient(135deg, var(--secondary), var(--accent));
  color: white;
  box-shadow: 0 4px 15px rgba(166, 124, 82, 0.3);
}

.edit-profile-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(166, 124, 82, 0.4);
}

.reset-password-btn {
  background: linear-gradient(135deg, var(--accent), var(--primary));
  color: white;
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.3);
}

.reset-password-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(107, 79, 63, 0.4);
}

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
  background: var(--bg-card);
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

.edit-form {
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

.photo-upload-section {
  display: grid;
  grid-template-columns: auto 1fr;
  gap: 24px;
  align-items: start;
}

.current-photo {
  flex-shrink: 0;
}

.photo-preview, .photo-placeholder {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  border: 3px solid rgba(107, 79, 63, 0.2);
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.2);
}

.photo-preview {
  object-fit: cover;
}

.photo-placeholder {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 36px;
  color: white;
}

.photo-controls {
  display: flex;
  flex-direction: column;
  gap: 12px;
}

.photo-input {
  display: none;
}

.photo-upload-btn, .photo-remove-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 8px;
  padding: 12px 16px;
  border-radius: 8px;
  font-size: 0.9rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
}

.photo-upload-btn {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  box-shadow: 0 2px 8px rgba(107, 79, 63, 0.3);
}

.photo-upload-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(107, 79, 63, 0.4);
}

.photo-remove-btn {
  background: linear-gradient(135deg, var(--error), var(--error));
  color: white;
  box-shadow: 0 2px 8px rgba(229, 57, 53, 0.3);
}

.photo-remove-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(229, 57, 53, 0.4);
}

.form-actions {
  display: flex;
  gap: 16px;
  justify-content: center;
  flex-wrap: wrap;
  margin-top: 8px;
}

.cancel-btn, .save-btn {
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

.save-btn {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.3);
}

.save-btn:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(107, 79, 63, 0.4);
}

.save-btn:disabled {
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

/* Desktop styles */
@media (min-width: 1024px) {
  .profile-content {
    padding: 40px 30px;
  }

  .profile-header h1 {
    font-size: 3rem;
  }

  .profile-container {
    gap: 32px;
  }

  .profile-card {
    padding: 32px;
  }

  .profile-card.main-profile {
    gap: 32px;
  }

  .avatar-image, .avatar-placeholder {
    width: 140px;
    height: 140px;
    font-size: 56px;
  }

  .member-name {
    font-size: 2.5rem;
  }

  .info-grid {
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
  }

  .relations-grid {
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
  }

  .children-list {
    grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
    gap: 16px;
  }

  .profile-actions {
    gap: 20px;
  }

  .back-btn, .edit-btn {
    padding: 16px 28px;
    font-size: 1.1rem;
  }
}

/* Tablet styles */
@media (min-width: 768px) and (max-width: 1023px) {
  .profile-content {
    padding: 30px 20px;
  }

  .profile-card.main-profile {
    flex-direction: column;
    text-align: center;
    gap: 20px;
  }

  .member-name {
    font-size: 2.2rem;
  }

  .info-grid {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  }

  .relations-grid {
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  }
}

/* Admin Profile Styles */
.admin-badge {
  background: linear-gradient(135deg, var(--secondary), var(--primary));
  color: white;
  padding: 8px 16px;
  border-radius: 20px;
  font-size: 0.9rem;
  font-weight: 600;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.admin-actions-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}

.admin-action-btn {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 12px;
  padding: 16px 20px;
  border-radius: 12px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  border: none;
  text-decoration: none;
}

.admin-action-btn:first-child {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.3);
}

.admin-action-btn:first-child:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(107, 79, 63, 0.4);
}

.admin-action-btn:last-child {
  background: linear-gradient(135deg, var(--secondary), var(--accent));
  color: white;
  box-shadow: 0 4px 15px rgba(166, 124, 82, 0.3);
}

.admin-action-btn:last-child:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(166, 124, 82, 0.4);
}

.reset-form {
  display: grid;
  gap: 24px;
}

.reset-form .form-section {
  border-bottom: 1px solid rgba(107, 79, 63, 0.1);
  padding-bottom: 20px;
}

.reset-form .form-section:last-child {
  border-bottom: none;
  padding-bottom: 0;
}

.reset-form .section-title {
  color: var(--text-primary);
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

/* Mobile styles */
@media (max-width: 767px) {
  .profile-page {
    padding-top: 60px; /* Account for fixed nav */
  }

  .profile-content {
    padding: 20px 15px;
  }

  .profile-header h1 {
    font-size: 2rem;
  }

  .profile-container {
    gap: 20px;
  }

  .profile-card {
    padding: 20px;
  }

  .profile-card.main-profile {
    flex-direction: column;
    text-align: center;
    gap: 20px;
  }

  .avatar-image, .avatar-placeholder {
    width: 100px;
    height: 100px;
    font-size: 40px;
  }

  .member-name {
    font-size: 1.8rem;
  }

  .member-badges {
    justify-content: center;
  }

  .info-grid {
    grid-template-columns: 1fr;
    gap: 16px;
  }

  .relations-grid {
    grid-template-columns: 1fr;
    gap: 12px;
  }

  .relation-item {
    padding: 12px;
  }

  .children-list {
    grid-template-columns: 1fr;
    gap: 8px;
  }

  .child-item {
    background: rgba(107, 79, 63, 0.05);
  }

  .child-link {
    padding: 10px 12px;
  }

  .profile-actions {
    flex-direction: column;
    gap: 12px;
  }

  .back-btn, .edit-btn {
    justify-content: center;
    padding: 14px 20px;
    font-size: 1rem;
  }
}
</style>
