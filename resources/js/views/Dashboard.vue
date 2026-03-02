<template>

<div class="relative flex min-h-screen w-full flex-col overflow-x-hidden">
<!-- Top Navigation Bar -->
<header class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-background-dark px-6 py-3 sticky top-0 z-50">
<div class="flex items-center gap-8">
<div class="flex items-center gap-3 text-primary">
<div class="size-8 flex items-center justify-center bg-primary/10 rounded-lg">
<span class="material-symbols-outlined text-primary">analytics</span>
</div>
<h2 class="text-slate-900 dark:text-white text-lg font-bold leading-tight tracking-tight">OSINT Tracker</h2>
</div>
<label class="hidden md:flex flex-col min-w-64">
<div class="flex w-full items-stretch rounded-lg bg-slate-100 dark:bg-slate-800/50 border border-transparent focus-within:border-primary/50 transition-all">
<div class="text-slate-500 flex items-center justify-center pl-3">
<span class="material-symbols-outlined text-[20px]">search</span>
</div>
<input class="w-full bg-transparent border-none focus:ring-0 text-sm py-2 px-3 text-slate-900 dark:text-slate-100 placeholder:text-slate-500" placeholder="Search intel, reports, coordinates..."/>
</div>
</label>
</div>
<div class="flex items-center gap-4">
<button class="flex items-center justify-center rounded-lg h-10 w-10 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-primary/10 hover:text-primary transition-colors">
<span class="material-symbols-outlined">notifications</span>
</button>
<div class="h-8 w-[1px] bg-slate-200 dark:bg-slate-800 mx-2"></div>
<div class="flex items-center gap-3">
<div class="flex flex-col items-end hidden sm:flex">
<span class="text-sm font-semibold dark:text-white">Senior Analyst</span>
<span class="text-xs text-slate-500">ID: #8821-XP</span>
</div>
<div class="bg-center bg-no-repeat aspect-square bg-cover rounded-full size-10 border-2 border-primary/20" data-alt="User profile professional portrait" style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuDpsAYY2vm-iV8iHinmg9VFbr_aUKHWXSiX_-kVBHKEM0GwAMnppb9-1SsNWNfQPBZMKNr-MfJ2eT1rmVUncdQG31o8Xpd8_aG9U1ikIU9Xcxm-YnlXWARy4Ni76PlJQCY_kHVlNWWGwKYaVP8iPVIWA-5czjjW4614wxa37IVR1mkX1xWIpPAREK4ren_quX3ETDj1MUc2ftr4HPY48cyxvUXrBd7BcTty8ezcz7NwhZ0E_emG4GeeXLSnqNQYyNejZ8uST95w4etr")'>
</div>
</div>
</div>
</header>
<div class="flex flex-1 overflow-hidden">
<!-- Left Sidebar Navigation -->
<aside class="w-64 border-r border-slate-200 dark:border-slate-800 bg-white dark:bg-background-dark hidden lg:flex flex-col p-4 gap-2">
<div class="mb-4 px-3">
<p class="text-xs font-bold uppercase tracking-widest text-slate-400 dark:text-slate-500">Navigation</p>
</div>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg bg-primary text-white font-medium" href="#">
<span class="material-symbols-outlined">dashboard</span>
<span>Dashboard</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined">person</span>
<span>Profile</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined">description</span>
<span>Reports</span>
</a>
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined">map</span>
<span>Tactical Map</span>
</a>
<div class="mt-auto flex flex-col gap-2">
<a class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-slate-600 dark:text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors" href="#">
<span class="material-symbols-outlined">settings</span>
<span>Settings</span>
</a>
<div class="bg-primary/10 rounded-xl p-4 mt-4">
<p class="text-xs font-bold text-primary uppercase mb-1">System Status</p>
<div class="flex items-center gap-2">
<div class="size-2 bg-emerald-500 rounded-full animate-pulse"></div>
<span class="text-xs font-medium dark:text-slate-200">Operational</span>
</div>
</div>
</div>
</aside>
<!-- Main Content Area -->
<main class="flex-1 overflow-y-auto custom-scrollbar p-6 lg:p-8 bg-slate-50 dark:bg-background-dark">
<!-- Welcome Header -->
<div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
<div>
<h1 class="text-3xl font-black dark:text-white tracking-tight">Welcome back, Analyst Smith</h1>
<p class="text-slate-500 dark:text-slate-400 mt-1">Global Security Index: <span class="text-amber-500 font-semibold">Elevated</span> | Active conflict zones: 12</p>
</div>
<button class="bg-primary hover:bg-primary/90 text-white px-5 py-2.5 rounded-lg font-bold flex items-center gap-2 transition-all shadow-lg shadow-primary/20">
<span class="material-symbols-outlined text-[20px]">add</span>
                        Submit New Tip
                    </button>
