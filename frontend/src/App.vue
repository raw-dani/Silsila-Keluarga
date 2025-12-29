<template>
  <div id="app">
    <router-view />
    <Footer />
  </div>
</template>

<script>
import Footer from './components/Footer.vue'

export default {
  name: 'App',
  components: {
    Footer
  }
}
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

#app {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: var(--text-primary);
  min-height: 100vh;
  background: #ffffff;
  line-height: 1.6;
  font-weight: 400;
  display: flex;
  flex-direction: column;
}

/* Desktop optimizations */
@media (min-width: 1024px) {
  #app {
    font-size: 16px;
    background-attachment: fixed;
  }

  /* Improve scrollbar for desktop */
  ::-webkit-scrollbar {
    width: 8px;
  }

  ::-webkit-scrollbar-track {
    background: rgba(255, 255, 255, 0.1);
  }

  ::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.3);
    border-radius: 4px;
  }

  ::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.5);
  }
}

/* Tablet optimizations */
@media (min-width: 768px) and (max-width: 1023px) {
  #app {
    font-size: 15px;
  }
}

/* Mobile optimizations */
@media (max-width: 767px) {
  #app {
    font-size: 14px;
    background-attachment: scroll;
  }
}

/* Global improvements for desktop experience */
@media (min-width: 1024px) {
  /* Better focus states for desktop users */
  *:focus {
    outline: 2px solid #42b883;
    outline-offset: 2px;
  }

  /* Improve button interactions */
  button {
    transition: all 0.2s ease;
  }

  /* Better link styles */
  a {
    transition: color 0.2s ease;
  }
}

/* High contrast mode support */
@media (prefers-contrast: high) {
  #app {
    background: #ffffff;
    color: #000000;
  }
}

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
  }
}
</style>
