<template>
  <nav class="navbar">
    <div class="nav-container">
      <div class="nav-brand">
        <router-link to="/dashboard" class="brand-link">
          <span class="brand-icon">👨‍👩‍👧‍👦</span>
          <span class="brand-text">Silsila Keluarga</span>
        </router-link>
      </div>

      <div class="nav-menu" :class="{ active: isMenuOpen }">
        <router-link to="/dashboard" class="nav-link" @click="closeMenu">
          <!-- <span class="nav-icon">🏠</span> -->
          <span>Dashboard</span>
        </router-link>

        <router-link to="/family-tree" class="nav-link" @click="closeMenu">
          <!-- <span class="nav-icon">🌳</span> -->
          <span>Pohon Keluarga</span>
        </router-link>

        <router-link v-if="isMember" to="/request-update" class="nav-link" @click="closeMenu">
          <!-- <span class="nav-icon">📝</span> -->
          <span>Ajukan Perubahan</span>
        </router-link>

        <router-link to="/profile" class="nav-link" @click="closeMenu">
          <!-- <span class="nav-icon">👤</span> -->
          <span>Profile</span>
        </router-link>

        <div v-if="isAdmin" class="nav-dropdown">
          <button @click="toggleAdminMenu" class="nav-link dropdown-toggle" :class="{ active: isAdminMenuOpen }">
            <!-- <span class="nav-icon">⚙️</span> -->
            <span>Admin Panel</span>
            <span class="dropdown-arrow" :class="{ open: isAdminMenuOpen }">▼</span>
          </button>
          <div v-if="isAdminMenuOpen" class="dropdown-menu">
            <router-link to="/admin" class="dropdown-item" @click="closeMenu">
              <!-- <span class="dropdown-icon">👥</span> -->
              <span>Kelola Anggota</span>
            </router-link>
            <router-link to="/admin-management" class="dropdown-item" @click="closeMenu">
              <span class="dropdown-icon">👑</span>
              <span>Kelola Admin</span>
            </router-link>
          </div>
        </div>
      </div>

      <div class="nav-actions">
        <div class="user-info">
          <span class="user-role" :class="userRoleClass">{{ userRoleText }}</span>
          <span class="user-name">{{ userName }}</span>
        </div>
        <button @click="logout" class="logout-btn">
          <span></span>
          <span class="logout-text">Logout</span>
        </button>
      </div>

      <button class="menu-toggle" @click="toggleMenu" :class="{ active: isMenuOpen }">
        <span></span>
        <span></span>
        <span></span>
      </button>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const isMenuOpen = ref(false)
const isAdminMenuOpen = ref(false)

const userName = ref('')
const userRole = ref('')

const isAdmin = computed(() => userRole.value === 'admin')
const isMember = computed(() => userRole.value === 'member')

const userRoleText = computed(() => {
  switch (userRole.value) {
    case 'admin': return 'Admin'
    case 'member': return 'Member'
    default: return 'User'
  }
})

const userRoleClass = computed(() => {
  switch (userRole.value) {
    case 'admin': return 'role-admin'
    case 'member': return 'role-member'
    default: return 'role-user'
  }
})

onMounted(() => {
  userName.value = 'User' // In real app, get from API
  userRole.value = localStorage.getItem('role') || 'member'
})

const toggleMenu = () => {
  isMenuOpen.value = !isMenuOpen.value
}

const closeMenu = () => {
  isMenuOpen.value = false
  isAdminMenuOpen.value = false
}

const toggleAdminMenu = () => {
  isAdminMenuOpen.value = !isAdminMenuOpen.value
}

const logout = () => {
  localStorage.removeItem('token')
  localStorage.removeItem('role')
  router.push('/login')
}
</script>

<style scoped>
.navbar {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(249, 243, 237, 0.95));
  backdrop-filter: blur(20px);
  border-bottom: 1px solid rgba(107, 79, 63, 0.08);
  box-shadow: 0 4px 25px rgba(107, 79, 63, 0.08);
  position: sticky;
  top: 0;
  z-index: 1000;
  transition: all 0.3s ease;
}

