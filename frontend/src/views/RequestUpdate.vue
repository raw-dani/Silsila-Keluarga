<template>
  <div class="request-update-page">
    <Navigation />

    <div class="request-content">
      <div class="request-header">
        <h1>Ajukan Perubahan Data Keluarga</h1>
        <p>Kirim permintaan perubahan data keluarga kepada admin untuk disetujui</p>
      </div>

      <div class="request-form-container">
        <form @submit.prevent="submitRequest" class="request-form">
          <!-- Member Selection -->
          <div class="form-section">
            <h3 class="section-title">👤 Pilih Anggota Keluarga</h3>
            <div class="form-group">
              <label for="targetMember">Anggota yang ingin diubah</label>
              <div class="searchable-dropdown" :class="{ open: showMemberDropdown }">
                <input
                  type="text"
                  id="targetMember"
                  v-model="memberSearchQuery"
                  @focus="openMemberDropdown"
                  @input="filterMembers"
                  :placeholder="getSelectedMemberName() || 'Cari anggota keluarga...'"
                  class="dropdown-input"
                  :class="{ 'selected': targetMemberId }"
                  readonly
                >
                <!-- Hidden input for form validation -->
                <input
                  type="hidden"
                  name="targetMemberId"
                  v-model="targetMemberId"
                  required
                >
                <div v-if="showMemberDropdown" class="dropdown-list" ref="memberDropdown">
                  <div
                    v-for="member in filteredMembers.slice(0, 10)"
                    :key="member.id"
                    @click="selectMember(member.id)"
                    class="dropdown-item"
                    :class="{ selected: targetMemberId == member.id }"
                  >
                    {{ member.name }} <span class="generation-info">{{ getGenerationSymbol(member.generation_level) }}</span>
                  </div>
                  <div v-if="filteredMembers.length === 0" class="dropdown-item disabled">
                    Tidak ada hasil
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Change Type -->
          <div class="form-section">
            <h3 class="section-title">📝 Jenis Perubahan</h3>
            <div class="form-group">
              <label for="changeType">Kategori perubahan</label>
              <select id="changeType" v-model="changeType" required class="form-select">
                <option value="">Pilih jenis perubahan</option>
                <option value="biodata">📋 Biodata (nama, tanggal lahir, dll)</option>
                <option value="hubungan">👨‍👩‍👧 Hubungan Keluarga</option>
                <option value="foto">📸 Foto Profil</option>
              </select>
            </div>
          </div>

          <!-- Biodata Form -->
          <div v-if="changeType === 'biodata'" class="form-section">
            <h3 class="section-title">📋 Biodata</h3>
            <div class="form-grid">
              <div class="form-group">
                <label for="biodataName">Nama</label>
                <input
                  id="biodataName"
                  v-model="biodataForm.name"
                  type="text"
                  class="form-input"
                  placeholder="Masukkan nama lengkap"
                >
              </div>
              <div class="form-group">
                <label for="biodataEmail">Email</label>
                <input
                  id="biodataEmail"
                  v-model="biodataForm.email"
                  type="email"
                  class="form-input"
                  placeholder="contoh@email.com"
                >
              </div>
              <div class="form-group">
                <label for="biodataPhone">Nomor Telepon</label>
                <input
                  id="biodataPhone"
                  v-model="biodataForm.phone"
                  type="tel"
                  class="form-input"
                  placeholder="+628xxxxxxxxx"
                >
              </div>
              <div class="form-group">
                <label for="biodataGender">Jenis Kelamin</label>
                <select id="biodataGender" v-model="biodataForm.gender" class="form-select">
                  <option value="">Pilih jenis kelamin</option>
                  <option value="male">Laki-laki</option>
                  <option value="female">Perempuan</option>
                </select>
              </div>
              <div class="form-group">
                <label for="biodataBirth">Tanggal Lahir</label>
                <input
                  id="biodataBirth"
                  v-model="biodataForm.birth_date"
                  type="date"
                  class="form-input"
                >
              </div>
              <div class="form-group">
                <label for="biodataDeath">Tanggal Meninggal (Opsional)</label>
                <input
                  id="biodataDeath"
                  v-model="biodataForm.death_date"
                  type="date"
                  class="form-input"
                >
              </div>
            </div>
            <div class="current-data-preview" v-if="selectedMemberData">
              <h4>Data Saat Ini:</h4>
              <div class="data-preview-grid">
                <div><strong>Nama:</strong> {{ selectedMemberData.name }}</div>
                <div><strong>Email:</strong> {{ selectedMemberData.email || 'Tidak ada' }}</div>
                <div><strong>Telepon:</strong> {{ selectedMemberData.phone || 'Tidak ada' }}</div>
                <div><strong>Jenis Kelamin:</strong> {{ selectedMemberData.gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</div>
                <div><strong>Tanggal Lahir:</strong> {{ selectedMemberData.birth_date ? formatDate(selectedMemberData.birth_date) : 'Tidak ada' }}</div>
                <div><strong>Tanggal Meninggal:</strong> {{ selectedMemberData.death_date ? formatDate(selectedMemberData.death_date) : 'Tidak ada' }}</div>
              </div>
            </div>
          </div>

          <!-- Hubungan Form -->
          <div v-if="changeType === 'hubungan'" class="form-section">
            <h3 class="section-title">👨‍👩‍👧 Hubungan Keluarga</h3>
            <div class="form-group">
              <label for="spouseSelect">Pilih Pasangan</label>
              <div class="searchable-dropdown" :class="{ open: showSpouseDropdown }">
                <input
                  type="text"
                  id="spouseSelect"
                  v-model="spouseSearchQuery"
                  @focus="openSpouseDropdown"
                  @input="filterSpouseMembers"
                  :placeholder="getSelectedSpouseName() || 'Cari pasangan...'"
                  class="dropdown-input"
                >
                <div v-if="showSpouseDropdown" class="dropdown-list" ref="spouseDropdown">
                  <div
                    v-for="spouse in filteredSpouseMembers.slice(0, 10)"
                    :key="spouse.id"
                    @click="selectSpouse(spouse.id)"
                    class="dropdown-item"
                    :class="{ selected: selectedSpouseId == spouse.id }"
                  >
                    {{ spouse.name }} <span class="generation-info">{{ getGenerationSymbol(spouse.generation_level) }}</span>
                  </div>
                  <div v-if="filteredSpouseMembers.length === 0" class="dropdown-item disabled">
                    Tidak ada pasangan tersedia
                  </div>
                </div>
              </div>
              <small class="form-hint">Pilih pasangan dari daftar anggota keluarga yang tersedia</small>
            </div>
            <div class="current-data-preview" v-if="selectedMemberData">
              <h4>Hubungan Saat Ini:</h4>
              <div class="relationship-preview">
                <div><strong>Pasangan:</strong> {{ selectedMemberData.spouse ? selectedMemberData.spouse.name : 'Belum ada pasangan' }}</div>
              </div>
            </div>
          </div>

          <!-- Foto Form -->
          <div v-if="changeType === 'foto'" class="form-section">
            <h3 class="section-title">📸 Foto Profil</h3>
            <div class="form-group">
              <label for="profilePhoto">Upload Foto Baru</label>
              <div class="file-upload">
                <input
                  type="file"
                  id="profilePhoto"
                  @change="handleFileUpload"
                  accept="image/*"
                  class="file-input"
                >
                <div class="file-upload-area">
                  <div class="upload-icon">📸</div>
                  <div class="upload-text">
                    <span class="upload-primary">{{ photo ? photo.name : 'Klik untuk memilih foto' }}</span>
                    <span class="upload-secondary">Format: JPG, PNG (maksimal 5MB)</span>
                  </div>
                </div>
              </div>
              <small class="form-hint">Upload foto profil baru untuk mengganti foto yang ada</small>
            </div>
            <div class="current-photo-preview" v-if="selectedMemberData">
              <h4>Foto Saat Ini:</h4>
              <div class="photo-preview">
                <img
                  v-if="selectedMemberData.photo"
                  :src="getPhotoUrl(selectedMemberData.photo)"
                  :alt="selectedMemberData.name"
                  class="current-photo"
                >
                <div v-else class="no-photo">
                  <div class="avatar-placeholder">
                    {{ selectedMemberData.gender === 'male' ? '👨' : selectedMemberData.gender === 'female' ? '👩' : '👤' }}
                  </div>
                  <p>Belum ada foto</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Fallback Text Area (when no specific form is selected) -->
          <div v-if="!changeType" class="form-section">
            <h3 class="section-title">✏️ Detail Perubahan</h3>
            <div class="form-group">
              <label for="newData">Jelaskan perubahan yang diinginkan</label>
              <textarea
                id="newData"
                v-model="newData"
                placeholder="Pilih jenis perubahan terlebih dahulu"
                disabled
                class="form-textarea"
                rows="6"
              ></textarea>
              <small class="form-hint">Pilih jenis perubahan di atas untuk menampilkan form yang sesuai</small>
            </div>
          </div>

          <!-- Photo Upload -->
          <!-- <div class="form-section">
            <h3 class="section-title">📎 Upload Berkas (Opsional)</h3>
            <div class="form-group">
              <label for="photo">Upload foto baru atau dokumen pendukung</label>
              <div class="file-upload">
                <input
                  type="file"
                  id="photo"
                  @change="handleFileUpload"
                  accept="image/*,.pdf,.doc,.docx"
                  class="file-input"
                >
                <div class="file-upload-area">
                  <div class="upload-icon">📎</div>
                  <div class="upload-text">
                    <span class="upload-primary">{{ photo ? photo.name : 'Klik untuk memilih file' }}</span>
                    <span class="upload-secondary">atau drag & drop file di sini</span>
                  </div>
                </div>
              </div>
              <small class="form-hint">Format yang didukung: JPG, PNG, PDF, DOC, DOCX (maksimal 5MB)</small>
            </div>
          </div> -->

          <!-- Submit Button -->
          <div class="form-actions">
            <button type="button" @click="$router.go(-1)" class="cancel-btn">
              <span class="btn-icon">⬅️</span>
              <span>Batal</span>
            </button>
            <button type="submit" :disabled="loading" class="submit-btn">
              <span class="btn-icon">{{ loading ? '⏳' : '📤' }}</span>
              <span>{{ loading ? 'Mengirim...' : 'Kirim Pengajuan' }}</span>
            </button>
          </div>
        </form>

        <!-- Message Display -->
        <div v-if="message" class="message-container" :class="messageType">
          <div class="message-icon">{{ messageType === 'success' ? '✅' : '❌' }}</div>
          <div class="message-content">
            <h4>{{ messageType === 'success' ? 'Berhasil!' : 'Error' }}</h4>
            <p>{{ message }}</p>
          </div>
        </div>
      </div>

      <!-- Info Card -->
      <div class="info-card">
        <h3>ℹ️ Informasi Penting</h3>
        <ul>
          <li>Pengajuan akan diperiksa oleh admin sebelum disetujui</li>
          <li>Anda akan mendapat notifikasi setelah pengajuan diproses</li>
          <li>Pastikan data yang dimasukkan akurat dan lengkap</li>
          <li>Untuk perubahan yang mendesak, hubungi admin langsung</li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import Navigation from '../components/Navigation.vue'

const router = useRouter()
const targetMemberId = ref('')
const changeType = ref('')
const newData = ref('')
const photo = ref(null)
const loading = ref(false)
const message = ref('')
const messageType = ref('')
const familyMembers = ref([])
const selectedMember = ref(null)

// Searchable dropdown properties
const memberSearchQuery = ref('')
const showMemberDropdown = ref(false)
const memberDropdown = ref(null)

// Biodata form properties
const biodataForm = ref({
  name: '',
  email: '',
  phone: '',
  gender: '',
  birth_date: '',
  death_date: ''
})

// Hubungan form properties
const spouseSearchQuery = ref('')
const showSpouseDropdown = ref(false)
const spouseDropdown = ref(null)
const selectedSpouseId = ref('')

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
  try {
    const response = await api.get('/family-members')
    familyMembers.value = response.data
  } catch (error) {
    console.error('Error loading family members:', error)
    // Fallback to mock data
    familyMembers.value = [
      { id: 1, name: 'John Doe', generation_level: 3 },
      { id: 2, name: 'Jane Doe', generation_level: 3 }
    ]
  }
})

