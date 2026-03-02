<template>

<div class="relative flex h-auto min-h-screen w-full flex-col overflow-x-hidden">
<!-- Top Nav Bar -->
<header class="flex items-center justify-between border-b border-slate-200 dark:border-slate-800 bg-white dark:bg-background-dark px-6 py-3 sticky top-0 z-50">
<div class="flex items-center gap-8">
<div class="flex items-center gap-3 text-primary">
<div class="size-8 bg-primary rounded flex items-center justify-center text-white">
<span class="material-symbols-outlined text-2xl">radar</span>
</div>
<h2 class="text-slate-900 dark:text-slate-100 text-lg font-bold leading-tight tracking-tight">OSINT Tracker</h2>
</div>
<nav class="hidden md:flex items-center gap-6">
<a class="text-primary text-sm font-semibold" href="#">Dashboard</a>
<a class="text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary text-sm font-medium transition-colors" href="#">Map</a>
<a class="text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary text-sm font-medium transition-colors" href="#">Intelligence</a>
<a class="text-slate-600 dark:text-slate-400 hover:text-primary dark:hover:text-primary text-sm font-medium transition-colors" href="#">Reports</a>
</nav>
</div>
<div class="flex flex-1 justify-end gap-4 items-center">
<div class="relative w-full max-w-xs">
<span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xl">search</span>
<input class="w-full bg-slate-100 dark:bg-slate-800 border-none rounded-lg py-2 pl-10 pr-4 text-sm focus:ring-2 focus:ring-primary text-slate-900 dark:text-slate-100 placeholder:text-slate-500" placeholder="Search equipment, models, serials..." type="text"/>
</div>
<div class="flex gap-2">
<button class="flex items-center justify-center rounded-lg size-10 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
<span class="material-symbols-outlined">notifications</span>
</button>
<button class="flex items-center justify-center rounded-lg size-10 bg-slate-100 dark:bg-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors">
<span class="material-symbols-outlined">settings</span>
</button>
</div>
<div class="size-10 rounded-full border-2 border-primary bg-slate-200 dark:bg-slate-700 overflow-hidden">
<img alt="User Avatar" class="w-full h-full object-cover" data-alt="User profile avatar photo" src="https://lh3.googleusercontent.com/aida-public/AB6AXuCyPEAOzXz-NnOEBRRNuTXmBHY7_lr6P1PiBTKRJK19LwOQ3B3OsnurWz-Kf-JcW5fUhXhwRMRFyQpuLOBCMTgslSfbDtc1fELMTwXIHrSfEtppFiKDmgoxFRsizbDgcmzZ97ifI7wPJ8VTlIWaJqJwpoGFfxm3GCi77Twwv1PYuMtVcJVEGHu7Wa3JfKdt2s8iSL03-CAes28Bj5UbYl00O8mjTeWrbXhs8BxLUDop3nzPqFWfURXw_gmeOfjwWhe2U6-hIKzXULz9"/>
</div>
</div>
</header>
<main class="flex-1 px-6 py-6 max-w-[1600px] mx-auto w-full">
<!-- High Level Statistics -->
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
<div class="flex flex-col gap-2 rounded-xl p-6 bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex justify-between items-start">
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Total Equipment Tracked</p>
<span class="material-symbols-outlined text-primary">precision_manufacturing</span>
</div>
<p class="text-slate-900 dark:text-slate-100 text-3xl font-bold">8,421</p>
<div class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400 text-sm font-semibold">
<span class="material-symbols-outlined text-sm">trending_up</span>
<span>+0.5% vs last month</span>
</div>
</div>
<div class="flex flex-col gap-2 rounded-xl p-6 bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex justify-between items-start">
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Confirmed Sightings (24h)</p>
<span class="material-symbols-outlined text-primary">visibility</span>
</div>
<p class="text-slate-900 dark:text-slate-100 text-3xl font-bold">+42</p>
<div class="flex items-center gap-1 text-emerald-600 dark:text-emerald-400 text-sm font-semibold">
<span class="material-symbols-outlined text-sm">trending_up</span>
<span>+12% vs average</span>
</div>
</div>
<div class="flex flex-col gap-2 rounded-xl p-6 bg-white dark:bg-slate-800/50 border border-slate-200 dark:border-slate-800 shadow-sm">
<div class="flex justify-between items-start">
<p class="text-slate-500 dark:text-slate-400 text-sm font-medium">Verified Losses</p>
<span class="material-symbols-outlined text-primary">running_with_errors</span>
</div>
<p class="text-slate-900 dark:text-slate-100 text-3xl font-bold">1,204</p>
<div class="flex items-center gap-1 text-rose-600 dark:text-rose-400 text-sm font-semibold">
<span class="material-symbols-outlined text-sm">trending_down</span>
<span>-2% from yesterday</span>
</div>
</div>
</div>
<!-- Filter Bar -->
<div class="flex flex-wrap items-center gap-4 mb-6 bg-white dark:bg-slate-800/30 p-4 rounded-xl border border-slate-200 dark:border-slate-800">
<div class="flex items-center gap-2">
<span class="material-symbols-outlined text-slate-400">filter_list</span>
<span class="text-sm font-semibold text-slate-700 dark:text-slate-300">Filters:</span>
</div>
<div class="flex gap-3 flex-wrap">
<button class="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors border border-slate-200 dark:border-slate-700">
            Category: All <span class="material-symbols-outlined text-base">expand_more</span>
