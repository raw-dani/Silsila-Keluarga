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
              <select id="targetMember" v-model="targetMemberId" required class="form-select">
                <option value="">Pilih anggota keluarga</option>
                <option v-for="member in familyMembers" :key="member.id" :value="member.id">
                  {{ member.name }} ({{ getGenerationSymbol(member.generation_level) }})
                </option>
              </select>
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

          <!-- New Data -->
          <div class="form-section">
            <h3 class="section-title">✏️ Detail Perubahan</h3>
            <div class="form-group">
              <label for="newData">Jelaskan perubahan yang diinginkan</label>
              <textarea
                id="newData"
                v-model="newData"
                placeholder="Contoh: Ubah nama dari 'John' menjadi 'Jonathan', atau tambahkan hubungan ayah dengan anggota X"
                required
                class="form-textarea"
                rows="6"
              ></textarea>
              <small class="form-hint">Berikan detail yang jelas agar admin dapat memproses permintaan dengan tepat</small>
            </div>
          </div>

          <!-- Photo Upload -->
          <div class="form-section">
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
          </div>

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
import { ref, onMounted } from 'vue'
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
    formData.append('new_data', newData.value)
    if (photo.value) {
      formData.append('photo', photo.value)
    }

    await api.post('/update-requests', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    message.value = 'Pengajuan berhasil dikirim! Admin akan memproses permintaan Anda dalam 1-3 hari kerja.'
    messageType.value = 'success'

    // Reset form
    targetMemberId.value = ''
    changeType.value = ''
    newData.value = ''
    photo.value = null

    // Auto-hide message after 5 seconds
    setTimeout(() => {
      message.value = ''
    }, 5000)
  } catch (error) {
    message.value = error.response?.data?.message || 'Gagal mengirim pengajuan. Silakan coba lagi.'
    messageType.value = 'error'
  } finally {
    loading.value = false
  }
}
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

.form-hint {
  color: var(--text-muted);
  font-size: 0.85rem;
  font-style: italic;
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
</style>
