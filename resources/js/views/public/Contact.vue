<template>
健康

<meta charset="utf-8"/>
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&amp;family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap" rel="stylesheet"/>
<script id="tailwind-config">
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            "primary": "#135bec",
            "background-light": "#f6f6f8",
            "background-dark": "#101622",
          },
          fontFamily: {
            "display": ["Inter"]
          },
          borderRadius: {"DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px"},
        },
      },
    }
  </script>
<style>
    body {
      font-family: 'Inter', sans-serif;
    }
    .material-symbols-outlined {
      font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
    }
  </style>
<div class="relative flex h-auto min-h-screen w-full flex-col bg-background-light dark:bg-background-dark overflow-x-hidden">
<div class="layout-container flex h-full grow flex-col">
<!-- Top Navigation Bar -->
<header class="flex items-center justify-between whitespace-nowrap border-b border-solid border-slate-200 dark:border-slate-800 px-6 lg:px-20 py-4 bg-background-light dark:bg-background-dark">
<div class="flex items-center gap-8">
<div class="flex items-center gap-3 text-primary">
<div class="size-6">
<svg fill="none" viewbox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
<path clip-rule="evenodd" d="M39.475 21.6262C40.358 21.4363 40.6863 21.5589 40.7581 21.5934C40.7876 21.655 40.8547 21.857 40.8082 22.3336C40.7408 23.0255 40.4502 24.0046 39.8572 25.2301C38.6799 27.6631 36.5085 30.6631 33.5858 33.5858C30.6631 36.5085 27.6632 38.6799 25.2301 39.8572C24.0046 40.4502 23.0255 40.7407 22.3336 40.8082C21.8571 40.8547 21.6551 40.7875 21.5934 40.7581C21.5589 40.6863 21.4363 40.358 21.6262 39.475C21.8562 38.4054 22.4689 36.9657 23.5038 35.2817C24.7575 33.2417 26.5497 30.9744 28.7621 28.762C30.9744 26.5497 33.2417 24.7574 35.2817 23.5037C36.9657 22.4689 38.4054 21.8562 39.475 21.6262ZM4.41189 29.2403L18.7597 43.5881C19.8813 44.7097 21.4027 44.9179 22.7217 44.7893C24.0585 44.659 25.5148 44.1631 26.9723 43.4579C29.9052 42.0387 33.2618 39.5667 36.4142 36.4142C39.5667 33.2618 42.0387 29.9052 43.4579 26.9723C44.1631 25.5148 44.659 24.0585 44.7893 22.7217C44.9179 21.4027 44.7097 19.8813 43.5881 18.7597L29.2403 4.41187C27.8527 3.02428 25.8765 3.02573 24.2861 3.36776C22.6081 3.72863 20.7334 4.58419 18.8396 5.74801C16.4978 7.18716 13.9881 9.18353 11.5858 11.5858C9.18354 13.988 7.18717 16.4978 5.74802 18.8396C4.58421 20.7334 3.72865 22.6081 3.36778 24.2861C3.02574 25.8765 3.02429 27.8527 4.41189 29.2403Z" fill="currentColor" fill-rule="evenodd"></path>
</svg>
</div>
<h2 class="text-slate-900 dark:text-slate-100 text-xl font-bold leading-tight tracking-tight">OSINT Intel</h2>
</div>
<nav class="hidden md:flex items-center gap-6">
<a class="text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary text-sm font-medium transition-colors" href="#">Dashboard</a>
<a class="text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary text-sm font-medium transition-colors" href="#">Reports</a>
<a class="text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary text-sm font-medium transition-colors" href="#">Tracking</a>
<a class="text-primary text-sm font-semibold" href="#">Contact</a>
</nav>
</div>
<div class="flex items-center gap-4">
<label class="hidden sm:flex items-center relative">
<span class="absolute left-3 text-slate-400 material-symbols-outlined">search</span>
<input class="form-input w-64 pl-10 h-10 rounded-lg border-none bg-slate-100 dark:bg-slate-800 text-slate-900 dark:text-slate-100 placeholder:text-slate-500 focus:ring-2 focus:ring-primary transition-all" placeholder="Search Intel Database"/>
</label>
<button class="bg-primary text-white px-5 py-2 rounded-lg font-bold text-sm hover:bg-primary/90 transition-colors">Sign In</button>
</div>
</header>
<!-- Main Content Area -->
<main class="flex-1 max-w-7xl mx-auto w-full px-6 lg:px-20 py-12">
<!-- Header Section -->
<div class="mb-12">
<h1 class="text-slate-900 dark:text-slate-100 text-4xl lg:text-5xl font-black mb-4 tracking-tight">Get in Touch</h1>
<p class="text-slate-600 dark:text-slate-400 text-lg max-w-2xl leading-relaxed">
            For operational inquiries, intelligence reporting, media requests, or specialized conflict tracking data, please reach out to our global analysis team. We maintain strict confidentiality in all communications.
          </p>
