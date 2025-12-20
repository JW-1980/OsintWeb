import { defineStore } from 'pinia';
import { ref, computed } from 'vue';
import { User } from '@/types';
import { useApi } from '@/composables/useApi';

export const useAuthStore = defineStore('auth', () => {
  const user = ref<User | null>(null);
  const token = ref<string | null>(localStorage.getItem('auth_token'));
  const api = useApi();

  const isAuthenticated = computed(() => !!user.value && !!token.value);
  const isAdmin = computed(() => user.value?.role === 'admin');
  const isAnalyst = computed(() => user.value?.role === 'admin' || user.value?.role === 'analyst');

  async function login(email: string, password: string): Promise<void> {
    try {
      const response = await api.post<{ user: User; token: string }>('/auth/login', {
        email,
        password
      });

      user.value = response.user;
      token.value = response.token;
      localStorage.setItem('auth_token', response.token);
    } catch (error) {
      throw error;
    }
  }

  async function register(data: {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    organization?: string;
  }): Promise<void> {
    try {
      const response = await api.post<{ user: User; token: string }>('/auth/register', data);

      user.value = response.user;
      token.value = response.token;
      localStorage.setItem('auth_token', response.token);
    } catch (error) {
      throw error;
    }
  }

  async function logout(): Promise<void> {
    try {
      await api.post('/auth/logout');
    } finally {
      user.value = null;
      token.value = null;
      localStorage.removeItem('auth_token');
    }
  }

  async function fetchUser(): Promise<void> {
    if (!token.value) {
      return;
    }

    try {
      const response = await api.get<User>('/auth/user');
      user.value = response;
    } catch (error) {
      user.value = null;
      token.value = null;
      localStorage.removeItem('auth_token');
      throw error;
    }
  }

  async function updateProfile(data: Partial<User>): Promise<void> {
    try {
      const response = await api.put<User>('/auth/profile', data);
      user.value = response;
    } catch (error) {
      throw error;
    }
  }

  return {
    user,
    token,
    isAuthenticated,
    isAdmin,
    isAnalyst,
    login,
    register,
    logout,
    fetchUser,
    updateProfile
  };
});