const handleFileUpload = (event) => {
  photo.value = event.target.files[0]
}

const submitRequest = async () => {
  loading.value = true
  message.value = ''
  try {
    const formData = new FormData()
    formData.append('target_member_id', targetMemberId.value)
    formData.append('change_type', changeType.value)

    // Prepare new_data based on change type
    let requestData = {}

    if (changeType.value === 'biodata') {
      // For biodata changes, send the new biodata values
      requestData = {
        type: 'biodata',
        data: biodataForm.value
      }
    } else if (changeType.value === 'hubungan') {
      // For relationship changes, send spouse information
      requestData = {
        type: 'hubungan',
        spouse_id: selectedSpouseId.value,
        spouse_name: getSelectedSpouseName()
      }
    } else if (changeType.value === 'foto') {
      // For photo changes, photo will be sent separately
      requestData = {
        type: 'foto',
        description: 'Update foto profil'
      }
    }

    formData.append('new_data', JSON.stringify(requestData))

    // Add photo if uploaded (for any change type that includes photo)
    if (photo.value) {
      console.log('🎯 DEBUG RequestUpdate: Adding photo to formData')
      console.log('Photo file:', photo.value)
      console.log('Photo name:', photo.value.name)
      console.log('Photo size:', photo.value.size)
      console.log('Photo type:', photo.value.type)
      formData.append('photo', photo.value)
    } else {
      console.log('🎯 DEBUG RequestUpdate: No photo to upload')
    }

    console.log('🎯 DEBUG RequestUpdate: FormData contents:')
    for (let [key, value] of formData.entries()) {
      console.log(`${key}:`, value)
    }

    await api.post('/update-requests', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    message.value = 'Pengajuan berhasil dikirim! Admin akan memproses permintaan Anda dalam 1-3 hari kerja.'
    messageType.value = 'success'

    // Reset form
    resetForm()

    // Auto-hide message after 5 seconds
    setTimeout(() => {
      message.value = ''
    }, 5000)
  } catch (error) {
    console.error('🎯 DEBUG RequestUpdate: Error submitting request:', error)
    console.error('Error response:', error.response)
    message.value = error.response?.data?.message || 'Gagal mengirim pengajuan. Silakan coba lagi.'
    messageType.value = 'error'
  } finally {
    loading.value = false
  }
}

// Computed property for filtered members
const filteredMembers = computed(() => {
  if (!memberSearchQuery.value.trim()) {
    return familyMembers.value
  }

  const query = memberSearchQuery.value.toLowerCase()
  return familyMembers.value.filter(member =>
    member.name.toLowerCase().includes(query)
  )
})

// Computed property for selected member data
const selectedMemberData = computed(() => {
  if (!targetMemberId.value) return null
  return familyMembers.value.find(member => member.id == targetMemberId.value)
})

// Computed property for filtered spouse members (opposite gender)
const filteredSpouseMembers = computed(() => {
  if (!spouseSearchQuery.value.trim()) {
    return availableSpouses.value
  }

  const query = spouseSearchQuery.value.toLowerCase()
  return availableSpouses.value.filter(member =>
    member.name.toLowerCase().includes(query)
  )
})

// Computed property for available spouses (opposite gender to selected member)
const availableSpouses = computed(() => {
  if (!selectedMemberData.value) return []

  const currentGender = selectedMemberData.value.gender
  const oppositeGender = currentGender === 'male' ? 'female' : 'male'

  return familyMembers.value.filter(member => {
    // Exclude the selected member
    if (member.id == targetMemberId.value) return false

    // Only opposite gender
    if (member.gender !== oppositeGender) return false

    // Exclude already married members (unless they're married to the selected member)
    if (member.spouse_id && member.spouse_id != targetMemberId.value) return false

    return true
  })
})

// Searchable dropdown methods
const openMemberDropdown = () => {
  showMemberDropdown.value = true
}

const selectMember = (memberId) => {
  targetMemberId.value = memberId
  memberSearchQuery.value = ''
  showMemberDropdown.value = false

  // Load member data into forms
  loadMemberData(memberId)
}

const filterMembers = () => {
  showMemberDropdown.value = true
}

const getSelectedMemberName = () => {
  if (!targetMemberId.value) return ''
  const member = familyMembers.value.find(m => m.id == targetMemberId.value)
  return member ? member.name : ''
}

// Spouse dropdown methods
const openSpouseDropdown = () => {
  showSpouseDropdown.value = true
}

const selectSpouse = (memberId) => {
  selectedSpouseId.value = memberId
  spouseSearchQuery.value = ''
  showSpouseDropdown.value = false
}

const filterSpouseMembers = () => {
  showSpouseDropdown.value = true
}

const getSelectedSpouseName = () => {
  if (!selectedSpouseId.value) return ''
  const spouse = familyMembers.value.find(m => m.id == selectedSpouseId.value)
  return spouse ? spouse.name : ''
}

// Load member data into forms when member is selected
const loadMemberData = (memberId) => {
  const member = familyMembers.value.find(m => m.id == memberId)
  if (member) {
    // Populate biodata form with current data
    biodataForm.value = {
      name: member.name || '',
      email: member.email || '',
      phone: member.phone || '',
      gender: member.gender || '',
      birth_date: member.birth_date || '',
      death_date: member.death_date || ''
    }

    // Set current spouse if exists
    selectedSpouseId.value = member.spouse_id || ''
  }
}

// Reset form function
const resetForm = () => {
  targetMemberId.value = ''
  changeType.value = ''
  newData.value = ''
  photo.value = null
  memberSearchQuery.value = ''
  spouseSearchQuery.value = ''
  selectedSpouseId.value = ''
  biodataForm.value = {
    name: '',
    email: '',
    phone: '',
    gender: '',
    birth_date: '',
    death_date: ''
  }
}

// Close dropdown when clicking outside
const closeDropdowns = () => {
  showMemberDropdown.value = false
  showSpouseDropdown.value = false
}

// Format date helper
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

// Get photo URL helper
const getPhotoUrl = (photoPath) => {
  if (!photoPath) return '/default-avatar.png'

  // If it's already a full URL, return as is
  if (photoPath.startsWith('http')) {
    return photoPath
  }

  // If it's a relative path (stored in storage), construct the full URL
  const backendUrl = window.location.origin.replace('5173', '8000')
  return `${backendUrl}/storage/${photoPath}`
}

onMounted(async () => {
  try {
    const response = await api.get('/family-members')
    familyMembers.value = response.data
  } catch (error) {
    console.error('Error loading family members:', error)
    // Fallback to mock data
    familyMembers.value = [
      { id: 1, name: 'John Doe', generation_level: 3 },
      { id: 2, name: 'Jane Doe', generation_level: 3 }
    ]
  }

  // Add click outside listener
  document.addEventListener('click', (e) => {
    if (memberDropdown.value && !memberDropdown.value.contains(e.target) &&
        !e.target.closest('.dropdown-input')) {
      closeDropdowns()
    }
  })
})
</script>

<style scoped>
.request-update-page {
  min-height: 100vh;
  background: var(--bg-main);
}

.request-content {
  max-width: 1000px;
  margin: 0 auto;
  padding: 30px 20px;
}

.request-header {
  text-align: center;
  margin-bottom: 40px;
}

.request-header h1 {
  color: var(--text-primary);
  font-size: 2.5rem;
  margin-bottom: 10px;
  font-weight: 700;
}

.request-header p {
  color: var(--text-secondary);
  font-size: 1.1rem;
}

.request-form-container {
  background: var(--bg-card);
  border-radius: 16px;
  padding: 32px;
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.1);
  border: 1px solid rgba(107, 79, 63, 0.1);
  margin-bottom: 32px;
}

