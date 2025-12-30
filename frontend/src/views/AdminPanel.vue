<template>
  <div class="admin-panel-page">
    <Navigation />

    <div class="admin-panel-content">
      <div class="panel-header">
        <h1>Panel Admin - Kelola Data Keluarga</h1>
        <p>Kelola anggota keluarga dan approve permintaan perubahan</p>
      </div>

    <div class="tabs">
      <button v-if="isAdmin" :class="{ active: activeTab === 'members' }" @click="activeTab = 'members'">Kelola Anggota</button>
      <button :class="{ active: activeTab === 'requests' }" @click="activeTab = 'requests'">Request Perubahan</button>      
    </div>

    <div v-if="activeTab === 'requests'" class="requests-section">
      <h2>Permintaan Perubahan Pending</h2>
      <div v-if="pendingRequests.length === 0" class="no-requests">
        Tidak ada permintaan pending.
      </div>
      <div v-else v-for="request in pendingRequests" :key="request.id" class="request-card">
        <h3>Permintaan dari: {{ request.member?.name }}</h3>
        <p v-if="request.change_type === 'add_member'">
          <strong>Jenis:</strong> Penambahan Anggota Keluarga
        </p>
        <p v-else>
          <strong>Anggota:</strong> {{ request.target_member?.name }}
        </p>
        <p v-if="request.change_type === 'add_member'">
          <strong>Detail Anggota Baru:</strong>
        </p>
        <p v-else>
          <strong>Jenis:</strong> {{ getChangeTypeLabel(request.change_type) }}
        </p>
        <div v-if="request.change_type === 'add_member'" class="member-data-preview">
          <div v-if="getMemberData(request)" class="member-info-preview">
            <p><strong>Nama:</strong> {{ getMemberData(request).name }}</p>
            <p><strong>Jenis Kelamin:</strong> {{ getMemberData(request).gender === 'male' ? 'Laki-laki' : 'Perempuan' }}</p>
            <p v-if="getMemberData(request).birth_date"><strong>Tanggal Lahir:</strong> {{ formatDate(getMemberData(request).birth_date) }}</p>
            <p v-if="getMemberData(request).relationship_type">
              <strong>Hubungan:</strong>
              <span v-if="request.target_member_id && getRelationInfo(request)">
                {{ getMemberData(request).relationship_type === 'child' ? 'Anak dari' : 'Pasangan dari' }} {{ getRelationInfo(request) }}
              </span>
              <span v-else>
                {{ getMemberData(request).relationship_type === 'child' ? 'Anak' : 'Pasangan' }}
              </span>
            </p>
          </div>
        </div>
        <!-- Dynamic content based on change type -->
        <div v-else class="request-details">
          <div v-if="getRequestType(request) === 'biodata'" class="biodata-details">
            <h4>📋 Biodata Baru:</h4>
            <div class="data-grid">
              <div v-if="getBiodataField(request, 'name')"><strong>Nama:</strong> {{ getBiodataField(request, 'name') }}</div>
              <div v-if="getBiodataField(request, 'email')"><strong>Email:</strong> {{ getBiodataField(request, 'email') }}</div>
              <div v-if="getBiodataField(request, 'phone')"><strong>Telepon:</strong> {{ getBiodataField(request, 'phone') }}</div>
              <div v-if="getBiodataField(request, 'gender')"><strong>Jenis Kelamin:</strong> {{ getBiodataField(request, 'gender') === 'male' ? 'Laki-laki' : 'Perempuan' }}</div>
              <div v-if="getBiodataField(request, 'birth_date')"><strong>Tanggal Lahir:</strong> {{ formatDate(getBiodataField(request, 'birth_date')) }}</div>
              <div v-if="getBiodataField(request, 'death_date')"><strong>Tanggal Meninggal:</strong> {{ formatDate(getBiodataField(request, 'death_date')) }}</div>
            </div>
          </div>

          <div v-else-if="getRequestType(request) === 'hubungan'" class="relationship-details">
            <h4>👨‍👩‍👧 Hubungan Baru:</h4>
            <div class="relationship-info">
              <div><strong>Pasangan:</strong> {{ getSpouseName(request) }}</div>
            </div>
          </div>

          <div v-else-if="getRequestType(request) === 'foto'" class="photo-details">
            <h4>📸 Foto Baru:</h4>
            <div class="photo-preview">
              <img
                v-if="request.photo"
                :src="getPhotoUrl(request.photo)"
                :alt="'Foto baru untuk ' + request.targetMember?.name"
                class="request-photo"
              >
              <div v-else class="no-photo">
                <div class="photo-placeholder">📸</div>
                <p>Foto akan diupload saat pengajuan</p>
              </div>
            </div>
          </div>

          <div v-else class="generic-details">
            <strong>Data Baru:</strong> {{ request.new_data }}
          </div>
        </div>
        <div class="actions">
          <button @click="approveRequest(request.id)" class="approve-btn">Approve</button>
          <button @click="rejectRequest(request.id)" class="reject-btn">Reject</button>
        </div>
      </div>
    </div>

    <div v-if="activeTab === 'members'" class="members-section">
      <div class="members-header">
        <h2>Daftar Anggota Keluarga</h2>
        <div class="header-actions" v-if="isAdmin">
          <!-- <button @click="fixSpouseRelationships" class="fix-btn" title="Perbaiki Hubungan Pasangan">
            <span class="btn-icon">🔗</span>
            <span>Perbaiki Pasangan</span>
          </button> -->
          <button @click="openAddForm()" class="add-btn">Tambah Anggota</button>
        </div>
      </div>

      <div class="members-controls">
        <div class="search-container">
          <input
            type="text"
            v-model="searchQuery"
            placeholder="Cari anggota keluarga..."
            class="search-input"
          >
          <span class="search-icon">🔍</span>
        </div>

        <div class="filter-controls">
          <select v-model="sortBy" class="sort-select">
            <option value="name">Urutkan: Nama</option>
            <option value="generation">Urutkan: Generasi</option>
            <option value="gender">Urutkan: Jenis Kelamin</option>
          </select>

          <select v-model="filterGender" class="filter-select">
            <option value="">Semua Jenis Kelamin</option>
            <option value="male">Laki-laki</option>
            <option value="female">Perempuan</option>
          </select>
        </div>
      </div>

      <div class="members-bulk-actions" v-if="isAdmin && selectedMembers.length > 0">
        <span class="selected-count">{{ selectedMembers.length }} anggota dipilih</span>
        <button @click="bulkDeleteMembers" class="bulk-delete-btn">
          <span class="btn-icon">🗑️</span>
          <span>Hapus Terpilih</span>
        </button>
        <button @click="clearSelection" class="clear-selection-btn">Batal Pilih</button>
      </div>

      <div class="members-controls-header">
        <div class="members-stats">
          <span class="stats-text">Menampilkan {{ filteredMembers.length }} dari {{ familyMembers.length }} anggota</span>
        </div>
        <div class="selection-controls" v-if="isAdmin">
          <label class="select-all-label">
            <input type="checkbox" v-model="selectAll" @change="toggleSelectAll" class="select-all-checkbox">
            <span>Pilih Semua</span>
          </label>
        </div>
      </div>

      <div class="members-grid">
        <div v-for="member in filteredMembers" :key="member.id" class="member-card">
          <div v-if="isAdmin" class="member-selection">
            <input
              type="checkbox"
              :value="member.id"
              v-model="selectedMembers"
              class="member-checkbox"
            >
          </div>

          <div class="member-card-content">
            <div class="member-avatar">
              <img v-if="member.photo" :src="getPhotoUrl(member.photo)" :alt="member.name" class="avatar-img">
              <div v-else class="avatar-placeholder">
                {{ member.gender === 'male' ? '👨' : member.gender === 'female' ? '👩' : '👤' }}
              </div>
            </div>

            <div class="member-info">
              <h3 class="member-name">{{ member.name }}</h3>
              <div class="member-details">
                <span class="member-generation">{{ getGenerationSymbol(member.generation_level) }}</span>
                <span class="member-gender" :class="member.gender">
                  {{ member.gender === 'male' ? '♂' : member.gender === 'female' ? '♀' : '⚲' }}
                </span>
              </div>
              <div class="member-birth" v-if="member.birth_date">
                <small>Lahir: {{ formatDate(member.birth_date) }}</small>
              </div>
              <div class="member-parents" v-if="getParentNames(member)">
                <small>Orang tua: {{ getParentNames(member) }}</small>
              </div>
              <div class="member-spouse" v-if="member.spouse">
                <small>Pasangan: {{ member.spouse.name }}</small>
              </div>
            </div>
          </div>

          <div class="member-actions" v-if="isAdmin">
            <button @click="viewMemberDetail(member.id)" class="view-btn" title="Lihat Detail">
              <span class="btn-icon"></span>
              <span class="btn-text">Detail</span>
            </button>
            <button @click="openEditForm(member)" class="edit-btn" title="Edit">
              <span class="btn-icon"></span>
              <span class="btn-text">Edit</span>
            </button>
            <button
              v-if="member.email && !member.is_user"
              @click="convertToUser(member.id)"
              class="convert-btn"
              title="Konversi ke User"
            >
              <span class="btn-icon"></span>
              <span class="btn-text">User</span>
            </button>
            <button
              v-if="member.is_user"
              class="user-badge"
              title="Sudah menjadi user"
              disabled
            >
              <span class="btn-icon">✅</span>
              <span class="btn-text">User</span>
            </button>
            <button @click="deleteMember(member.id)" class="delete-btn" title="Hapus">
              <span class="btn-icon"></span>
              <span class="btn-text">Hapus</span>
            </button>
          </div>
        </div>
      </div>

      <div v-if="filteredMembers.length === 0" class="no-members">
        <div class="empty-icon">👥</div>
        <h3>Tidak ada anggota ditemukan</h3>
        <p>Coba ubah kriteria pencarian atau filter</p>
      </div>
    </div>

    <!-- Modal Form -->
    <div v-if="showForm" class="modal-overlay" @click="closeForm">
      <div class="modal-content" @click.stop>
        <h3>{{ isEditing ? 'Edit Anggota' : 'Tambah Anggota' }}</h3>
        <form @submit.prevent="saveMember">
          <div class="form-group">
            <label>Nama:</label>
            <input v-model="formData.name" type="text" required>
          </div>
          <div class="form-group">
            <label>Email (opsional):</label>
            <input v-model="formData.email" type="email" placeholder="contoh@email.com">
          </div>
          <div class="form-group">
            <label>Nomor Telepon (opsional):</label>
            <input v-model="formData.phone" type="tel" placeholder="+628xxxxxxxxx">
          </div>
          <div class="form-group">
            <label>Jenis Kelamin:</label>
            <select v-model="formData.gender" required>
              <option value="male">Laki-laki</option>
              <option value="female">Perempuan</option>
            </select>
          </div>
          <div class="form-group">
            <label>Tanggal Lahir (opsional):</label>
            <input v-model="formData.birth_date" type="date">
          </div>
          <div class="form-group">
            <label>Tanggal Meninggal (opsional):</label>
            <input v-model="formData.death_date" type="date">
          </div>
          <div class="form-group">
            <label>Ayah:</label>
            <div class="searchable-dropdown" :class="{ open: showFatherDropdown }">
              <input
                type="text"
                v-model="fatherSearch"
                @focus="openFatherDropdown"
                @input="fatherSearch = $event.target.value"
                :placeholder="getSelectedFatherName() || 'Cari ayah...'"
                class="dropdown-input"
              >
              <div v-if="showFatherDropdown" class="dropdown-list" ref="fatherDropdown">
                <div
                  v-for="member in filteredFatherMembers.slice(0, 10)"
                  :key="member.id"
                  @click="selectFather(member.id)"
                  class="dropdown-item"
                  :class="{ selected: formData.father_id == member.id }"
                >
                  {{ member.name }} <span class="generation-info">{{ getGenerationSymbol(member.generation_level) }}</span>
                </div>
                <div v-if="filteredFatherMembers.length === 0" class="dropdown-item disabled">
                  Tidak ada hasil
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Ibu:</label>
            <div class="searchable-dropdown" :class="{ open: showMotherDropdown }">
              <input
                type="text"
                v-model="motherSearch"
                @focus="openMotherDropdown"
                @input="motherSearch = $event.target.value"
                :placeholder="getSelectedMotherName() || 'Cari ibu...'"
                class="dropdown-input"
              >
              <div v-if="showMotherDropdown" class="dropdown-list" ref="motherDropdown">
                <div
                  v-for="member in filteredMotherMembers.slice(0, 10)"
                  :key="member.id"
                  @click="selectMother(member.id)"
                  class="dropdown-item"
                  :class="{ selected: formData.mother_id == member.id }"
                >
                  {{ member.name }} <span class="generation-info">{{ getGenerationSymbol(member.generation_level) }}</span>
                </div>
                <div v-if="filteredMotherMembers.length === 0" class="dropdown-item disabled">
                  Tidak ada hasil
                </div>
              </div>
            </div>
          </div>
          <div class="form-group">
            <label>Pasangan:</label>
            <div class="searchable-dropdown" :class="{ open: showSpouseDropdown }">
              <input
                type="text"
                v-model="spouseSearch"
                @focus="openSpouseDropdown"
                @input="spouseSearch = $event.target.value"
                :placeholder="getSelectedSpouseName() || 'Cari pasangan...'"
                class="dropdown-input"
              >
              <div v-if="showSpouseDropdown" class="dropdown-list" ref="spouseDropdown">
                <div
                  v-for="member in filteredSpouseMembers.slice(0, 10)"
                  :key="member.id"
                  @click="selectSpouse(member.id)"
                  class="dropdown-item"
                  :class="{ selected: formData.spouse_id == member.id }"
                >
                  {{ member.name }} <span class="generation-info">{{ getGenerationSymbol(member.generation_level) }}</span>
                </div>
                <div v-if="filteredSpouseMembers.length === 0" class="dropdown-item disabled">
                  Tidak ada hasil
                </div>
              </div>
            </div>
          </div>

          <div class="form-group">
            <label>Catatan:</label>
            <textarea v-model="formData.notes"></textarea>
          </div>
          <div class="form-group">
            <label>Foto:</label>
            <input type="file" @change="handleFileUpload" accept="image/*">
          </div>
          <div class="form-actions">
            <button type="submit" :disabled="loading">{{ loading ? 'Menyimpan...' : 'Simpan' }}</button>
            <button type="button" @click="closeForm">Batal</button>
          </div>
        </form>
      </div>
    </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'
