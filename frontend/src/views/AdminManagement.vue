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

onMounted(() => {
  loadAdmins()
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