</div>
<!-- Metric Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
<div class="bg-white dark:bg-slate-800/40 p-6 rounded-xl border border-slate-200 dark:border-slate-800">
<div class="flex justify-between items-start mb-4">
<span class="material-symbols-outlined text-primary p-2 bg-primary/10 rounded-lg">upload_file</span>
<span class="text-emerald-500 text-xs font-bold px-2 py-1 bg-emerald-500/10 rounded">+2%</span>
</div>
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium">My Submitted Tips</p>
<h3 class="text-3xl font-bold dark:text-white mt-1">24</h3>
</div>
<div class="bg-white dark:bg-slate-800/40 p-6 rounded-xl border border-slate-200 dark:border-slate-800">
<div class="flex justify-between items-start mb-4">
<span class="material-symbols-outlined text-amber-500 p-2 bg-amber-500/10 rounded-lg">bookmark</span>
<span class="text-slate-400 text-xs font-bold px-2 py-1 bg-slate-400/10 rounded">0%</span>
</div>
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Saved Reports</p>
<h3 class="text-3xl font-bold dark:text-white mt-1">12</h3>
</div>
<div class="bg-white dark:bg-slate-800/40 p-6 rounded-xl border border-slate-200 dark:border-slate-800">
<div class="flex justify-between items-start mb-4">
<span class="material-symbols-outlined text-rose-500 p-2 bg-rose-500/10 rounded-lg">warning</span>
<span class="text-emerald-500 text-xs font-bold px-2 py-1 bg-emerald-500/10 rounded">+1 NEW</span>
</div>
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Active Alerts</p>
<h3 class="text-3xl font-bold dark:text-white mt-1">5</h3>
</div>
<div class="bg-white dark:bg-slate-800/40 p-6 rounded-xl border border-slate-200 dark:border-slate-800">
<div class="flex justify-between items-start mb-4">
<span class="material-symbols-outlined text-blue-400 p-2 bg-blue-400/10 rounded-lg">verified</span>
<span class="text-emerald-500 text-xs font-bold px-2 py-1 bg-emerald-500/10 rounded">+5%</span>
</div>
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Verification Progress</p>
<h3 class="text-3xl font-bold dark:text-white mt-1">88%</h3>
</div>
</div>
<!-- Dashboard Layout Grid -->
<div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
<!-- Recent Activity Feed (Left - 2/3 wide on XL) -->
<div class="xl:col-span-2 flex flex-col gap-4">
<div class="flex items-center justify-between mb-2">
<h2 class="text-xl font-bold dark:text-white flex items-center gap-2">
<span class="material-symbols-outlined text-primary">history</span>
                                Recent Activity
                            </h2>
