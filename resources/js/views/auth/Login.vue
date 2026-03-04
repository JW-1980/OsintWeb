<template>

<!-- Background Decoration -->
<div class="fixed inset-0 tactical-grid pointer-events-none"></div>
<div class="fixed top-0 left-0 w-full h-full bg-gradient-to-b from-primary/5 via-transparent to-primary/5 pointer-events-none"></div>
<!-- Navigation -->
<header class="relative z-10 flex items-center justify-between px-6 lg:px-12 py-6">
<div class="flex items-center gap-3">
<div class="text-primary">
<svg class="w-8 h-8" fill="none" viewbox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
<path d="M42.1739 20.1739L27.8261 5.82609C29.1366 7.13663 28.3989 10.1876 26.2002 13.7654C24.8538 15.9564 22.9595 18.3449 20.6522 20.6522C18.3449 22.9595 15.9564 24.8538 13.7654 26.2002C10.1876 28.3989 7.13663 29.1366 5.82609 27.8261L20.1739 42.1739C21.4845 43.4845 24.5355 42.7467 28.1133 40.548C30.3042 39.2016 32.6927 37.3073 35 35C37.3073 32.6927 39.2016 30.3042 40.548 28.1133C42.7467 24.5355 43.4845 21.4845 42.1739 20.1739Z" fill="currentColor"></path>
</svg>
</div>
<h2 class="text-xl font-bold tracking-tight uppercase">IntelLink OSINT</h2>
</div>
<nav class="hidden md:flex items-center gap-8">
<a class="text-sm font-medium opacity-70 hover:opacity-100 transition-opacity" href="#">Platform</a>
<a class="text-sm font-medium opacity-70 hover:opacity-100 transition-opacity" href="#">Documentation</a>
<a class="text-sm font-medium opacity-70 hover:opacity-100 transition-opacity" href="#">System Status</a>
</nav>
</header>
<!-- Main Content -->
<main class="relative z-10 flex-1 flex items-center justify-center p-6">
<div class="w-full max-w-[440px]">
<!-- Login Card -->
<div class="glass-panel rounded-xl p-8 shadow-2xl">
<div class="text-center mb-10">
<h1 class="text-2xl font-bold mb-2">Secure Access Portal</h1>
<p class="text-slate-400 text-sm">Military Intelligence &amp; Conflict Analysis Grid</p>
</div>
<form class="space-y-6">
<!-- Email Field -->
<div class="space-y-2">
<label class="text-xs font-semibold uppercase tracking-wider text-slate-400 ml-1" for="email">Terminal ID / Email</label>
<div class="relative flex items-center">
<span class="material-symbols-outlined absolute left-4 text-slate-500 text-xl">alternate_email</span>
<input class="w-full bg-slate-900/50 border border-slate-700 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg py-3.5 pl-12 pr-4 text-slate-100 placeholder:text-slate-600 transition-all outline-none" id="email" placeholder="name@agency.gov" type="email"/>
</div>
</div>
<!-- Password Field -->
<div class="space-y-2">
<label class="text-xs font-semibold uppercase tracking-wider text-slate-400 ml-1" for="password">Access Key</label>
<div class="relative flex items-center">
<span class="material-symbols-outlined absolute left-4 text-slate-500 text-xl">encrypted</span>
<input class="w-full bg-slate-900/50 border border-slate-700 focus:border-primary focus:ring-1 focus:ring-primary rounded-lg py-3.5 pl-12 pr-4 text-slate-100 placeholder:text-slate-600 transition-all outline-none" id="password" placeholder="••••••••••••" type="password"/>
</div>
</div>
<!-- Options -->
<div class="flex items-center justify-between text-sm">
<label class="flex items-center gap-2 cursor-pointer group">
<input class="rounded border-slate-700 bg-slate-900 text-primary focus:ring-primary focus:ring-offset-slate-900" type="checkbox"/>
<span class="text-slate-400 group-hover:text-slate-200 transition-colors">Persistent Session</span>
</label>
<a class="text-primary hover:text-blue-400 font-medium transition-colors" href="#">Credential Recovery</a>
</div>
<!-- Sign In Button -->
<button class="w-full bg-primary hover:bg-blue-600 text-white font-bold py-4 rounded-lg shadow-lg shadow-primary/20 transition-all active:scale-[0.98] flex items-center justify-center gap-2 uppercase tracking-widest text-sm" type="submit">
                        Initialize Connection
                        <span class="material-symbols-outlined text-lg">login</span>
</button>
</form>
<div class="mt-8 pt-8 border-t border-slate-800 text-center">
<p class="text-slate-400 text-sm">
                        Unauthorized access is monitored.
                        <a class="text-primary font-semibold hover:underline ml-1" href="#">Request Clearances</a>
</p>
</div>
</div>
<!-- Global Footer Info -->
<div class="mt-8 flex flex-col items-center gap-4 text-[10px] uppercase tracking-[0.2em] text-slate-600">
<div class="flex items-center gap-6">
<div class="flex items-center gap-1">
<span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                        Encrypted: AES-256
                    </div>
<div>Lat: 38.8977° N, Lon: 77.0365° W</div>
</div>
<p>© 2024 IntelLink OSINT Systems. Classified Environment.</p>
</div>
</div>
</main>
<!-- Side Map Preview (Aesthetic Element) -->
<div class="hidden xl:block fixed bottom-12 right-12 w-64 h-64 opacity-20 pointer-events-none">
<div class="relative w-full h-full border border-primary/30 rounded-full flex items-center justify-center overflow-hidden">
<div class="absolute inset-0 bg-primary/5 rounded-full border border-primary/10 scale-75"></div>
<div class="absolute inset-0 bg-primary/5 rounded-full border border-primary/10 scale-50"></div>
<div class="absolute w-full h-px bg-primary/40 rotate-45"></div>
<div class="absolute w-full h-px bg-primary/40 -rotate-45"></div>
<div class="absolute w-px h-full bg-primary/40"></div>
<div class="absolute h-px w-full bg-primary/40"></div>
<span class="material-symbols-outlined text-primary text-4xl animate-pulse">radar</span>
</div>
</div>

</template>

<script setup lang="ts">
// @ts-nocheck
import { reactive, ref, nextTick } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const form = reactive({
  email: '',
  password: '',
  remember: false
});

const loading = ref(false);
const error = ref('');

const handleLogin = async () => {
  error.value = '';
  loading.value = true;

  try {
    await authStore.login(form.email, form.password);

    // Wait for state to update
    await nextTick();

    // Redirect to intended destination or dashboard
    const redirect = route.query.redirect as string;
    await router.replace(redirect || '/dashboard');
  } catch (err: any) {
    console.error('Login failed:', err);
    error.value = err?.message || 'Login failed. Please check your credentials.';
  } finally {
    loading.value = false;
  }
};
</script>