import Navigation from '../components/Navigation.vue'

const router = useRouter()
const activeTab = ref('members')
const pendingRequests = ref([])
const familyMembers = ref([])
const treeData = ref([])
const showForm = ref(false)
const isEditing = ref(false)
const loading = ref(false)
const selectedMember = ref(null)
const photoFile = ref(null)
// Removed: const selectedAvatar = ref('')

// User role
const userRole = ref('')

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

// Search and filter properties
const searchQuery = ref('')
const sortBy = ref('name')
const filterGender = ref('')

// Dropdown search properties
const fatherSearch = ref('')
const motherSearch = ref('')
const spouseSearch = ref('')
const showFatherDropdown = ref(false)
const showMotherDropdown = ref(false)
const showSpouseDropdown = ref(false)

// Bulk selection properties
const selectedMembers = ref([])
const selectAll = ref(false)

const formData = ref({
  name: '',
  email: '',
  phone: '',
  gender: 'male',
  birth_date: '',
  death_date: '',
  father_id: '',
  mother_id: '',
  spouse_id: '',
  notes: '',
  avatar: ''
})

const isAdmin = computed(() => userRole.value === 'admin')

const maleMembers = computed(() => familyMembers.value.filter(m => m.gender === 'male'))
const femaleMembers = computed(() => familyMembers.value.filter(m => m.gender === 'female'))
const availableSpouses = computed(() => {
  return familyMembers.value.filter(m => {
    // Exclude member yang sedang diedit
    if (isEditing.value && selectedMember.value && m.id === selectedMember.value.id) {
      return false
    }

    // Include spouse yang sudah ada untuk member ini (saat edit)
    if (isEditing.value && selectedMember.value && selectedMember.value.spouse_id === m.id) {
      return true
    }

    // Exclude yang sudah punya spouse (kecuali spouse dari member yang sedang diedit)
    if (m.spouse_id && m.spouse_id !== selectedMember.value?.id) {
      return false
    }

    // Filter berdasarkan gender yang berlawanan
    const currentGender = formData.value.gender
    if (currentGender === 'male') {
      return m.gender === 'female'
    } else if (currentGender === 'female') {
      return m.gender === 'male'
    }

    return false
  })
})