</div>
<div class="grid grid-cols-1 lg:grid-cols-12 gap-12 mb-20">
<!-- Contact Form Column -->
<div class="lg:col-span-7 bg-white dark:bg-slate-900/50 p-8 rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm">
<form action="#" class="space-y-6" method="POST">
<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
<div class="space-y-2">
<label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Full Name</label>
<input class="w-full h-12 rounded-lg bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:border-primary focus:ring-primary" placeholder="John Doe" type="text"/>
</div>
<div class="space-y-2">
<label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Email Address</label>
<input class="w-full h-12 rounded-lg bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:border-primary focus:ring-primary" placeholder="secure@protonmail.com" type="email"/>
</div>
</div>
<div class="space-y-2">
<label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Subject</label>
<input class="w-full h-12 rounded-lg bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:border-primary focus:ring-primary" placeholder="Inquiry regarding conflict tracking" type="text"/>
</div>
<div class="space-y-2">
<label class="text-sm font-semibold text-slate-700 dark:text-slate-300">Message</label>
<textarea class="w-full rounded-lg bg-slate-50 dark:bg-slate-800 border-slate-200 dark:border-slate-700 text-slate-900 dark:text-slate-100 focus:border-primary focus:ring-primary resize-none" placeholder="Type your secure message here..." rows="6"></textarea>
</div>
<button class="w-full bg-primary hover:bg-primary/90 text-white font-bold py-4 rounded-lg transition-all flex items-center justify-center gap-2 group" type="submit">
                Submit Intelligence Request
                <span class="material-symbols-outlined group-hover:translate-x-1 transition-transform">send</span>
</button>
</form>
</div>
<!-- Alternative Contact Info Column -->
<div class="lg:col-span-5 flex flex-col gap-8">
<div class="bg-primary/10 dark:bg-primary/5 border border-primary/20 p-8 rounded-xl">
<h3 class="text-xl font-bold mb-6 flex items-center gap-2">
<span class="material-symbols-outlined text-primary">security</span>
                Secure Communication
              </h3>
<div class="space-y-6">
<div class="flex items-start gap-4">
<div class="bg-primary text-white p-2 rounded-lg">
<span class="material-symbols-outlined">mail</span>
</div>
<div>
<p class="text-sm font-semibold text-slate-500 uppercase tracking-wider">Proton Mail</p>
<p class="text-lg font-medium">intel-ops@protonmail.com</p>
</div>
</div>
<div class="flex items-start gap-4">
<div class="bg-primary text-white p-2 rounded-lg">
<span class="material-symbols-outlined">encrypted</span>
</div>
<div>
<p class="text-sm font-semibold text-slate-500 uppercase tracking-wider">PGP Fingerprint</p>
<p class="font-mono text-sm break-all text-slate-400">4D6F 7265 2049 6E74 656C 2044 6174 6121</p>
</div>
</div>
</div>
</div>
<div class="bg-white dark:bg-slate-900/50 border border-slate-200 dark:border-slate-800 p-8 rounded-xl shadow-sm">
<h3 class="text-xl font-bold mb-6">Digital Presence</h3>
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
<a class="flex flex-col items-center justify-center p-4 rounded-lg bg-slate-50 dark:bg-slate-800 hover:bg-primary/10 transition-colors group" href="#">
<span class="material-symbols-outlined text-3xl mb-2 text-slate-400 group-hover:text-primary">hub</span>
<span class="text-sm font-medium">X / Twitter</span>
</a>
<a class="flex flex-col items-center justify-center p-4 rounded-lg bg-slate-50 dark:bg-slate-800 hover:bg-primary/10 transition-colors group" href="#">
<span class="material-symbols-outlined text-3xl mb-2 text-slate-400 group-hover:text-primary">groups</span>
<span class="text-sm font-medium">Discord</span>
</a>
<a class="flex flex-col items-center justify-center p-4 rounded-lg bg-slate-50 dark:bg-slate-800 hover:bg-primary/10 transition-colors group" href="#">
<span class="material-symbols-outlined text-3xl mb-2 text-slate-400 group-hover:text-primary">badge</span>
<span class="text-sm font-medium">LinkedIn</span>
</a>
</div>
</div>
<div class="p-8 border border-dashed border-slate-300 dark:border-slate-700 rounded-xl">
<p class="text-slate-500 text-sm leading-relaxed">
                Response times may vary based on ongoing global developments and current operational tempo. Urgent intelligence alerts are prioritized.
              </p>