</button>
<button class="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors border border-slate-200 dark:border-slate-700">
            Origin: All <span class="material-symbols-outlined text-base">expand_more</span>
</button>
<button class="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors border border-slate-200 dark:border-slate-700">
            Conflict Zone: All <span class="material-symbols-outlined text-base">expand_more</span>
</button>
<button class="flex items-center gap-2 px-4 py-2 bg-slate-100 dark:bg-slate-800 rounded-lg text-sm font-medium hover:bg-slate-200 dark:hover:bg-slate-700 transition-colors border border-slate-200 dark:border-slate-700">
            Status: All <span class="material-symbols-outlined text-base">expand_more</span>
</button>
</div>
<button class="ml-auto text-primary text-sm font-semibold hover:underline">Clear all filters</button>
</div>
<div class="flex flex-col lg:flex-row gap-6">
<!-- Main Data Table -->
<div class="flex-1">
<div class="bg-white dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 overflow-hidden shadow-sm">
<div class="p-4 border-b border-slate-200 dark:border-slate-800 flex justify-between items-center">
<h3 class="font-bold text-slate-900 dark:text-slate-100">Equipment Inventory</h3>
<button class="flex items-center gap-2 text-xs font-bold text-slate-500 uppercase tracking-wider hover:text-primary transition-colors">
                Export Data <span class="material-symbols-outlined text-sm">download</span>
