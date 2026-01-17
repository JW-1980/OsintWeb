import { createRouter, createWebHistory, RouteRecordRaw } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const routes: RouteRecordRaw[] = [
  // ==========================================
  // PUBLIC ROUTES (No login required)
  // ==========================================
  {
    path: '/',
    name: 'home',
    component: () => import('@/views/Home.vue'),
    meta: { requiresAuth: false, isPublic: true }
  },
  {
    path: '/explore',
    name: 'explore-map',
    component: () => import('@/views/public/ExploreMap.vue'),
    meta: { requiresAuth: false, isPublic: true }
  },
  {
    path: '/explore/events',
    name: 'explore-events',
    component: () => import('@/views/public/ExploreEvents.vue'),
    meta: { requiresAuth: false, isPublic: true }
  },
  {
    path: '/explore/events/:id',
    name: 'explore-event-detail',
    component: () => import('@/views/public/ExploreEventDetail.vue'),
    meta: { requiresAuth: false, isPublic: true },
    props: true
  },
  {
    path: '/explore/equipment',
    name: 'explore-equipment',
    component: () => import('@/views/public/ExploreEquipment.vue'),
    meta: { requiresAuth: false, isPublic: true }
  },
  {
    path: '/explore/equipment/:id',
    name: 'explore-equipment-detail',
    component: () => import('@/views/public/ExploreEquipmentDetail.vue'),
    meta: { requiresAuth: false, isPublic: true },
    props: true
  },
  {
    path: '/explore/actors',
    name: 'explore-actors',
    component: () => import('@/views/public/ExploreActors.vue'),
    meta: { requiresAuth: false, isPublic: true }
  },
  {
    path: '/explore/actors/:id',
    name: 'explore-actor-detail',
    component: () => import('@/views/public/ExploreActorDetail.vue'),
    meta: { requiresAuth: false, isPublic: true },
    props: true
  },
  {
    path: '/explore/conflicts',
    name: 'explore-conflicts',
    component: () => import('@/views/public/ExploreConflicts.vue'),
    meta: { requiresAuth: false, isPublic: true }
  },
  {
    path: '/explore/conflicts/:id',
    name: 'explore-conflict-detail',
    component: () => import('@/views/public/ExploreConflictDetail.vue'),
    meta: { requiresAuth: false, isPublic: true },
    props: true
  },
  {
    path: '/submit-tip',
    name: 'submit-tip',
    component: () => import('@/views/public/SubmitTip.vue'),
    meta: { requiresAuth: false, isPublic: true }
  },
  {
    path: '/about',
    name: 'about',
    component: () => import('@/views/public/About.vue'),
    meta: { requiresAuth: false, isPublic: true }
  },
  {
    path: '/contact',
    name: 'contact',
    component: () => import('@/views/public/Contact.vue'),
    meta: { requiresAuth: false, isPublic: true }
  },

  // ==========================================
  // AUTHENTICATED USER ROUTES
  // ==========================================
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
    path: '/events/create',
    name: 'event-create',
    component: () => import('@/views/EventCreate.vue'),
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
    path: '/reports',
    name: 'reports',
    component: () => import('@/views/Reports.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/timeline',
    name: 'timeline',
    component: () => import('@/views/Timeline.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/alerts',
    name: 'alerts',
    component: () => import('@/views/Alerts.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/settings',
    name: 'settings',
    component: () => import('@/views/Settings.vue'),
    meta: { requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'profile',
    component: () => import('@/views/Profile.vue'),
    meta: { requiresAuth: true }
  },

  // ==========================================
  // ADMIN ROUTES
  // ==========================================
  {
    path: '/admin/login',
    name: 'admin-login',
    component: () => import('@/views/admin/AdminLogin.vue'),
    meta: { requiresGuest: true, isAdminLogin: true }
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
        path: 'roles',
        name: 'admin-roles',
        component: () => import('@/views/admin/Roles.vue')
      },
      {
        path: 'permissions',
        name: 'admin-permissions',
        component: () => import('@/views/admin/Permissions.vue')
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
        path: 'achievements',
        name: 'admin-achievements',
        component: () => import('@/views/admin/Achievements.vue')
      },
      {
        path: 'agents',
        name: 'admin-agents',
        component: () => import('@/views/admin/Agents.vue')
      },
      {
        path: 'skills',
        name: 'admin-skills',
        component: () => import('@/views/admin/Skills.vue')
      },
      {
        path: 'tips',
        name: 'admin-tips',
        component: () => import('@/views/admin/Tips.vue')
      },
      {
        path: 'reports',
        name: 'admin-reports',
        component: () => import('@/views/admin/Reports.vue')
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

  // ==========================================
  // AUTH ROUTES
  // ==========================================
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
    path: '/forgot-password',
    name: 'forgot-password',
    component: () => import('@/views/auth/ForgotPassword.vue'),
    meta: { requiresGuest: true }
  },
  {
    path: '/reset-password/:token',
    name: 'reset-password',
    component: () => import('@/views/auth/ResetPassword.vue'),
    meta: { requiresGuest: true },
    props: true
  },

  // ==========================================
  // ERROR ROUTES
  // ==========================================
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

  // Require authentication for protected routes
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    // Redirect admin routes to admin login, others to regular login
    if (to.meta.requiresAdmin) {
      next({ name: 'admin-login' });
    } else {
      next({ name: 'login', query: { redirect: to.fullPath } });
    }
  }
  // Redirect guests from guest-only pages
  else if (to.meta.requiresGuest && authStore.isAuthenticated) {
    // Admin login page redirects to admin panel, regular login to dashboard
    if (to.meta.isAdminLogin && authStore.isAdmin) {
      next({ name: 'admin' });
    } else {
      next({ name: 'dashboard' });
    }
  }
  // Check admin privileges for admin routes
  else if (to.meta.requiresAdmin && !authStore.isAdmin) {
    next({ name: 'admin-login' });
  }
  else {
    next();
  }
});

export { routes };

export default router;
