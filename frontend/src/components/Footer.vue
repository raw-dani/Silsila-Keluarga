<template>
  <footer class="app-footer">
    <div class="footer-container">
      <div class="footer-content">
        <div class="brand-section">
          <h3 class="brand-name">Silsila Keluarga</h3>
          <p class="brand-subtitle">Digital Family Tree Management</p>
        </div>

        <div class="developer-section">
          <div class="developer-info">
            <span class="developer-label">Crafted by</span>
            <span class="developer-name">Rohmat Ali Wardani</span>
          </div>
          <a
            href="https://www.linkedin.com/in/rohmat-ali-wardani/"
            target="_blank"
            rel="noopener noreferrer"
            class="linkedin-link"
            aria-label="Connect with Rohmat Ali Wardani on LinkedIn"
          >
            <svg class="linkedin-icon" viewBox="0 0 24 24" fill="currentColor">
              <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
            </svg>
          </a>
        </div>
      </div>

      <div class="footer-bottom">
        <div class="footer-decoration">
          <div class="decoration-line"></div>
        </div>
        <p class="copyright">&copy; 2025 Silsila Keluarga. All rights reserved.</p>
      </div>
    </div>
  </footer>
</template>

<script setup>
import { computed } from 'vue'

const isAdmin = computed(() => {
  return localStorage.getItem('role') === 'admin'
})
</script>

<style scoped>
.app-footer {
  background: linear-gradient(135deg, var(--bg-card) 0%, var(--bg-main) 100%);
  border-top: 1px solid rgba(107, 79, 63, 0.1);
  margin-top: auto;
  flex-shrink: 0;
  position: relative;
}

.app-footer::before {
  content: '';
  position: absolute;
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  width: 100px;
  height: 1px;
  background: linear-gradient(90deg, transparent, var(--primary), transparent);
}

.footer-container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.footer-content {
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  padding: 48px 0 32px;
  gap: 24px;
}

.brand-section {
  margin-bottom: 16px;
}

.brand-name {
  font-size: 1.5rem;
  font-weight: 700;
  color: var(--text-primary);
  margin: 0 0 4px 0;
  letter-spacing: -0.02em;
}

.brand-subtitle {
  font-size: 0.875rem;
  color: var(--text-secondary);
  margin: 0;
  font-weight: 500;
}

.developer-section {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 16px;
}

.developer-info {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 4px;
}

.developer-label {
  font-size: 0.75rem;
  color: var(--text-muted);
  font-weight: 500;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.developer-name {
  font-size: 0.95rem;
  color: var(--text-secondary);
  font-weight: 600;
}

.linkedin-link {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 40px;
  height: 40px;
  background: #0077b5;
  color: white;
  border-radius: 10px;
  text-decoration: none;
  transition: all 0.3s ease;
  box-shadow: 0 2px 8px rgba(0, 119, 181, 0.2);
}

.linkedin-link:hover {
  background: #005885;
  transform: translateY(-2px);
  box-shadow: 0 4px 16px rgba(0, 119, 181, 0.3);
}

.linkedin-icon {
  width: 20px;
  height: 20px;
}

.footer-bottom {
  border-top: 1px solid rgba(107, 79, 63, 0.1);
  padding: 24px 0;
  margin-top: 24px;
}

.footer-decoration {
  display: flex;
  justify-content: center;
  margin-bottom: 16px;
}

.decoration-line {
  width: 60px;
  height: 2px;
  background: linear-gradient(90deg, var(--primary), var(--secondary));
  border-radius: 1px;
}

.copyright {
  margin: 0;
  font-size: 0.8rem;
  color: var(--text-secondary);
  font-weight: 500;
  text-align: center;
}

/* Tablet and Desktop */
@media (min-width: 768px) {
  .footer-container {
    padding: 0 40px;
  }

  .footer-content {
    flex-direction: row;
    justify-content: space-between;
    text-align: left;
    padding: 64px 0 40px;
    gap: 0;
  }

  .brand-section {
    margin-bottom: 0;
    text-align: left;
  }

  .brand-name {
    font-size: 1.75rem;
  }

  .brand-subtitle {
    font-size: 1rem;
  }

  .developer-section {
    align-items: flex-end;
  }

  .developer-info {
    align-items: flex-end;
    text-align: right;
  }

  .footer-bottom {
    padding: 32px 0;
    margin-top: 32px;
  }

  .decoration-line {
    width: 80px;
  }

  .copyright {
    font-size: 0.875rem;
  }
}

/* Large Desktop */
@media (min-width: 1024px) {
  .brand-name {
    font-size: 2rem;
  }

  .footer-content {
    padding: 80px 0 48px;
  }

  .linkedin-link {
    width: 44px;
    height: 44px;
  }

  .linkedin-icon {
    width: 22px;
    height: 22px;
  }
}
</style>