const memberGenerations = computed(() => {
  const generations = {}

  // Load tree data synchronously if available
  if (treeData.value && treeData.value.length > 0) {
    const findGenerationsInTree = (nodes) => {
      for (const node of nodes) {
        generations[node.id] = node.generation
        if (node.children && node.children.length > 0) {
          findGenerationsInTree(node.children)
        }
      }
    }
    findGenerationsInTree(treeData.value)
  }

  return generations
})

// Filtered and sorted members for the members list
const filteredMembers = computed(() => {
  let filtered = familyMembers.value.filter(member => {
    // Search filter
    const matchesSearch = searchQuery.value === '' ||
      member.name.toLowerCase().includes(searchQuery.value.toLowerCase())

    // Gender filter
    const matchesGender = filterGender.value === '' || member.gender === filterGender.value

    return matchesSearch && matchesGender
  })

  // Sort the filtered results
  filtered.sort((a, b) => {
    switch (sortBy.value) {
      case 'name':
        return a.name.localeCompare(b.name)
      case 'generation':
        const genA = memberGenerations.value[a.id] || a.generation_level
        const genB = memberGenerations.value[b.id] || b.generation_level
        return genA - genB
      case 'gender':
        return a.gender.localeCompare(b.gender)
      default:
        return 0
    }
  })

  return filtered
})