<button class="text-sm text-primary font-semibold hover:underline">View All</button>
</div>
<div class="bg-white dark:bg-slate-800/40 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden">
<div class="flex flex-col">
<!-- Activity Item 1 -->
<div class="p-4 border-b border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
<div class="flex gap-4">
<div class="size-10 rounded-full bg-emerald-500/10 flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-emerald-500">check_circle</span>
</div>
<div class="flex-1">
<div class="flex justify-between items-start">
<h4 class="font-bold dark:text-slate-200">Tip Verified: Satellite Imagery Analysis</h4>
<span class="text-xs text-slate-500">2h ago</span>
</div>
<p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Your tip regarding troop movements in Sector 7-G has been cross-referenced and verified as 'Highly Likely'.</p>
<div class="mt-2 flex gap-2">
<span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-primary/10 text-primary">Intelligence</span>
<span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 dark:bg-slate-700 text-slate-500">Sector 7-G</span>
</div>
</div>
</div>
</div>
<!-- Activity Item 2 -->
<div class="p-4 border-b border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
<div class="flex gap-4">
<div class="size-10 rounded-full bg-rose-500/10 flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-rose-500">crisis_alert</span>
</div>
<div class="flex-1">
<div class="flex justify-between items-start">
<h4 class="font-bold dark:text-slate-200">Alert Escalation: Eastern Border</h4>
<span class="text-xs text-slate-500">5h ago</span>
</div>
<p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Multiple reports of communication jamming detected in tracked AO 'Delta Ridge'. Conflict rating increased to Level 4.</p>
</div>
</div>
</div>
<!-- Activity Item 3 -->
<div class="p-4 border-b border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
<div class="flex gap-4">
<div class="size-10 rounded-full bg-amber-500/10 flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-amber-500">edit_note</span>
</div>
<div class="flex-1">
<div class="flex justify-between items-start">
<h4 class="font-bold dark:text-slate-200">Report Draft Updated</h4>
<span class="text-xs text-slate-500">1d ago</span>
</div>
<p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Weekly Intelligence Summary (W.I.S) has been updated with new geolocation data from the Kharkiv region.</p>
</div>
</div>
</div>
<!-- Activity Item 4 -->
<div class="p-4 hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
<div class="flex gap-4">
<div class="size-10 rounded-full bg-blue-500/10 flex items-center justify-center shrink-0">
<span class="material-symbols-outlined text-blue-500">group</span>
</div>
<div class="flex-1">
<div class="flex justify-between items-start">
<h4 class="font-bold dark:text-slate-200">Collaborator Added</h4>
<span class="text-xs text-slate-500">2d ago</span>
</div>
<p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Analyst 'GeoSentinel' has joined your collaborative investigation on 'Supply Chain Disruptions'.</p>
</div>
</div>
</div>
</div>
</div>
</div>
<!-- Saved Map Views (Right - 1/3 wide on XL) -->
<div class="flex flex-col gap-4">
<div class="flex items-center justify-between mb-2">
<h2 class="text-xl font-bold dark:text-white flex items-center gap-2">
<span class="material-symbols-outlined text-primary">explore</span>
                                Saved Map Views
                            </h2>
</div>
<div class="grid grid-cols-1 gap-4">
<!-- Map View 1 -->
<div class="group relative overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/40">
<div class="h-32 w-full bg-slate-200 dark:bg-slate-700 bg-cover bg-center transition-transform group-hover:scale-105 duration-500" data-alt="Satellite map view of urban combat zone" data-location="Kyiv" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCz_S72KbB-NUh3Vy2Fwm45rMvew7bqsqXuSNVcWiW6UkeYwhvRID4o95J9bbANOyhkGIlPsHU1xhA053eGB9BP9N1URszQ3irxTbs5t6Qh6fzw9DJ7q4qYfQ75q0X8GoIonPlnw-Ja0Z2EIm4yP6XR3B-0e6WYsphCcghgpte0Lc9luFnExxo7Cbuquay083nckkNjaYkfk3vmuFpO4E6QUUG39S3ux-hEOeNwuhtl9SGAyXiOTFaAL63ObqA2gAxFMAf3v7NRmQRT')">
</div>
<div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
<div class="absolute bottom-3 left-3 right-3">
<h4 class="text-white font-bold text-sm">Northern Front - Urban AO</h4>
<div class="flex items-center gap-1 text-slate-300 text-[10px] mt-0.5">
<span class="material-symbols-outlined text-[12px]">location_on</span>
                                        50.4501° N, 30.5234° E
                                    </div>
</div>
<button class="absolute top-3 right-3 p-1.5 rounded-lg bg-black/40 text-white opacity-0 group-hover:opacity-100 transition-opacity backdrop-blur-md">
<span class="material-symbols-outlined text-[18px]">open_in_new</span>
</button>
</div>
<!-- Map View 2 -->
<div class="group relative overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/40">
<div class="h-32 w-full bg-slate-200 dark:bg-slate-700 bg-cover bg-center transition-transform group-hover:scale-105 duration-500" data-alt="Topographical map of mountain range conflict" data-location="Caucasus Mountains" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuD9lhO4umAoOmMRVP-WRFg88orIh7GLwzgdYDWd8i4lBm7HNEzNQZJFK_Hw55WN2H83UZUzmYPzeWCkGOphlPBsXCEzvsmRmy1mysXBvdgjcKiTjWhn1H1ciuS7wNQBTZ7CxgMS6gKq7L9-y5L8nKZ-CecZZsZjZlhj7Bdlgqp5ksb8_uHWnsyv_608uFFbQCgy8g3XTvp3REdTo2J5qRGm63h8_lu9P_VIDsrQg99k0IMp7-VtwxsMCCiDXXWvvGrzZADKnaaHTmvi')">
</div>
<div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
<div class="absolute bottom-3 left-3 right-3">
<h4 class="text-white font-bold text-sm">Caucasus - Pass Monitor</h4>
<div class="flex items-center gap-1 text-slate-300 text-[10px] mt-0.5">
<span class="material-symbols-outlined text-[12px]">location_on</span>
                                        42.5333° N, 44.4667° E
                                    </div>