</div>
</div>
</div>
<!-- Map Visualization Section -->
<div class="relative w-full h-[500px] bg-slate-900 rounded-2xl overflow-hidden border border-slate-800 shadow-2xl group">
<div class="absolute inset-0 z-10 bg-gradient-to-t from-slate-900 via-transparent to-transparent pointer-events-none"></div>
<div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?q=80&amp;w=2000&amp;auto=format&amp;fit=crop')] bg-cover bg-center mix-blend-overlay opacity-30 grayscale" data-alt="Dark global map with interconnected nodes" data-location="Global Intelligence Network" style=""></div>
<!-- Animated Glow Points (Abstract representations of data nodes) -->
<div class="absolute top-1/4 left-1/4 w-3 h-3 bg-primary rounded-full animate-pulse shadow-[0_0_15px_rgba(19,91,236,0.8)] z-20"></div>
<div class="absolute top-1/3 left-1/2 w-2 h-2 bg-primary/60 rounded-full animate-pulse shadow-[0_0_10px_rgba(19,91,236,0.5)] z-20 delay-700"></div>
<div class="absolute bottom-1/3 left-1/3 w-2 h-2 bg-primary rounded-full animate-pulse shadow-[0_0_12px_rgba(19,91,236,0.7)] z-20 delay-300"></div>
<div class="absolute top-1/2 right-1/4 w-3 h-3 bg-primary/80 rounded-full animate-pulse shadow-[0_0_15px_rgba(19,91,236,0.8)] z-20 delay-1000"></div>
<div class="absolute bottom-1/4 right-1/3 w-2 h-2 bg-primary rounded-full animate-pulse shadow-[0_0_10px_rgba(19,91,236,0.6)] z-20 delay-500"></div>
<div class="absolute inset-0 flex flex-col items-center justify-center z-30 px-6 text-center">
<h2 class="text-white text-3xl font-bold mb-4">Global Intelligence Network</h2>
<p class="text-slate-300 max-w-xl">
              Our platform operates on a decentralized global infrastructure, collecting and analyzing data across 190+ jurisdictions to provide real-time battlefield and geopolitical insights.
            </p>
<div class="mt-8 flex gap-8">
<div class="text-center">
<p class="text-primary text-2xl font-black">2.4k+</p>
<p class="text-slate-400 text-xs uppercase tracking-widest font-bold">Active Nodes</p>
</div>
<div class="text-center">
<p class="text-primary text-2xl font-black">24/7</p>
<p class="text-slate-400 text-xs uppercase tracking-widest font-bold">Monitoring</p>
</div>
<div class="text-center">
<p class="text-primary text-2xl font-black">12ms</p>
<p class="text-slate-400 text-xs uppercase tracking-widest font-bold">Avg Latency</p>
</div>
</div>
</div>
</div>
</main>
<!-- Simple Footer -->
<footer class="border-t border-slate-200 dark:border-slate-800 py-10 px-6 lg:px-20 bg-slate-50 dark:bg-slate-900/30">
<div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-6">
<div class="flex items-center gap-2 text-slate-400">
<span class="material-symbols-outlined">verified_user</span>
<span class="text-sm">© 2024 OSINT Intel Systems. All rights reserved.</span>
</div>
<div class="flex gap-8">
<a class="text-sm text-slate-500 hover:text-primary transition-colors" href="#">Privacy Policy</a>
<a class="text-sm text-slate-500 hover:text-primary transition-colors" href="#">Intelligence Disclosure</a>
<a class="text-sm text-slate-500 hover:text-primary transition-colors" href="#">Terms of Service</a>
</div>
</div>
</footer>
</div>
</div>

</template>

<script setup lang="ts">
// @ts-nocheck
import { ref, reactive, computed } from 'vue';
import { useThemeStore } from '@/stores/theme';

const themeStore = useThemeStore();
const isDark = computed(() => themeStore.isDark);

const toggleTheme = () => {
  themeStore.toggleTheme();
};

const submitted = ref(false);
const submitting = ref(false);

const form = reactive({
  name: '',
  email: '',
  subject: '',
  organization: '',
  message: '',
  privacy: false
});

const handleSubmit = async () => {
  submitting.value = true;

  // Simulate API call
  await new Promise(resolve => setTimeout(resolve, 1500));

  submitted.value = true;
  submitting.value = false;
};

const resetForm = () => {
  form.name = '';
  form.email = '';
  form.subject = '';
  form.organization = '';
  form.message = '';
  form.privacy = false;
  submitted.value = false;
};
</script>