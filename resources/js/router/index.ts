import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes: RouteRecordRaw[] = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/Home.vue'),
    meta: { requiresAuth: false, isPublic: true }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: () => import('@/views/Dashboard.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/map',
    name: 'map',
    component: () => import('@/views/MapPage.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/events',
    name: 'events',
    component: () => import('@/views/EventList.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/events/:id',
    name: 'event-detail',
    component: () => import('@/views/EventDetail.vue'),
    meta: { requiresAuth: true },
    props: true
  },
  {
    path: '/equipment',
    name: 'equipment',
    component: () => import('@/views/EquipmentList.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/equipment/:id',
    name: 'equipment-detail',
    component: () => import('@/views/EquipmentDetail.vue'),
    meta: { requiresAuth: true },
    props: true
  },
  {
    path: '/zones',
    name: 'zones',
    component: () => import('@/views/ZonesList.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/analytics',
    name: 'analytics',
    component: () => import('@/views/Analytics.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/settings',
    name: 'settings',
    component: () => import('@/views/Settings.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/admin',
    name: 'admin',
    component: () => import('@/views/admin/AdminDashboard.vue'),
    meta: { requiresAuth: true, requiresAdmin: true },
    children: [
      {
        path: 'users',
        name: 'admin-users',
        component: () => import('@/views/admin/Users.vue')
      },
      {
        path: 'actors',
        name: 'admin-actors',
        component: () => import('@/views/admin/Actors.vue')
      },
      {
        path: 'conflicts',
        name: 'admin-conflicts',
        component: () => import('@/views/admin/Conflicts.vue')
      },
      {
        path: 'events',
        name: 'admin-events',
        component: () => import('@/views/admin/Events.vue')
      },
      {
        path: 'equipment',
        name: 'admin-equipment',
        component: () => import('@/views/admin/Equipment.vue')
      },
      {
        path: 'zones',
        name: 'admin-zones',
        component: () => import('@/views/admin/Zones.vue')
      },
      {
        path: 'audit-logs',
        name: 'admin-audit-logs',
        component: () => import('@/views/admin/AuditLogs.vue')
      },
      {
        path: 'settings',
        name: 'admin-settings',
        component: () => import('@/views/admin/Settings.vue')
      }
    ]
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('@/views/auth/Login.vue'),
    meta: { requiresGuest: true }
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('@/views/auth/Register.vue'),
    meta: { requiresGuest: true }
  },
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    component: () => import('@/views/NotFound.vue')
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes
});

router.beforeEach(async (to, _from, next) => {
  const authStore = useAuthStore();

  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    next({ name: 'login', query: { redirect: to.fullPath } });
  } else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    next({ name: 'dashboard' });
  } else if (to.meta.requiresAdmin && !authStore.isAdmin) {
    next({ name: 'dashboard' });
  } else {
    next();
  }
});

export { routes };

export default router;
