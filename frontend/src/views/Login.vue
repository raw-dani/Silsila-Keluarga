<template>
  <div class="login-container">
    <h2>Login ke Aplikasi Silsila Keluarga</h2>
    <form @submit.prevent="login">
      <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" v-model="email" required>
      </div>
      <div class="form-group">
        <label for="password">Password:</label>
        <input type="password" id="password" v-model="password" required>
      </div>
      <button type="submit" :disabled="loading">
        {{ loading ? 'Logging in...' : 'Login' }}
      </button>
    </form>
    <p v-if="error" class="error">{{ error }}</p>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../services/api'

const router = useRouter()
const email = ref('')
const password = ref('')
const loading = ref(false)
const error = ref('')

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
</script>

<style scoped>
.login-container {
  max-width: 450px;
  margin: 100px auto;
  padding: 40px;
  background: var(--bg-card);
  backdrop-filter: blur(10px);
  border-radius: 16px;
  box-shadow: 0 20px 60px rgba(107, 79, 63, 0.1);
  text-align: center;
  position: relative;
  overflow: hidden;
  border: 1px solid rgba(217, 177, 130, 0.2);
}

.login-container::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
}

.login-container h2 {
  color: #333;
  margin-bottom: 30px;
  font-size: 2rem;
  font-weight: 600;
}

.form-group {
  margin-bottom: 20px;
  text-align: left;
}

label {
  display: block;
  margin-bottom: 8px;
  font-weight: 600;
  color: #555;
  font-size: 14px;
}

input {
  width: 100%;
  padding: 15px 16px;
  border: 2px solid #e1e5e9;
  border-radius: 8px;
  box-sizing: border-box;
  font-size: 16px;
  transition: border-color 0.3s ease;
}

input:focus {
  outline: none;
  border-color: var(--primary);
  box-shadow: 0 0 0 3px rgba(107, 79, 63, 0.1);
}

button {
  width: 100%;
  padding: 15px;
  background: linear-gradient(135deg, var(--primary), var(--secondary));
  color: white;
  border: none;
  border-radius: 25px;
  cursor: pointer;
  font-size: 16px;
  font-weight: 600;
  transition: all 0.3s ease;
  box-shadow: 0 4px 15px rgba(107, 79, 63, 0.3);
}

button:hover:not(:disabled) {
  transform: translateY(-2px);
  box-shadow: 0 6px 20px rgba(107, 79, 63, 0.4);
}

button:disabled {
  background: #ccc;
  box-shadow: none;
  transform: none;
  cursor: not-allowed;
}

.error {
  color: #f44336;
  margin-top: 15px;
  font-weight: 500;
  background: #ffebee;
  padding: 10px;
  border-radius: 6px;
  border-left: 4px solid #f44336;
}

/* Desktop styles */
@media (min-width: 1024px) {
  .login-container {
    margin: 150px auto;
    padding: 50px;
  }

  .login-container h2 {
    font-size: 2.5rem;
  }
}

/* Mobile styles */
@media (max-width: 767px) {
  .login-container {
    margin: 50px 20px;
    padding: 30px 20px;
  }

  .login-container h2 {
    font-size: 1.8rem;
  }

  input {
    font-size: 16px; /* Prevent zoom on iOS */
  }
}
</style>