.request-form {
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
  font-size: 1.4rem;
  font-weight: 600;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
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

.form-select, .form-textarea {
  width: 100%;
  padding: 14px 16px;
  border: 2px solid rgba(107, 79, 63, 0.2);
  border-radius: 8px;
  background: var(--bg-main);
  color: var(--text-primary);
  font-size: 1rem;
  transition: all 0.3s ease;
}

.form-select:focus, .form-textarea:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(107, 79, 63, 0.1);
}

.form-textarea {
  resize: vertical;
  min-height: 120px;
  line-height: 1.5;
}

.form-input {
  width: 100%;
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

.form-hint {
  color: var(--text-muted);
  font-size: 0.85rem;
  font-style: italic;
}

.form-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
  gap: 16px;
  margin-bottom: 24px;
}

.current-data-preview, .current-photo-preview {
  background: rgba(107, 79, 63, 0.05);
  border: 1px solid rgba(107, 79, 63, 0.1);
  border-radius: 12px;
  padding: 20px;
  margin-top: 16px;
}

.current-data-preview h4, .current-photo-preview h4 {
  color: var(--text-primary);
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.current-data-preview h4:before, .current-photo-preview h4:before {
  content: "📊";
  font-size: 1.2rem;
}

.data-preview-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 12px;
}