// Filtered members for searchable dropdowns
const filteredFatherMembers = computed(() => {
  return maleMembers.value.filter(member =>
    fatherSearch.value === '' ||
    member.name.toLowerCase().includes(fatherSearch.value.toLowerCase())
  )
})

const filteredMotherMembers = computed(() => {
  return femaleMembers.value.filter(member =>
    motherSearch.value === '' ||
    member.name.toLowerCase().includes(motherSearch.value.toLowerCase())
  )
})

const filteredSpouseMembers = computed(() => {
  return availableSpouses.value.filter(member =>
    spouseSearch.value === '' ||
    member.name.toLowerCase().includes(spouseSearch.value.toLowerCase())
  )
})

onMounted(() => {
  loadRequests()
  loadMembers()
})

// Watchers for automatic parent selection
watch(() => formData.value.father_id, (newFatherId, oldFatherId) => {
  // Prevent infinite loop by checking if value actually changed
  if (newFatherId && showForm.value && newFatherId !== oldFatherId) {
    // Find the father's spouse and set as mother
    const father = familyMembers.value.find(m => m.id == newFatherId)
    if (father && father.spouse_id) {
      formData.value.mother_id = father.spouse_id
    }
  }
})

watch(() => formData.value.mother_id, (newMotherId, oldMotherId) => {
  // Prevent infinite loop by checking if value actually changed
  if (newMotherId && showForm.value && newMotherId !== oldMotherId) {
    // Find the mother's spouse and set as father
    const mother = familyMembers.value.find(m => m.id == newMotherId)
    if (mother && mother.spouse_id) {
      formData.value.father_id = mother.spouse_id
    }
  }
})

const loadRequests = async () => {
  try {
    const response = await api.get('/update-requests?status=pending')
    pendingRequests.value = response.data

    console.log('🎯 DEBUG AdminPanel: Loaded requests:', pendingRequests.value)
    pendingRequests.value.forEach((request, index) => {
    console.log(`Request ${index}:`, {
        id: request.id,
        change_type: request.change_type,
        target_member_id: request.target_member_id,
        photo: request.photo,
        new_data: request.new_data,
        member: request.member,
        target_member: request.target_member,
        targetMemberName: request.target_member?.name,
        hasTargetMemberRelation: !!request.target_member
      })
    })
  } catch (error) {
    console.error('Error loading requests:', error)
  }
}

const loadMembers = async () => {
  try {
    const response = await api.get('/family-members')
    familyMembers.value = response.data

    // Also load tree data for generation calculations
    await loadTreeData()
  } catch (error) {
    console.error('Error loading members:', error)
  }
}

const loadTreeData = async () => {
  try {
    const response = await api.get('/family-tree')
    treeData.value = response.data
  } catch (error) {
    console.error('Error loading tree data:', error)
  }
}

const approveRequest = async (id) => {
  try {
    await api.post(`/update-requests/${id}/approve`)
    alert('Request approved successfully')
    loadRequests()
    loadMembers() // Refresh members list to show newly added members
  } catch (error) {
    alert('Error approving request')
  }
}

const rejectRequest = async (id) => {
  try {
    await api.post(`/update-requests/${id}/reject`)
    alert('Request rejected successfully')
    loadRequests()
  } catch (error) {
    alert('Error rejecting request')
  }
}

const openAddForm = () => {
  isEditing.value = false
  selectedMember.value = null
  formData.value = {
    name: '',
    gender: 'male',
    birth_date: '',
    death_date: '',
    father_id: '',
    mother_id: '',
    spouse_id: '',
    notes: '',
    avatar: ''
  }
  photoFile.value = null
  showForm.value = true
}

const openEditForm = (member) => {
  isEditing.value = true
  selectedMember.value = member
  formData.value = { ...member }
  photoFile.value = null
  showForm.value = true
}

const closeForm = () => {
  showForm.value = false
}

const handleFileUpload = (event) => {
  photoFile.value = event.target.files[0]
}