</button>
</div>
<div class="overflow-x-auto">
<table class="w-full text-left border-collapse">
<thead>
<tr class="bg-slate-50 dark:bg-slate-800/80 text-slate-500 dark:text-slate-400 text-xs font-semibold uppercase">
<th class="px-6 py-4">Thumbnail</th>
<th class="px-6 py-4">Model Name</th>
<th class="px-6 py-4">Type</th>
<th class="px-6 py-4">Affiliation</th>
<th class="px-6 py-4 text-center">Status</th>
</tr>
</thead>
<tbody class="divide-y divide-slate-100 dark:divide-slate-800">
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
<td class="px-6 py-4">
<div class="w-16 h-10 bg-slate-200 dark:bg-slate-700 rounded overflow-hidden">
<img alt="MBT" class="w-full h-full object-cover" data-alt="Military main battle tank in field" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAi-kasl_ByMHhsaEROmNacWsoDYA9h6MxCj-qFUo0xSD-ebhaJLgyLSN5X_y3aZJ-91Gz_5F5FoduAnC8hNQW1Z6vM08SHO9s_2g1THwqUlGwHMJYYvSjKcj0CUJPJVFfl336l9k_iwammbF4DX2netNbjDdZxCENJJMOwJzUdhPFgVImiH8131zGSMlhaVrS3fIrX9ww9haXwW0ucOmcBGnHZu9LzW8tooOqPikkHt5Sk-kOS-mxbg7FpkgcerrcEqhesnklSgF-H"/>
</div>
</td>
<td class="px-6 py-4 font-semibold text-slate-900 dark:text-slate-100">T-72B3 M2016</td>
<td class="px-6 py-4 text-slate-600 dark:text-slate-400">Main Battle Tank</td>
<td class="px-6 py-4 text-slate-600 dark:text-slate-400">Russian Federation</td>
<td class="px-6 py-4 text-center">
<span class="inline-flex items-center rounded-full bg-rose-100 dark:bg-rose-900/30 px-3 py-1 text-xs font-bold text-rose-600 dark:text-rose-400">Destroyed</span>
</td>
</tr>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
<td class="px-6 py-4">
<div class="w-16 h-10 bg-slate-200 dark:bg-slate-700 rounded overflow-hidden">
<img alt="IFV" class="w-full h-full object-cover" data-alt="Infantry fighting vehicle on muddy road" src="https://lh3.googleusercontent.com/aida-public/AB6AXuAnJC_jx4cEKHY6fSBKYG6YxLpbWOmEtjPWzwcmSc5nsKKwvN7EVPyHrcjTMetey0tX0s5HOwtfGmMpUGCeMIqLq_0P6cL31v7FBlkdEwtjYP6psTZzvsc-UstXSI1rkQAO898Ap_aa31gfr_Y0OssFKgoivYjXdX1wHesTv4OUniJUJAva2oOM8B9tkbdWs81sVQ8x4AR-1qWpm6wDpLrzBizBjRJHUMiDXfqIkT8l5FEYIUcdn93epxqOlERD1mGQuUhCpPtEgcdz"/>
</div>
</td>
<td class="px-6 py-4 font-semibold text-slate-900 dark:text-slate-100">M2A2 Bradley</td>
<td class="px-6 py-4 text-slate-600 dark:text-slate-400">IFV</td>
<td class="px-6 py-4 text-slate-600 dark:text-slate-400">Ukraine</td>
<td class="px-6 py-4 text-center">
<span class="inline-flex items-center rounded-full bg-emerald-100 dark:bg-emerald-900/30 px-3 py-1 text-xs font-bold text-emerald-600 dark:text-emerald-400">Active</span>
</td>
</tr>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
<td class="px-6 py-4">
<div class="w-16 h-10 bg-slate-200 dark:bg-slate-700 rounded overflow-hidden">
<img alt="APC" class="w-full h-full object-cover" data-alt="Armored personnel carrier camouflage" src="https://lh3.googleusercontent.com/aida-public/AB6AXuDrgqksTG7W-jLuCZp-t-ClEVCCwzCWQ7TAA2JVutJnFVj8s59w3SFZ7r3Mg4cgynadAhDTMWcl3sfZlYGmqmX2QF7YeZEewfDqda4rhf7IR66Fd9E07BjG7eYOCD_lCa9Xyj3ukH6N-daSRkeg7KvRX5Xnxb0SRYbSo4tNef-tcJ9mi1IVtFz2Afu4Ydzh2xZPZpoqzMb-gxMu5b-x0eeBcvSnLYmdbMF0k_0ezDivKMnJ-dTYtz1M0DNr9D2JeoO4isNhtAyMGbKH"/>
</div>
</td>
<td class="px-6 py-4 font-semibold text-slate-900 dark:text-slate-100">BTR-82A</td>
<td class="px-6 py-4 text-slate-600 dark:text-slate-400">APC</td>
<td class="px-6 py-4 text-slate-600 dark:text-slate-400">Russian Federation</td>
<td class="px-6 py-4 text-center">
<span class="inline-flex items-center rounded-full bg-amber-100 dark:bg-amber-900/30 px-3 py-1 text-xs font-bold text-amber-600 dark:text-amber-400">Abandoned</span>
</td>
</tr>
<tr class="hover:bg-slate-50 dark:hover:bg-slate-800/30 transition-colors group">
<td class="px-6 py-4">
<div class="w-16 h-10 bg-slate-200 dark:bg-slate-700 rounded overflow-hidden">
<img alt="Aircraft" class="w-full h-full object-cover" data-alt="Military aircraft flying through clouds" src="https://lh3.googleusercontent.com/aida-public/AB6AXuBPAGpiel3YSPRTWtRX3EFACrw1z63VNTMOcKheZNqhsAQq8OLZu3JhaJfdhuK2Nrz7b8aS9ailzGqISyx6PJUgW5L0erzm7RUWtQa96k8yLWCf7rDn9e3X1vegMo2WxO-_6k31Kvy6mOKM9B0oirzGDXV2TRm6KpvQqEfKc11C9t9r4oabqaTvbmUjfB_iteLt6Wo409z5SSPHi8l7HQQQWjr39Ff6obqxPDGCpWJMTrUFhEPN1SVRw-ctN8zYSJHyr9f8g-UxOtkE"/>
</div>
</td>
<td class="px-6 py-4 font-semibold text-slate-900 dark:text-slate-100">Su-25 Grach</td>
<td class="px-6 py-4 text-slate-600 dark:text-slate-400">Attack Aircraft</td>
<td class="px-6 py-4 text-slate-600 dark:text-slate-400">Russian Federation</td>
<td class="px-6 py-4 text-center">
<span class="inline-flex items-center rounded-full bg-sky-100 dark:bg-sky-900/30 px-3 py-1 text-xs font-bold text-sky-600 dark:text-sky-400">Sighted</span>
</td>
</tr>
</tbody>
</table>
</div>
<div class="p-4 border-t border-slate-200 dark:border-slate-800 flex items-center justify-between text-sm text-slate-500">
<p>Showing 1 to 4 of 8,421 entries</p>
<div class="flex gap-2">
<button class="px-3 py-1 rounded border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800">Previous</button>
<button class="px-3 py-1 rounded bg-primary text-white font-medium">1</button>
<button class="px-3 py-1 rounded border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800">2</button>
<button class="px-3 py-1 rounded border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800">3</button>
<button class="px-3 py-1 rounded border border-slate-200 dark:border-slate-700 hover:bg-slate-100 dark:hover:bg-slate-800">Next</button>
</div>
</div>
</div>
</div>
<!-- Right Sidebar: Intelligence Reports -->
<aside class="w-full lg:w-80 flex flex-col gap-6">
<div class="bg-white dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 p-4 shadow-sm">
<h3 class="font-bold text-slate-900 dark:text-slate-100 mb-4 flex items-center gap-2">
<span class="material-symbols-outlined text-primary text-xl">feed</span>
              Latest Intel Reports
            </h3>
