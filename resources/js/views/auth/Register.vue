<template>

<header class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 px-6 lg:px-10 py-4 bg-white/5 backdrop-blur-md sticky top-0 z-50">
<div class="flex items-center gap-3">
<div class="size-8 bg-primary rounded flex items-center justify-center text-white">
<span class="material-symbols-outlined text-2xl">radar</span>
</div>
<h2 class="text-slate-900 dark:text-white text-lg font-bold leading-tight tracking-tight">OSINT Intel Platform</h2>
</div>
<div class="flex items-center gap-6">
<nav class="hidden md:flex items-center gap-8">
<a class="text-slate-600 dark:text-slate-300 text-sm font-medium hover:text-primary transition-colors" href="#">Platform</a>
<a class="text-slate-600 dark:text-slate-300 text-sm font-medium hover:text-primary transition-colors" href="#">Data Sources</a>
<a class="text-slate-600 dark:text-slate-300 text-sm font-medium hover:text-primary transition-colors" href="#">Pricing</a>
</nav>
<button class="bg-primary hover:bg-blue-700 text-white text-sm font-bold py-2 px-6 rounded-lg transition-all">
                Sign In
            </button>
</div>
</header>
<main class="flex-1 flex items-center justify-center p-6 relative overflow-hidden">
<!-- Abstract tactical background element -->
<div class="absolute inset-0 z-0 opacity-20 pointer-events-none">
<div class="absolute top-0 left-0 w-full h-full bg-[radial-gradient(circle_at_center,_var(--tw-gradient-stops))] from-primary/20 via-transparent to-transparent"></div>
</div>
<div class="w-full max-w-[520px] bg-white dark:bg-[#1c2331] border border-slate-200 dark:border-slate-800 rounded-xl shadow-2xl p-8 lg:p-10 z-10">
<div class="mb-8">
<h1 class="text-3xl font-black text-slate-900 dark:text-white mb-2 tracking-tight">Join the Network</h1>
<p class="text-slate-500 dark:text-slate-400 text-base">Access advanced conflict tracking and geospatial intelligence tools.</p>
</div>
<form class="space-y-5">
<div class="space-y-2">
<label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Full Name</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xl">person</span>
<input class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-[#101622] border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-slate-900 dark:text-white placeholder:text-slate-500" placeholder="John Doe" type="text"/>
</div>
</div>
<div class="space-y-2">
<label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Email Address</label>
<div class="relative">
<span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xl">mail</span>
<input class="w-full pl-12 pr-4 py-3 bg-slate-50 dark:bg-[#101622] border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-slate-900 dark:text-white placeholder:text-slate-500" placeholder="name@organization.gov" type="email"/>
</div>
</div>
<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
<div class="space-y-2">
<label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Password</label>
<div class="relative">
<input class="w-full px-4 py-3 bg-slate-50 dark:bg-[#101622] border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-slate-900 dark:text-white" placeholder="••••••••" type="password"/>
<span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 cursor-pointer">visibility</span>
</div>
</div>
<div class="space-y-2">
<label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">Confirm Password</label>
<div class="relative">
<input class="w-full px-4 py-3 bg-slate-50 dark:bg-[#101622] border border-slate-200 dark:border-slate-700 rounded-lg focus:ring-2 focus:ring-primary focus:border-transparent outline-none text-slate-900 dark:text-white" placeholder="••••••••" type="password"/>
</div>
</div>
</div>
<div class="space-y-2">
<div class="flex justify-between items-center mb-1">
<span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Password Strength</span>
<span class="text-xs font-bold text-emerald-500 uppercase tracking-wider">Strong</span>
</div>
<div class="flex gap-1 h-1.5">
<div class="flex-1 bg-emerald-500 rounded-full"></div>
<div class="flex-1 bg-emerald-500 rounded-full"></div>
<div class="flex-1 bg-emerald-500 rounded-full"></div>
<div class="flex-1 bg-slate-200 dark:bg-slate-700 rounded-full"></div>
</div>
<p class="text-[11px] text-slate-500 dark:text-slate-400 italic">Include a special character and a number.</p>
</div>
<div class="flex items-start gap-3 py-2">
<input class="mt-1 rounded border-slate-300 dark:border-slate-700 bg-slate-50 dark:bg-[#101622] text-primary focus:ring-primary" id="terms" type="checkbox"/>
<label class="text-sm text-slate-600 dark:text-slate-400" for="terms">
                        I agree to the <a class="text-primary hover:underline" href="#">Terms of Service</a> and <a class="text-primary hover:underline" href="#">Privacy Policy</a>
</label>
</div>
<button class="w-full bg-primary hover:bg-blue-700 text-white font-bold py-4 rounded-lg shadow-lg shadow-primary/20 transition-all transform active:scale-[0.98]" type="submit">
                    Create Intelligence Account
                </button>
</form>
<div class="mt-8 pt-6 border-t border-slate-100 dark:border-slate-800 text-center">
<p class="text-slate-600 dark:text-slate-400 text-sm">
                    Already have an account? <a class="text-primary font-bold hover:underline" href="#">Log In</a>
</p>
</div>
</div>
</main>
<footer class="p-6 text-center">
<div class="flex justify-center items-center gap-4 text-slate-400 text-xs mb-2">
<div class="flex items-center gap-1">
<span class="material-symbols-outlined text-sm">verified_user</span>
<span>End-to-End Encrypted</span>
</div>
<div class="flex items-center gap-1">
<span class="material-symbols-outlined text-sm">public</span>
<span>Global Compliance</span>
</div>
</div>
<p class="text-slate-500 dark:text-slate-500 text-[10px] uppercase tracking-widest font-bold">
            © 2024 OSINT Intel Platform • Classified Access Only
        </p>
</footer>

</template>

<script setup lang="ts">
// @ts-nocheck
import { reactive, ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = reactive({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  acceptTerms: false
});

const loading = ref(false);
const error = ref('');

const handleRegister = async () => {
  error.value = '';

  // Validate password match
  if (form.password !== form.password_confirmation) {
    error.value = 'Passwords do not match';
    return;
  }

  // Validate password length
  if (form.password.length < 8) {
    error.value = 'Password must be at least 8 characters';
    return;
  }

  loading.value = true;

  try {
    await authStore.register({
      name: form.name,
      email: form.email,
      password: form.password,
      password_confirmation: form.password_confirmation
    });
    router.push('/dashboard');
  } catch (err: any) {
    console.error('Registration failed:', err);
    error.value = err?.message || 'Registration failed. Please try again.';
  } finally {
    loading.value = false;
  }
};
</script>