.data-preview-grid > div {
  background: white;
  padding: 12px 16px;
  border-radius: 8px;
  border: 1px solid rgba(107, 79, 63, 0.1);
  font-size: 0.9rem;
  line-height: 1.4;
}

.relationship-preview {
  background: white;
  padding: 16px;
  border-radius: 8px;
  border: 1px solid rgba(107, 79, 63, 0.1);
}

.photo-preview {
  display: flex;
  justify-content: center;
  align-items: center;
}

.current-photo {
  max-width: 150px;
  max-height: 150px;
  border-radius: 12px;
  object-fit: cover;
  border: 3px solid rgba(107, 79, 63, 0.2);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.no-photo {
  text-align: center;
  padding: 20px;
  background: white;
  border-radius: 12px;
  border: 1px solid rgba(107, 79, 63, 0.1);
  min-width: 150px;
}

.no-photo .avatar-placeholder {
  font-size: 3rem;
  margin-bottom: 8px;
}

.no-photo p {
  color: var(--text-secondary);
  font-size: 0.9rem;
  margin: 0;
}

.file-upload {
  position: relative;
  width: 100%;
}

.file-input {
  position: absolute;
  width: 100%;
  height: 100%;
  opacity: 0;
  cursor: pointer;
  z-index: 2;
}

.file-upload-area {
  border: 2px dashed rgba(107, 79, 63, 0.3);
  border-radius: 12px;
  padding: 32px 24px;
  text-align: center;
  background: rgba(107, 79, 63, 0.02);
  transition: all 0.3s ease;
  cursor: pointer;
}

.file-upload-area:hover {
  border-color: var(--primary);
  background: rgba(107, 79, 63, 0.05);
}

.upload-icon {
  font-size: 3rem;
  margin-bottom: 12px;
  opacity: 0.7;
}

.upload-text {
  display: flex;
  flex-direction: column;
  gap: 4px;
}

.upload-primary {
  color: var(--text-primary);
  font-weight: 500;
}

.upload-secondary {
  color: var(--text-secondary);
  font-size: 0.9rem;
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

.btn-icon {
  font-size: 1.2rem;
}

.message-container {
  margin-top: 24px;
  padding: 20px;
  border-radius: 12px;
  display: flex;
  align-items: flex-start;
  gap: 16px;
  animation: slideIn 0.3s ease;
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

.info-card {
  background: linear-gradient(135deg, rgba(217, 177, 130, 0.1), rgba(255, 255, 255, 0.8));
  border: 1px solid rgba(217, 177, 130, 0.3);
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 4px 15px rgba(217, 177, 130, 0.1);
}

.info-card h3 {
  color: var(--primary);
  font-size: 1.3rem;
  font-weight: 600;
  margin-bottom: 16px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.info-card ul {
  margin: 0;
  padding-left: 20px;
}

.info-card li {
  color: var(--text-secondary);
  margin-bottom: 8px;
  line-height: 1.5;
}

.info-card li:last-child {
  margin-bottom: 0;
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
  .request-content {
    padding: 40px 30px;
  }

  .request-header h1 {
    font-size: 3rem;
  }

  .request-form-container {
    padding: 40px;
  }

  .request-form {
    gap: 40px;
  }

  .form-section {
    padding-bottom: 32px;
  }

  .section-title {
    font-size: 1.5rem;
  }

  .form-actions {
    gap: 20px;
  }

  .cancel-btn, .submit-btn {
    padding: 18px 32px;
    font-size: 1.1rem;
    min-width: 180px;
  }

  .info-card {
    padding: 32px;
  }
}

/* Tablet styles */
@media (min-width: 768px) and (max-width: 1023px) {
  .request-content {
    padding: 30px 20px;
  }

  .request-form-container {
    padding: 28px;
  }

  .form-actions {
    flex-direction: column;
    gap: 12px;
  }

  .cancel-btn, .submit-btn {
    width: 100%;
    justify-content: center;
  }
}

/* Mobile styles */
@media (max-width: 767px) {
  .request-update-page {
    padding-top: 60px; /* Account for fixed nav */
  }

  .request-content {
    padding: 20px 15px;
  }

  .request-header h1 {
    font-size: 2rem;
  }

  .request-form-container {
    padding: 24px 20px;
  }

  .request-form {
    gap: 24px;
  }

  .section-title {
    font-size: 1.2rem;
    flex-direction: column;
    align-items: flex-start;
    gap: 4px;
  }

  .form-select, .form-textarea {
    padding: 12px 14px;
    font-size: 16px; /* Prevent zoom on iOS */
  }

  .file-upload-area {
    padding: 24px 16px;
  }

  .upload-icon {
    font-size: 2.5rem;
  }

  .form-actions {
    flex-direction: column;
    gap: 12px;
  }

  .cancel-btn, .submit-btn {
    width: 100%;
    padding: 16px 20px;
    justify-content: center;
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

  .info-card {
    padding: 20px;
  }

  .info-card ul {
    padding-left: 16px;
  }
}

/* Searchable Dropdown Styles */
.searchable-dropdown {
  position: relative;
  width: 100%;
}

.dropdown-input {
  width: 100%;
  padding: 14px 16px;
  border: 2px solid rgba(107, 79, 63, 0.2);
  border-radius: 8px;
  background: var(--bg-main);
  color: var(--text-primary);
  font-size: 1rem;
  transition: all 0.3s ease;
  cursor: pointer;
}

.dropdown-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(107, 79, 63, 0.1);
  background: white;
}

.dropdown-input::placeholder {
  color: var(--text-muted);
}

.dropdown-input.selected {
  background: rgba(107, 79, 63, 0.05);
  border-color: var(--primary);
  font-weight: 500;
}

.searchable-dropdown.open .dropdown-input {
  border-color: var(--primary);
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
}

.dropdown-list {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 2px solid var(--primary);
  border-top: none;
  border-radius: 0 0 8px 8px;
  max-height: 200px;
  overflow-y: auto;
  z-index: 1000;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.dropdown-item {
  padding: 12px 16px;
  cursor: pointer;
  border-bottom: 1px solid #f0f0f0;
  transition: background-color 0.2s ease;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.dropdown-item:last-child {
  border-bottom: none;
}

.dropdown-item:hover {
  background: rgba(107, 79, 63, 0.05);
}

.dropdown-item.selected {
  background: rgba(107, 79, 63, 0.1);
  font-weight: 600;
}

.dropdown-item.disabled {
  color: #999;
  cursor: not-allowed;
  font-style: italic;
}

.dropdown-item.disabled:hover {
  background: none;
}

.generation-info {
  font-size: 12px;
  color: var(--primary);
  font-weight: 500;
  opacity: 0.8;
}
</style>
