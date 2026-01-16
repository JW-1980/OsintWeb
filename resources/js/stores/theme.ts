import { defineStore } from 'pinia';
import { ref, watch } from 'vue';

export const useThemeStore = defineStore('theme', () => {
  const isDark = ref(false);

  // Initialize from localStorage or system preference
  const initTheme = () => {
    const stored = localStorage.getItem('theme');
    if (stored) {
      isDark.value = stored === 'dark';
    } else {
      isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    applyTheme();
  };

  const applyTheme = () => {
    if (isDark.value) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  };

  const toggleTheme = () => {
    isDark.value = !isDark.value;
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
    applyTheme();
  };

  const setTheme = (dark: boolean) => {
    isDark.value = dark;
    localStorage.setItem('theme', dark ? 'dark' : 'light');
    applyTheme();
  };

  // Watch for changes
  watch(isDark, applyTheme);

  return {
    isDark,
    initTheme,
    toggleTheme,
    setTheme
  };
});