const saveMember = async () => {
  loading.value = true
  try {
    const data = new FormData()
    Object.keys(formData.value).forEach(key => {
      // Skip empty parent IDs, null values, and empty strings
      if (formData.value[key] !== '' && formData.value[key] !== null && formData.value[key] !== undefined) {
        // For parent IDs, only include if they have a valid value
        if ((key === 'father_id' || key === 'mother_id' || key === 'spouse_id') && formData.value[key] === '') {
          return // Skip empty parent/spouse IDs
        }
        // Skip photo field as it's handled separately via photoFile
        if (key !== 'photo') {
          data.append(key, formData.value[key])
        }
      }
    })



    if (photoFile.value) {
      data.append('photo', photoFile.value)
    }

    if (isEditing.value) {
      await api.post(`/family-members/${selectedMember.value.id}?_method=PUT`, data, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      alert('Member updated successfully')
    } else {
      await api.post('/family-members', data, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      alert('Member added successfully')
    }

    closeForm()
    loadMembers()
  } catch (error) {
    alert('Error saving member: ' + (error.response?.data?.message || error.message))
  } finally {
    loading.value = false
  }
}

const deleteMember = async (id) => {
  if (!confirm('Yakin hapus anggota ini?')) return

  try {
    await api.delete(`/family-members/${id}`)
    alert('Member deleted successfully')
    loadMembers()
  } catch (error) {
    alert('Error deleting member')
  }
}

// Bulk selection functions
const toggleSelectAll = () => {
  if (selectAll.value) {
    selectedMembers.value = filteredMembers.value.map(member => member.id)
  } else {
    selectedMembers.value = []
  }
}

const bulkDeleteMembers = async () => {
  if (selectedMembers.value.length === 0) return

  const confirmMessage = `Yakin hapus ${selectedMembers.value.length} anggota yang dipilih?`
  if (!confirm(confirmMessage)) return

  try {
    // Delete each selected member
    const deletePromises = selectedMembers.value.map(id => api.delete(`/family-members/${id}`))
    await Promise.all(deletePromises)

    alert(`${selectedMembers.value.length} anggota berhasil dihapus`)
    selectedMembers.value = []
    selectAll.value = false
    loadMembers()
  } catch (error) {
    alert('Error deleting members: ' + (error.response?.data?.message || error.message))
  }
}

const clearSelection = () => {
  selectedMembers.value = []
  selectAll.value = false
}

const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}

const fixSpouseRelationships = async () => {
  if (!confirm('Apakah Anda yakin ingin memperbaiki hubungan pasangan? Ini akan membuat semua hubungan pasangan menjadi dua arah.')) {
    return
  }

  try {
    const response = await api.post('/fix-spouse-relationships')
    alert(`Berhasil diperbaiki: ${response.data.message}`)
    // Reload data to see the changes
    loadMembers()
  } catch (error) {
    console.error('Error fixing spouse relationships:', error)
    alert('Gagal memperbaiki hubungan pasangan: ' + (error.response?.data?.message || error.message))
  }
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

const getParentNames = (member) => {
  const parents = []

  if (member.father_id) {
    const father = familyMembers.value.find(m => m.id == member.father_id)
    if (father) parents.push(father.name)
  }

  if (member.mother_id) {
    const mother = familyMembers.value.find(m => m.id == member.mother_id)
    if (mother) parents.push(mother.name)
  }

  return parents.length > 0 ? parents.join(' & ') : null
}

// Searchable dropdown methods
const openFatherDropdown = () => {
  showFatherDropdown.value = true
  showMotherDropdown.value = false
  showSpouseDropdown.value = false
}

const openMotherDropdown = () => {
  showMotherDropdown.value = true
  showFatherDropdown.value = false
  showSpouseDropdown.value = false
}

const openSpouseDropdown = () => {
  showSpouseDropdown.value = true
  showFatherDropdown.value = false
  showMotherDropdown.value = false
}

const selectFather = (memberId) => {
  formData.value.father_id = memberId
  fatherSearch.value = ''
  showFatherDropdown.value = false
}

const selectMother = (memberId) => {
  formData.value.mother_id = memberId
  motherSearch.value = ''
  showMotherDropdown.value = false
}

const selectSpouse = (memberId) => {
  formData.value.spouse_id = memberId
  spouseSearch.value = ''
  showSpouseDropdown.value = false
}

const getSelectedFatherName = () => {
  if (!formData.value.father_id) return ''
  const father = familyMembers.value.find(m => m.id == formData.value.father_id)
  return father ? father.name : ''
}

const getSelectedMotherName = () => {
  if (!formData.value.mother_id) return ''
  const mother = familyMembers.value.find(m => m.id == formData.value.mother_id)
  return mother ? mother.name : ''
}

const getSelectedSpouseName = () => {
  if (!formData.value.spouse_id) return ''
  const spouse = familyMembers.value.find(m => m.id == formData.value.spouse_id)
  return spouse ? spouse.name : ''
}

// Close dropdowns when clicking outside
const closeDropdowns = () => {
  showFatherDropdown.value = false
  showMotherDropdown.value = false
  showSpouseDropdown.value = false
}

// Add click outside listener
onMounted(() => {
  userRole.value = localStorage.getItem('role') || 'member'
  loadRequests()
  loadMembers()
  document.addEventListener('click', (e) => {
    // Close dropdowns if clicked outside
    const dropdowns = document.querySelectorAll('.searchable-dropdown')
    let clickedOutside = true
    dropdowns.forEach(dropdown => {
      if (dropdown.contains(e.target)) {
        clickedOutside = false
      }
    })
    if (clickedOutside) {
      closeDropdowns()
    }
  })
})

const viewMemberDetail = (memberId) => {
  // Navigate to the profile page for this specific member
  router.push(`/profile/${memberId}`)
}

const convertToUser = async (memberId) => {
  if (!confirm('Apakah Anda yakin ingin mengkonversi anggota ini menjadi user? Mereka akan dapat login ke aplikasi.')) {
    return
  }

  try {
    await api.post(`/family-members/${memberId}/convert-to-user`)
    alert('Anggota berhasil dikonversi menjadi user! Mereka sekarang dapat login ke aplikasi.')
    loadMembers()
  } catch (error) {
    alert('Gagal mengkonversi anggota: ' + (error.response?.data?.message || error.message))
  }
}

const getMemberGeneration = async (member) => {
  // Get the tree data to see how backend calculates generations
  try {
    const response = await api.get('/family-tree')
    const treeData = response.data

    // Find the member in the tree data
    const findMemberInTree = (nodes) => {
      for (const node of nodes) {
        if (node.id == member.id) {
          return node
        }
        if (node.children && node.children.length > 0) {
          const found = findMemberInTree(node.children)
          if (found) return found
        }
      }
      return null
    }

    const treeMember = findMemberInTree(treeData)
    return treeMember ? treeMember.generation : member.generation_level
  } catch (error) {
    console.error('Error fetching tree data for generation:', error)
    return member.generation_level
  }
}

const getChangeTypeLabel = (changeType) => {
  const labels = {
    'biodata': 'Perubahan Biodata',
    'hubungan': 'Perubahan Hubungan',
    'foto': 'Perubahan Foto',
    'add_member': 'Penambahan Anggota'
  }
  return labels[changeType] || changeType
}

const getMemberData = (request) => {
  if (request.change_type !== 'add_member') return null

  try {
    return JSON.parse(request.new_data)
  } catch (error) {
    console.error('Error parsing member data:', error)
    return null
  }
}

const getRelationInfo = (request) => {
  if (!request.target_member_id) return null

  const targetMember = familyMembers.value.find(m => m.id == request.target_member_id)
  return targetMember ? targetMember.name : null
}

// Request data parsing functions
const getRequestType = (request) => {
  try {
    const data = JSON.parse(request.new_data)
    return data.type || null
  } catch (error) {
    return null
  }
}

const getBiodataField = (request, field) => {
  try {
    const data = JSON.parse(request.new_data)
    if (data.type === 'biodata' && data.data) {
      return data.data[field] || null
    }
    return null
  } catch (error) {
    return null
  }
}

const getSpouseName = (request) => {
  try {
    const data = JSON.parse(request.new_data)
    if (data.type === 'hubungan') {
      return data.spouse_name || 'Tidak ditemukan'
    }
    return null
  } catch (error) {
    return null
  }
}
</script>

<style scoped>
.admin-panel-page {
  min-height: 100vh;
  background: #ffffff;
}

.admin-panel-content {
  max-width: 1400px;
  margin: 0 auto;
  padding: 30px;
}

.panel-header {
  text-align: center;
  margin-bottom: 30px;
}

.panel-header h1 {
  color: var(--text-primary);
  font-size: 2.5rem;
  margin-bottom: 10px;
  font-weight: 700;
}

.panel-header p {
  color: var(--text-primary);
  font-size: 1.1rem;
}

.admin-panel h1 {
  color: white;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
  text-align: center;
  margin-bottom: 30px;
  font-size: 2.5rem;
}

.tabs {
  display: flex;
  margin-bottom: 30px;
  background: rgba(107, 79, 63, 0.1);
  backdrop-filter: blur(10px);
  border-radius: 12px;
  padding: 5px;
  box-shadow: 0 8px 32px rgba(107, 79, 63, 0.15);
  border: 1px solid rgba(107, 79, 63, 0.2);
}

.tabs button {
  flex: 1;
  padding: 15px 20px;
  border: none;
  background: transparent;
  color: var(--text-secondary);
  cursor: pointer;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.tabs button.active {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.3);
}

.tabs button:hover {
  background: rgba(107, 79, 63, 0.2);
  color: var(--text-primary);
}

.requests-section, .members-section {
  background: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  padding: 30px;
  box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
  margin-top: 20px;
}

.requests-section h2, .members-section h2 {
  color: #333;
  margin-bottom: 20px;
  font-size: 1.8rem;
}

.request-card, .member-card {
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 15px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  transition: transform 0.3s ease;
}

.request-card:hover, .member-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
}

.request-details {
  margin-top: 15px;
}

.biodata-details, .relationship-details, .photo-details {
  background: rgba(107, 79, 63, 0.05);
  border: 1px solid rgba(107, 79, 63, 0.1);
  border-radius: 12px;
  padding: 16px;
  margin-bottom: 12px;
}

.biodata-details h4, .relationship-details h4, .photo-details h4 {
  color: var(--text-primary);
  font-size: 1.1rem;
  font-weight: 600;
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}

.data-grid {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 8px;
}

.data-grid > div {
  background: white;
  padding: 8px 12px;
  border-radius: 6px;
  border: 1px solid rgba(107, 79, 63, 0.1);
  font-size: 0.9rem;
  line-height: 1.4;
}

.relationship-info {
  background: white;
  padding: 12px 16px;
  border-radius: 6px;
  border: 1px solid rgba(107, 79, 63, 0.1);
}

.photo-preview {
  display: flex;
  justify-content: center;
  align-items: center;
  background: white;
  border-radius: 8px;
  border: 1px solid rgba(107, 79, 63, 0.1);
  padding: 16px;
}

.request-photo {
  max-width: 200px;
  max-height: 200px;
  border-radius: 8px;
  object-fit: cover;
  border: 2px solid rgba(107, 79, 63, 0.2);
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.photo-placeholder {
  font-size: 4rem;
  opacity: 0.6;
}

.generic-details {
  background: rgba(244, 67, 54, 0.05);
  border: 1px solid rgba(244, 67, 54, 0.2);
  border-radius: 8px;
  padding: 12px 16px;
  margin-top: 15px;
  color: var(--text-secondary);
  font-size: 0.9rem;
}

.actions {
  margin-top: 15px;
  display: flex;
  gap: 10px;
  flex-wrap: wrap;
}

.approve-btn, .edit-btn {
  background: linear-gradient(135deg, var(--secondary), var(--accent));
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.approve-btn:hover, .edit-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 15px rgba(166, 124, 82, 0.3);
}

.reject-btn, .delete-btn {
  background: linear-gradient(135deg, var(--error), var(--error));
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.3s ease;
}

.reject-btn:hover, .delete-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 15px rgba(244, 67, 54, 0.3);
}

.add-btn {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  border: none;
  padding: 15px 30px;
  border-radius: 25px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.3);
  margin-bottom: 25px;
}