<div class="space-y-4">
<div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-lg border-l-4 border-primary">
<p class="text-xs text-slate-400 mb-1 font-medium">12 mins ago • Kharkiv Sector</p>
<h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">New T-90M convoy sighted heading West</h4>
<p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">Satellite imagery confirms movement of 8 units from temporary staging area...</p>
</div>
<div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-lg border-l-4 border-emerald-500">
<p class="text-xs text-slate-400 mb-1 font-medium">1 hour ago • Zap Sector</p>
<h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">Bradley IFV maintenance log update</h4>
<p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">4 units returned to service after repair at forward operating base...</p>
</div>
<div class="p-3 bg-slate-50 dark:bg-slate-800 rounded-lg border-l-4 border-amber-500">
<p class="text-xs text-slate-400 mb-1 font-medium">3 hours ago • Black Sea</p>
<h4 class="text-sm font-semibold text-slate-900 dark:text-slate-100 mb-1">Unidentified naval drone recovered</h4>
<p class="text-xs text-slate-600 dark:text-slate-400 line-clamp-2">COMSAT signal analysis suggests origin from Western manufacturer...</p>
</div>
</div>
<button class="w-full mt-4 py-2 text-xs font-bold text-primary uppercase tracking-widest hover:bg-primary/5 rounded transition-colors">
              View All Intelligence
            </button>
