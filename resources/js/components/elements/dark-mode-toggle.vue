<template>
  <div class="dark-mode-toggle-container">
    <button
      @click="toggleDarkMode"
      class="dark-mode-toggle"
      :title="$store.state.darkMode ? $t('Switch to light mode') : $t('Switch to dark mode')"
      :class="{ 'is-dark': $store.state.darkMode }"
    >
      <div class="toggle-track">
        <div class="toggle-indicator">
          <svg-vue v-if="$store.state.darkMode" class="h-4 w-4 icon-light" icon="font-awesome.sun-regular"></svg-vue>
          <svg-vue v-else class="h-4 w-4 icon-dark" icon="font-awesome.moon-regular"></svg-vue>
        </div>
      </div>
    </button>
  </div>
</template>

<script>
export default {
  name: "DarkModeToggle",
  methods: {
    toggleDarkMode() {
      this.$store.commit('toggleDarkMode');
    }
  }
}
</script>

<style scoped>
.dark-mode-toggle-container {
  display: inline-flex;
  align-items: center;
  margin: 0 0.5rem;
}

.dark-mode-toggle {
  position: relative;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0;
  background-color: transparent;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
  outline: none;
}

.toggle-track {
  width: 3rem;
  height: 1.5rem;
  background-color: #e2e8f0;
  border-radius: 9999px;
  position: relative;
  transition: all 0.3s ease;
  border: 1px solid #cbd5e0;
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
}

.is-dark .toggle-track {
  background-color: #4a5568;
  border-color: #2d3748;
}

.toggle-indicator {
  position: absolute;
  height: 1.25rem;
  width: 1.25rem;
  left: 0.125rem;
  top: 0.125rem;
  background-color: white;
  border-radius: 50%;
  transition: all 0.3s cubic-bezier(0.4, 0.0, 0.2, 1);
  display: flex;
  align-items: center;
  justify-content: center;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

.is-dark .toggle-indicator {
  transform: translateX(1.5rem);
  background-color: #18A5A7; /* Primary brand color */
}

.icon-light {
  color: #f6ad55; /* Orange/yellow for sun */
}

.icon-dark {
  color: #4a5568; /* Dark blue/gray for moon */
}

.dark-mode-toggle:focus .toggle-track {
  box-shadow: 0 0 0 2px rgba(24, 165, 167, 0.5);
}

.dark-mode-toggle:hover .toggle-indicator {
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
}
</style>