</div>
<button class="absolute top-3 right-3 p-1.5 rounded-lg bg-black/40 text-white opacity-0 group-hover:opacity-100 transition-opacity backdrop-blur-md">
<span class="material-symbols-outlined text-[18px]">open_in_new</span>
</button>
</div>
<!-- Map View 3 -->
<div class="group relative overflow-hidden rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-800/40">
<div class="h-32 w-full bg-slate-200 dark:bg-slate-700 bg-cover bg-center transition-transform group-hover:scale-105 duration-500" data-alt="Infrared heat map of shipping lanes" data-location="Strait of Hormuz" style="background-image: url('https://lh3.googleusercontent.com/aida-public/AB6AXuCAU0LyjE1zdpnlJ5F2NgVImQznFlNSgaZlNjVzwC_rrzBTrmDg1VsZSKrW7Zf9o1fUvDtu5FK3nK6L75mt7OnUXs7muXosmpNX-viJBT6EgfzTvsQNuU6q-PrnurhcCZR_Ve6-U5jei76UQk_YPt8ZgyKsXlVC9Uc5AZAS4uZgWjfoamlBvxulWoEul7T6aBnH51WRrrYUR5arRdlSvemHLuvQCBSjHLCK5EwkxONJ57tAfLQ2B2Esc-rv_YoOwMJSolGFr1zSzcJ_')">
</div>
<div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent"></div>
<div class="absolute bottom-3 left-3 right-3">
<h4 class="text-white font-bold text-sm">Hormuz Shipping Corridor</h4>
<div class="flex items-center gap-1 text-slate-300 text-[10px] mt-0.5">
<span class="material-symbols-outlined text-[12px]">location_on</span>
                                        26.5667° N, 56.2500° E
                                    </div>
</div>
<button class="absolute top-3 right-3 p-1.5 rounded-lg bg-black/40 text-white opacity-0 group-hover:opacity-100 transition-opacity backdrop-blur-md">
<span class="material-symbols-outlined text-[18px]">open_in_new</span>
</button>
</div>
</div>
<button class="w-full mt-2 py-3 border-2 border-dashed border-slate-200 dark:border-slate-800 rounded-xl text-slate-500 dark:text-slate-400 font-medium hover:border-primary/50 hover:text-primary transition-all flex items-center justify-center gap-2">
<span class="material-symbols-outlined">add_location</span>
                            Add Map Preset
                        </button>
</div>
</div>
</main>
</div>
</div>

</template>

<script setup lang="ts">
// @ts-nocheck
import { ref, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useEventsStore } from '@/stores/events';
import { useAuthStore } from '@/stores/auth';
import EventCard from '@/components/events/EventCard.vue';

const router = useRouter();
const eventsStore = useEventsStore();
const authStore = useAuthStore();

const stats = ref({
  totalEvents: 0,
  verifiedEvents: 0,
  activeConflicts: 0,
  recentEvents: 0
});

const recentEvents = computed(() => {
  return eventsStore.events.slice(0, 5);
});

onMounted(async () => {
  await eventsStore.fetchEvents();

  stats.value = {
    totalEvents: eventsStore.pagination.total,
    verifiedEvents: eventsStore.events.filter(e => e.verified).length,
    activeConflicts: 0,
    recentEvents: eventsStore.events.filter(e => {
      const eventDate = new Date(e.occurred_at);
      const sevenDaysAgo = new Date();
      sevenDaysAgo.setDate(sevenDaysAgo.getDate() - 7);
      return eventDate >= sevenDaysAgo;
    }).length
  };
});

const navigateToMap = () => {
  router.push({ name: 'map' });
};

const navigateToEvents = () => {
  router.push({ name: 'events' });
};
</script>