</div>
<div class="bg-primary/10 rounded-xl border border-primary/20 p-4">
<h3 class="font-bold text-slate-900 dark:text-slate-100 mb-2 flex items-center gap-2">
<span class="material-symbols-outlined text-primary text-xl">analytics</span>
              Quick Tip
            </h3>
<p class="text-xs text-slate-600 dark:text-slate-400 leading-relaxed">
              Use the "Status" filter to specifically track verified combat losses and generate damage assessment reports for active sectors.
            </p>
</div>
</aside>
</div>
<!-- Bottom Visualizations -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-8">
<div class="bg-white dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
<h3 class="font-bold text-slate-900 dark:text-slate-100 mb-6">Equipment Distribution by Type</h3>
<div class="flex items-center justify-around">
<div class="relative size-48">
<!-- Mock Donut Chart using Gradient/SVGs -->
<svg class="size-full transform -rotate-90" viewbox="0 0 36 36">
<circle class="stroke-slate-100 dark:stroke-slate-700" cx="18" cy="18" fill="none" r="16" stroke-width="3"></circle>
<circle class="stroke-primary" cx="18" cy="18" fill="none" r="16" stroke-dasharray="60, 100" stroke-width="3"></circle>
<circle class="stroke-sky-400" cx="18" cy="18" fill="none" r="16" stroke-dasharray="20, 100" stroke-dashoffset="-60" stroke-width="3"></circle>
<circle class="stroke-emerald-400" cx="18" cy="18" fill="none" r="16" stroke-dasharray="15, 100" stroke-dashoffset="-80" stroke-width="3"></circle>
</svg>
<div class="absolute inset-0 flex flex-col items-center justify-center">
<span class="text-2xl font-bold">8.4k</span>
<span class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">Total</span>
</div>
</div>
<div class="space-y-2">
<div class="flex items-center gap-3">
<div class="size-3 rounded-full bg-primary"></div>
<span class="text-sm font-medium text-slate-600 dark:text-slate-400">Tanks (60%)</span>
</div>
<div class="flex items-center gap-3">
<div class="size-3 rounded-full bg-sky-400"></div>
<span class="text-sm font-medium text-slate-600 dark:text-slate-400">IFVs (20%)</span>
</div>
<div class="flex items-center gap-3">
<div class="size-3 rounded-full bg-emerald-400"></div>
<span class="text-sm font-medium text-slate-600 dark:text-slate-400">Aircraft (15%)</span>
</div>
<div class="flex items-center gap-3">
<div class="size-3 rounded-full bg-slate-300 dark:bg-slate-600"></div>
<span class="text-sm font-medium text-slate-600 dark:text-slate-400">Others (5%)</span>
</div>
</div>
</div>
</div>
<div class="bg-white dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-800 p-6 shadow-sm">
<div class="flex justify-between items-center mb-6">
<h3 class="font-bold text-slate-900 dark:text-slate-100">Sightings Over Time</h3>
<div class="flex gap-2">
<span class="text-xs font-semibold px-2 py-1 bg-primary/10 text-primary rounded">7 Days</span>
<span class="text-xs font-semibold px-2 py-1 text-slate-400 hover:bg-slate-100 dark:hover:bg-slate-800 rounded cursor-pointer">30 Days</span>
</div>
</div>
<!-- Line Graph Mockup -->
<div class="h-48 w-full flex items-end gap-1 px-2">
<div class="flex-1 bg-primary/20 hover:bg-primary transition-all rounded-t h-[40%]" title="Mon: 12"></div>
<div class="flex-1 bg-primary/20 hover:bg-primary transition-all rounded-t h-[65%]" title="Tue: 24"></div>
<div class="flex-1 bg-primary/20 hover:bg-primary transition-all rounded-t h-[55%]" title="Wed: 18"></div>
<div class="flex-1 bg-primary/20 hover:bg-primary transition-all rounded-t h-[85%]" title="Thu: 38"></div>
<div class="flex-1 bg-primary/20 hover:bg-primary transition-all rounded-t h-[60%]" title="Fri: 22"></div>
<div class="flex-1 bg-primary/20 hover:bg-primary transition-all rounded-t h-[95%]" title="Sat: 42"></div>
<div class="flex-1 bg-primary hover:bg-primary transition-all rounded-t h-[100%]" title="Sun: 45"></div>
</div>
<div class="flex justify-between mt-4 text-[10px] font-bold text-slate-400 uppercase tracking-widest px-1">
<span>Mon</span>
<span>Tue</span>
<span>Wed</span>
<span>Thu</span>
<span>Fri</span>
<span>Sat</span>
<span>Sun</span>
</div>
</div>
</div>
</main>
<footer class="mt-12 py-8 px-6 border-t border-slate-200 dark:border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4">
<div class="flex items-center gap-2 text-slate-400 text-sm">
<span class="material-symbols-outlined text-base">verified_user</span>
<span>OSINT-TRACK Secure Intelligence Node #4920</span>
</div>
<div class="flex gap-6 text-sm font-medium text-slate-500">
<a class="hover:text-primary" href="#">Data Sources</a>
<a class="hover:text-primary" href="#">API Access</a>
<a class="hover:text-primary" href="#">Privacy Policy</a>
</div>
<div class="text-slate-400 text-xs">
        © 2024 OSINT Equipment Tracker. System Status: <span class="text-emerald-500 font-bold uppercase">Optimal</span>