.navbar:hover {
  background: linear-gradient(135deg, rgba(255, 255, 255, 0.99), rgba(249, 243, 237, 0.98));
  box-shadow: 0 6px 35px rgba(107, 79, 63, 0.12);
}

.nav-container {
  max-width: 1400px;
  margin: 0 auto;
  padding: 0 20px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  height: 75px;
}

.nav-brand .brand-link {
  display: flex;
  align-items: center;
  gap: 12px;
  text-decoration: none;
  color: var(--text-primary);
  font-weight: 700;
  font-size: 1.5rem;
  transition: all 0.3s ease;
  padding: 8px 16px;
  border-radius: 12px;
  position: relative;
  overflow: hidden;
}

.nav-brand .brand-link::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(107, 79, 63, 0.1), transparent);
  transition: left 0.5s ease;
}

.nav-brand .brand-link:hover::before {
  left: 100%;
}

.nav-brand .brand-link:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 20px rgba(107, 79, 63, 0.15);
}

.brand-icon {
  font-size: 2.2rem;
  filter: drop-shadow(0 2px 4px rgba(107, 79, 63, 0.2));
  transition: transform 0.3s ease;
}

.nav-brand .brand-link:hover .brand-icon {
  transform: scale(1.1) rotate(5deg);
}

.brand-text {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  font-weight: 800;
  letter-spacing: -0.02em;
}

.nav-menu {
  display: flex;
  gap: 30px;
  align-items: center;
}

.nav-link {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 18px;
  text-decoration: none;
  color: var(--text-secondary);
  font-weight: 600;
  font-size: 0.95rem;
  border-radius: 12px;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  overflow: hidden;
  letter-spacing: 0.02em;
}

.nav-link::before {
  content: '';
  position: absolute;
  top: 0;
  left: -100%;
  width: 100%;
  height: 100%;
  background: linear-gradient(90deg, transparent, rgba(107, 79, 63, 0.1), transparent);
  transition: left 0.4s ease;
}

.nav-link:hover::before {
  left: 100%;
}

.nav-link:hover {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: black;
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(107, 79, 63, 0.25);
}

.nav-link.router-link-active {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  box-shadow: 0 8px 25px rgba(107, 79, 63, 0.25);
  transform: translateY(-1px);
}

.nav-link.router-link-active::after {
  content: '';
  position: absolute;
  bottom: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 24px;
  height: 3px;
  background: var(--accent);
  border-radius: 2px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.nav-icon {
  font-size: 1.3rem;
  transition: transform 0.3s ease;
  filter: drop-shadow(0 1px 2px rgba(107, 79, 63, 0.2));
}

.nav-link:hover .nav-icon {
  transform: scale(1.1);
}

.nav-dropdown {
  position: relative;
}

.dropdown-toggle {
  background: none;
  border: none;
  cursor: pointer;
  display: flex;
  align-items: center;
  gap: 8px;
}

.dropdown-toggle.active {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.3);
}

.dropdown-arrow {
  font-size: 0.8rem;
  transition: transform 0.3s ease;
}

.dropdown-arrow.open {
  transform: rotate(180deg);
}

.dropdown-menu {
  position: absolute;
  top: 100%;
  left: 0;
  min-width: 200px;
  background: white;
  border-radius: 8px;
  box-shadow: 0 8px 25px rgba(107, 79, 63, 0.15);
  border: 1px solid rgba(107, 79, 63, 0.1);
  z-index: 1001;
  animation: dropdownSlideIn 0.2s ease;
}

.dropdown-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 16px;
  text-decoration: none;
  color: var(--text-secondary);
  font-weight: 500;
  transition: all 0.2s ease;
  border-bottom: 1px solid rgba(107, 79, 63, 0.05);
}