.add-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(107, 79, 63, 0.4);
}

/* Modal Styles */
.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.6);
  backdrop-filter: blur(5px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal-content {
  background: white;
  padding: 30px;
  border-radius: 16px;
  max-width: 600px;
  width: 90%;
  max-height: 80vh;
  overflow-y: auto;
  box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.modal-content h3 {
  margin: 0 0 25px 0;
  color: #333;
  font-size: 1.8rem;
  text-align: center;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #555;
  font-size: 14px;
}

.form-group input,
.form-group select,
.form-group textarea {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #e1e5e9;
  border-radius: 8px;
  box-sizing: border-box;
  font-size: 16px;
  transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #42b883;
  box-shadow: 0 0 0 3px rgba(66, 184, 131, 0.1);
}

.form-group textarea {
  height: 100px;
  resize: vertical;
}

.form-actions {
  display: flex;
  gap: 15px;
  justify-content: flex-end;
  margin-top: 30px;
}

.form-actions button {
  padding: 12px 24px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  transition: all 0.3s ease;
}

.form-actions button[type="submit"] {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.3);
}

.form-actions button[type="submit"]:hover {
  transform: translateY(-1px);
  box-shadow: 0 6px 20px rgba(66, 184, 131, 0.4);
}

.form-actions button[type="button"] {
  background: #f8f9fa;
  color: #6c757d;
  border: 2px solid #e9ecef;
}

.form-actions button[type="button"]:hover {
  background: #e9ecef;
}

.form-hint {
  color: #666;
  font-size: 14px;
  margin-top: 5px;
}

/* Searchable Dropdown Styles */
.searchable-dropdown {
  position: relative;
  width: 100%;
}

.dropdown-input {
  width: 100%;
  padding: 12px 16px;
  border: 2px solid #e1e5e9;
  border-radius: 8px;
  font-size: 16px;
  transition: border-color 0.3s ease;
  background: white;
  cursor: pointer;
}

.dropdown-input:focus {
  outline: none;
  border-color: #42b883;
  box-shadow: 0 0 0 3px rgba(66, 184, 131, 0.1);
}

.dropdown-input::placeholder {
  color: #666;
}

.searchable-dropdown.open .dropdown-input {
  border-color: #42b883;
  border-bottom-left-radius: 0;
  border-bottom-right-radius: 0;
}

.dropdown-list {
  position: absolute;
  top: 100%;
  left: 0;
  right: 0;
  background: white;
  border: 2px solid #42b883;
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
  background: rgba(66, 184, 131, 0.1);
}

.dropdown-item.selected {
  background: rgba(66, 184, 131, 0.2);
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



.members-bulk-actions {
  display: flex;
  align-items: center;
  gap: 15px;
  margin-bottom: 20px;
  padding: 15px 20px;
  background: rgba(107, 79, 63, 0.05);
  border: 1px solid rgba(107, 79, 63, 0.1);
  border-radius: 12px;
  flex-wrap: wrap;
}

.selected-count {
  color: var(--text-primary);
  font-weight: 600;
  font-size: 14px;
}

.bulk-delete-btn {
  background: linear-gradient(135deg, var(--error), var(--error));
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 8px;
}

.bulk-delete-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(229, 57, 53, 0.3);
}

.clear-selection-btn {
  background: #f8f9fa;
  color: var(--text-secondary);
  border: 2px solid #e9ecef;
  padding: 8px 16px;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 500;
  transition: all 0.3s ease;
}

.clear-selection-btn:hover {
  background: #e9ecef;
  color: var(--text-primary);
}

.members-controls-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
  flex-wrap: wrap;
  gap: 15px;
}