</div>
</footer>
</div>

```
</template>

<script setup lang="ts">
// @ts-nocheck
import { ref, reactive, computed } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();
const viewMode = ref<'grid' | 'list'>('grid');
const currentPage = ref(1);
const perPage = ref(12);

const stats = ref({
  total: 8943,
  destroyed: 5621,
  captured: 2134,
  categories: 42
});

const filters = reactive({
  search: '',
  category: '',
  country: '',
  status: ''
});

interface Equipment {
  id: number;
  name: string;
  category: string;
  manufacturer: string;
  origin_country: string;
  description: string;
  image_url?: string;
  destroyed: number;
  captured: number;
  abandoned: number;
  damaged: number;
  total_losses: number;
}

const equipment = ref<Equipment[]>([
  { id: 1, name: 'T-72B3', category: 'Tanks', manufacturer: 'Uralvagonzavod', origin_country: 'Russia', description: 'Main battle tank used extensively in the conflict', destroyed: 892, captured: 234, abandoned: 156, damaged: 78, total_losses: 1360 },
  { id: 2, name: 'T-80BVM', category: 'Tanks', manufacturer: 'Omsktransmash', origin_country: 'Russia', description: 'Advanced main battle tank with gas turbine engine', destroyed: 234, captured: 89, abandoned: 45, damaged: 23, total_losses: 391 },
  { id: 3, name: 'BMP-2', category: 'IFV', manufacturer: 'Kurganmashzavod', origin_country: 'Russia', description: 'Infantry fighting vehicle with 30mm autocannon', destroyed: 567, captured: 321, abandoned: 189, damaged: 45, total_losses: 1122 },
  { id: 4, name: 'BTR-82A', category: 'APC', manufacturer: 'Arzamas', origin_country: 'Russia', description: 'Wheeled armored personnel carrier', destroyed: 423, captured: 178, abandoned: 234, damaged: 56, total_losses: 891 },
  { id: 5, name: 'Leopard 2A6', category: 'Tanks', manufacturer: 'Krauss-Maffei', origin_country: 'Germany', description: 'Western main battle tank provided to Ukraine', destroyed: 23, captured: 2, abandoned: 5, damaged: 8, total_losses: 38 },
  { id: 6, name: 'M777 Howitzer', category: 'Artillery', manufacturer: 'BAE Systems', origin_country: 'USA', description: '155mm towed howitzer', destroyed: 45, captured: 3, abandoned: 12, damaged: 8, total_losses: 68 },
  { id: 7, name: 'HIMARS', category: 'MLRS', manufacturer: 'Lockheed Martin', origin_country: 'USA', description: 'High Mobility Artillery Rocket System', destroyed: 2, captured: 0, abandoned: 0, damaged: 1, total_losses: 3 },
  { id: 8, name: 'Su-35', category: 'Aircraft', manufacturer: 'Sukhoi', origin_country: 'Russia', description: 'Multi-role air superiority fighter', destroyed: 24, captured: 0, abandoned: 0, damaged: 3, total_losses: 27 },
  { id: 9, name: 'Ka-52', category: 'Helicopter', manufacturer: 'Kamov', origin_country: 'Russia', description: 'Attack helicopter with ejection seats', destroyed: 31, captured: 0, abandoned: 1, damaged: 5, total_losses: 37 },
  { id: 10, name: 'Orlan-10', category: 'UAV', manufacturer: 'Special Technology Center', origin_country: 'Russia', description: 'Reconnaissance UAV', destroyed: 234, captured: 89, abandoned: 34, damaged: 12, total_losses: 369 },
  { id: 11, name: 'T-64BV', category: 'Tanks', manufacturer: 'Kharkiv Morozov', origin_country: 'Ukraine', description: 'Ukrainian main battle tank', destroyed: 156, captured: 45, abandoned: 23, damaged: 34, total_losses: 258 },
  { id: 12, name: 'Bradley M2A2', category: 'IFV', manufacturer: 'BAE Systems', origin_country: 'USA', description: 'Infantry fighting vehicle provided to Ukraine', destroyed: 34, captured: 5, abandoned: 8, damaged: 12, total_losses: 59 }
]);

const filteredEquipment = computed(() => {
  return equipment.value.filter(e => {
    const matchesSearch = !filters.search ||
      e.name.toLowerCase().includes(filters.search.toLowerCase()) ||
      e.manufacturer.toLowerCase().includes(filters.search.toLowerCase());
    const matchesCategory = !filters.category || e.category.toLowerCase() === filters.category;
    const matchesCountry = !filters.country || e.origin_country.toLowerCase().includes(filters.country);
    return matchesSearch && matchesCategory && matchesCountry;
  });
});

const totalPages = computed(() => Math.ceil(filteredEquipment.value.length / perPage.value) || 1);

const getCategoryBadgeClass = (category: string) => {
  const classes: Record<string, string> = {
    'Tanks': 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400',
    'IFV': 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400',
    'APC': 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400',
    'Artillery': 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400',
    'MLRS': 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400',
    'Aircraft': 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-400',
    'Helicopter': 'bg-pink-100 dark:bg-pink-900/30 text-pink-700 dark:text-pink-400',
    'UAV': 'bg-cyan-100 dark:bg-cyan-900/30 text-cyan-700 dark:text-cyan-400'
  };
  return classes[category] || 'bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-400';
};

const viewEquipment = (item: Equipment) => {
  router.push({ name: 'equipment-detail', params: { id: item.id } });
};
</script>