.dropdown-item:last-child {
  border-bottom: none;
}

.dropdown-item:hover {
  background: rgba(107, 79, 63, 0.05);
  color: var(--text-primary);
}

.dropdown-item.router-link-active {
  background: rgba(107, 79, 63, 0.1);
  color: var(--primary);
  font-weight: 600;
}

.dropdown-icon {
  font-size: 1.1rem;
  flex-shrink: 0;
}

@keyframes dropdownSlideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.nav-actions {
  display: flex;
  align-items: center;
  gap: 20px;
}

.user-info {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  text-align: right;
}

.user-role {
  font-size: 0.8rem;
  font-weight: 600;
  padding: 2px 8px;
  border-radius: 12px;
  text-transform: uppercase;
  letter-spacing: 0.5px;
}

.user-role.role-admin {
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
}

.user-role.role-member {
  background: linear-gradient(135deg, var(--accent), var(--secondary));
  color: white;
}

.user-name {
  font-size: 0.9rem;
  color: var(--text-primary);
  font-weight: 500;
}

.logout-btn {
  display: flex;
  align-items: center;
  gap: 8px;
  padding: 8px 16px;
  background: linear-gradient(135deg, var(--error), var(--error));
  color: white;
  border: none;
  border-radius: 20px;
  cursor: pointer;
  font-weight: 500;
  transition: all 0.3s ease;
  box-shadow: 0 2px 10px rgba(229, 57, 53, 0.3);
}

.logout-btn:hover {
  transform: translateY(-1px);
  box-shadow: 0 4px 15px rgba(229, 57, 53, 0.4);
}

.logout-text {
  display: none;
}

.menu-toggle {
  display: none;
  flex-direction: column;
  gap: 4px;
  background: none;
  border: none;
  cursor: pointer;
  padding: 8px;
}

.menu-toggle span {
  width: 25px;
  height: 3px;
  background: var(--text-primary);
  transition: all 0.3s ease;
  transform-origin: center;
}

.menu-toggle.active span:nth-child(1) {
  transform: rotate(45deg) translate(6px, 6px);
}

.menu-toggle.active span:nth-child(2) {
  opacity: 0;
}

.menu-toggle.active span:nth-child(3) {
  transform: rotate(-45deg) translate(6px, -6px);
}

/* Desktop styles */
@media (min-width: 1024px) {
  .nav-container {
    padding: 0 40px;
    max-width: 1400px;
    margin: 0 auto;
  }

  .nav-brand .brand-link {
    font-size: 1.8rem;
  }

  .nav-menu {
    gap: 40px;
    flex: 1;
    justify-content: center;
  }

  .nav-link {
    padding: 12px 20px;
    font-size: 1rem;
    border-radius: 8px;
  }

  .nav-link:hover {
    background: rgba(107, 79, 63, 0.1);
    color: black;
  }

  .nav-actions {
    display: flex;
    align-items: center;
    gap: 25px;
  }

  .user-info {
    flex-direction: row;
    align-items: center;
    gap: 10px;
    background: rgba(255, 255, 255, 0.1);
    padding: 8px 16px;
    border-radius: 20px;
    backdrop-filter: blur(10px);
  }

  .user-role {
    font-size: 0.75rem;
    padding: 4px 10px;
  }

  .user-name {
    font-size: 0.9rem;
    color: rgba(255, 255, 255, 0.9);
  }

  .logout-btn {
    padding: 10px 20px;
    font-size: 0.9rem;
  }

  .logout-btn .logout-text {
    display: inline;
  }
}

/* Tablet styles */
@media (min-width: 768px) and (max-width: 1023px) {
  .nav-container {
    padding: 0 25px;
    height: 65px;
  }

  .nav-brand .brand-link {
    font-size: 1.4rem;
  }

  .nav-menu {
    gap: 20px;
  }

  .nav-link {
    padding: 8px 12px;
    font-size: 0.9rem;
  }

  .nav-link span:last-child {
    display: none;
  }

  .logout-btn .logout-text {
    display: inline;
  }
}