.selection-controls {
  display: flex;
  align-items: center;
  gap: 10px;
}

.select-all-label {
  display: flex;
  align-items: center;
  gap: 8px;
  cursor: pointer;
  font-size: 14px;
  color: var(--text-secondary);
  font-weight: 500;
  user-select: none;
}

.select-all-label:hover {
  color: var(--text-primary);
}

.select-all-checkbox {
  width: 16px;
  height: 16px;
  cursor: pointer;
}

.members-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 30px;
}

.members-header h2 {
  margin: 0;
  color: var(--text-primary);
}

.members-controls {
  display: flex;
  gap: 20px;
  margin-bottom: 20px;
  align-items: center;
  flex-wrap: wrap;
}

.search-container {
  position: relative;
  flex: 1;
  min-width: 250px;
  max-width: 400px;
}

.search-input {
  width: 100%;
  padding: 12px 40px 12px 16px;
  border: 2px solid rgba(107, 79, 63, 0.2);
  border-radius: 25px;
  font-size: 16px;
  transition: all 0.3s ease;
  background: rgba(255, 255, 255, 0.9);
}

.search-input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(107, 79, 63, 0.1);
  background: white;
}

.search-icon {
  position: absolute;
  right: 16px;
  top: 50%;
  transform: translateY(-50%);
  color: var(--text-secondary);
  font-size: 18px;
  pointer-events: none;
}

.filter-controls {
  display: flex;
  gap: 15px;
  align-items: center;
}

.sort-select, .filter-select {
  padding: 10px 16px;
  border: 2px solid rgba(107, 79, 63, 0.2);
  border-radius: 12px;
  background: rgba(255, 255, 255, 0.9);
  color: var(--text-primary);
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s ease;
  min-width: 150px;
}

.sort-select:focus, .filter-select:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(107, 79, 63, 0.1);
  background: white;
}

.members-stats {
  margin-bottom: 20px;
  padding: 12px 0;
  border-bottom: 1px solid rgba(107, 79, 63, 0.1);
}

.stats-text {
  color: var(--text-secondary);
  font-size: 14px;
  font-weight: 500;
}

.members-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
  gap: 20px;
}

.member-selection {
  flex-shrink: 0;
  padding-top: 4px;
}

.member-checkbox {
  width: 18px;
  height: 18px;
  cursor: pointer;
  accent-color: var(--primary);
}

.member-card {
  background: white;
  border: 1px solid rgba(0, 0, 0, 0.1);
  border-radius: 16px;
  padding: 24px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
  transition: all 0.3s ease;
  display: flex;
  flex-direction: column;
  gap: 16px;
  min-height: 280px;
  justify-content: space-between;
}

.member-card-content {
  display: flex;
  gap: 20px;
  align-items: flex-start;
}

.member-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 8px 25px rgba(107, 79, 63, 0.15);
  border-color: rgba(107, 79, 63, 0.2);
}

.member-avatar {
  flex-shrink: 0;
}

.avatar-img {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  object-fit: cover;
  border: 4px solid rgba(107, 79, 63, 0.1);
}

.avatar-placeholder {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 36px;
  color: white;
  border: 4px solid rgba(107, 79, 63, 0.2);
}

.member-info {
  flex: 1;
  min-width: 0;
}

.member-name {
  margin: 0 0 8px 0;
  color: var(--text-primary);
  font-size: 1.3rem;
  font-weight: 600;
  word-break: break-word;
}

.member-details {
  display: flex;
  gap: 12px;
  margin-bottom: 8px;
  flex-wrap: wrap;
}

.member-generation, .member-gender {
  font-size: 0.85rem;
  font-weight: 500;
  padding: 4px 8px;
  border-radius: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.member-generation {
  background: rgba(107, 79, 63, 0.1);
  color: var(--primary);
}

.member-gender.male {
  background: linear-gradient(135deg, var(--secondary), var(--secondary));
  color: white;
}

.member-gender.female {
  background: linear-gradient(135deg, var(--accent), var(--accent));
  color: white;
}

.member-birth {
  margin-top: 4px;
}

.member-birth small {
  color: var(--text-secondary);
  font-size: 0.8rem;
}

.member-actions {
  display: flex;
  gap: 8px;
  flex-shrink: 0;
  justify-content: center;
  width: 100%;
}

.member-actions button {
  padding: 8px 12px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 6px;
}

.edit-btn {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
}

.edit-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.3);
}

.delete-btn {
  background: #f8f9fa;
  color: var(--text-secondary);
  border: 2px solid #e9ecef;
}

.delete-btn:hover {
  background: #e9ecef;
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.view-btn {
  background: linear-gradient(135deg, var(--primary), var(--accent));
  color: white;
}

.view-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.3);
}

.convert-btn {
  background: linear-gradient(135deg, var(--secondary), var(--primary));
  color: white;
}

.convert-btn:hover {
  transform: translateY(-2px);
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.3);
}

.user-badge {
  background: linear-gradient(135deg, var(--success), var(--success));
  color: white;
  cursor: default;
}

.user-badge:hover {
  transform: none;
  box-shadow: 0 2px 8px rgba(76, 175, 80, 0.3);
}

.btn-icon {
  font-size: 16px;
}

.btn-text {
  display: inline;
}

.no-members {
  text-align: center;
  padding: 60px 20px;
  color: var(--text-secondary);
}

.no-members .empty-icon {
  font-size: 4rem;
  margin-bottom: 20px;
  opacity: 0.5;
}

.no-members h3 {
  margin: 0 0 10px 0;
  color: var(--text-primary);
  font-size: 1.5rem;
}

.no-members p {
  margin: 0;
  font-size: 1rem;
}

/* Desktop specific styles */
@media (min-width: 1024px) {
  .admin-panel-content {
    padding: 40px;
  }

  .panel-header h1 {
    font-size: 3rem;
  }

  .panel-header p {
    font-size: 1.2rem;
  }

  .tabs {
    max-width: 600px;
    margin: 0 auto 40px;
  }

  .requests-section, .members-section {
    padding: 40px;
  }

  .members-grid {
    grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
    gap: 24px;
  }

  .member-card {
    padding: 28px;
  }

  .modal-content {
    max-width: 700px;
    padding: 40px;
  }
}

/* Tablet styles */
@media (min-width: 768px) and (max-width: 1023px) {
  .admin-panel-content {
    padding: 25px;
  }

  .panel-header h1 {
    font-size: 2.2rem;
  }

  .tabs {
    margin-bottom: 25px;
  }

  .requests-section, .members-section {
    padding: 25px;
  }

  .modal-content {
    max-width: 500px;
    padding: 25px;
  }
}

/* Mobile styles */
@media (max-width: 767px) {
  .admin-panel-page {
    padding-top: 60px; /* Account for fixed nav */
  }

  .admin-panel-content {
    padding: 15px;
  }

  .panel-header {
    margin-bottom: 20px;
  }

  .panel-header h1 {
    font-size: 2rem;
  }

  .panel-header p {
    font-size: 1rem;
  }

  .tabs {
    flex-direction: column;
    margin-bottom: 20px;
  }

  .tabs button {
    padding: 12px;
    font-size: 15px;
  }

  .requests-section, .members-section {
    padding: 20px;
    border-radius: 12px;
  }

  .request-card, .member-card {
    padding: 15px;
  }

  .actions {
    flex-direction: column;
    gap: 8px;
  }

  .add-btn {
    padding: 12px 24px;
    font-size: 15px;
  }

  .modal-content {
    width: 95%;
    padding: 20px;
    max-height: 90vh;
  }

  .modal-content h3 {
    font-size: 1.5rem;
  }

  .form-group input,
  .form-group select,
  .form-group textarea {
    padding: 10px 12px;
    font-size: 16px; /* Prevent zoom on iOS */
  }

  .form-actions {
    flex-direction: column;
    gap: 10px;
  }

  .form-actions button {
    width: 100%;
    padding: 14px;
  }
}
</style>