/* Mobile styles */
@media (max-width: 767px) {
  .navbar {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(249, 243, 237, 0.96));
    backdrop-filter: blur(25px);
  }

  .nav-container {
    padding: 0 15px;
    height: 60px;
  }

  .nav-brand .brand-link {
    font-size: 1.2rem;
    padding: 6px 12px;
    border-radius: 8px;
  }

  .brand-icon {
    font-size: 1.8rem;
  }

  .brand-text {
    display: none;
  }

  .nav-menu {
    position: fixed;
    top: 60px;
    left: 0;
    right: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.98), rgba(249, 243, 237, 0.95));
    backdrop-filter: blur(25px);
    flex-direction: column;
    gap: 0;
    padding: 25px 0;
    box-shadow: 0 8px 35px rgba(107, 79, 63, 0.15);
    border-top: 1px solid rgba(107, 79, 63, 0.08);
    transform: translateY(-100%);
    opacity: 0;
    visibility: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
  }

  .nav-menu.active {
    transform: translateY(0);
    opacity: 1;
    visibility: visible;
  }

  .nav-link {
    width: 100%;
    padding: 16px 30px;
    justify-content: flex-start;
    border-radius: 0;
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
    transition: all 0.3s ease;
    position: relative;
  }

  .nav-link::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    height: 100%;
    width: 4px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    transform: scaleY(0);
    transition: transform 0.3s ease;
    border-radius: 0 2px 2px 0;
  }

  .nav-link:hover::before,
  .nav-link.router-link-active::before {
    transform: scaleY(1);
  }

  .nav-link:hover {
    background: rgba(107, 79, 63, 0.05);
    padding-left: 35px;
    transform: translateX(5px);
  }

  .nav-link span:last-child {
    display: inline;
  }

  .nav-icon {
    font-size: 1.4rem;
  }

  .dropdown-menu {
    position: static;
    box-shadow: none;
    border: none;
    background: rgba(107, 79, 63, 0.03);
    border-radius: 0;
    margin-top: 8px;
    border-top: 1px solid rgba(107, 79, 63, 0.08);
  }

  .dropdown-item {
    padding: 14px 50px;
    border-bottom: 1px solid rgba(107, 79, 63, 0.06);
    font-size: 0.95rem;
    transition: all 0.3s ease;
  }

  .dropdown-item:hover {
    background: rgba(107, 79, 63, 0.08);
    padding-left: 55px;
    transform: translateX(5px);
  }

  .dropdown-icon {
    font-size: 1.2rem;
  }

  .nav-actions {
    display: none;
  }

  .menu-toggle {
    display: flex;
    width: 32px;
    height: 32px;
    justify-content: center;
    align-items: center;
    border-radius: 8px;
    transition: all 0.3s ease;
  }

  .menu-toggle:hover {
    background: rgba(107, 79, 63, 0.1);
  }

  .menu-toggle span {
    width: 22px;
    height: 2px;
    background: var(--text-primary);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    transform-origin: center;
    border-radius: 1px;
  }

  .menu-toggle.active span:nth-child(1) {
    transform: rotate(45deg) translate(6px, 6px);
    width: 18px;
  }

  .menu-toggle.active span:nth-child(2) {
    opacity: 0;
    transform: translateX(10px);
  }

  .menu-toggle.active span:nth-child(3) {
    transform: rotate(-45deg) translate(6px, -6px);
    width: 18px;
  }

  .logout-btn {
    position: absolute;
    bottom: 25px;
    left: 50%;
    transform: translateX(-50%);
    padding: 12px 24px;
    font-size: 1rem;
    border-radius: 25px;
    box-shadow: 0 4px 20px rgba(229, 57, 53, 0.4);
  }

  .logout-btn .logout-text {
    display: inline;
  }
}
</style>
