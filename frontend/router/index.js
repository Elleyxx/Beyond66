import { createRouter, createWebHistory } from 'vue-router'
import Landing from '../views/LandingPage.vue'
import HomeView from '../views/HomePage.vue'
import LoginView from '../views/LoginPage.vue'
import RegisterView from '../views/RegisterPage.vue'
import ExploreView from '../views/ExplorePage.vue'
import NorwayView from '../views/NorwayPage.vue'
import SwedenView from '../views/SwedenPage.vue'
import FinlandView from '../views/FinlandPage.vue'
import IcelandView from '../views/IcelandPage.vue'
import DenmarkView from '../views/DenmarkPage.vue'
import ForumView from '../views/CommunityPage.vue'
import CommunityPostDetailView from '../views/CommunityPostDetail.vue'
import ProfileView from '../views/Profile.vue'
import ItineraryView from '../views/PlannerPage.vue'
import SearchView from '../views/SearchPage.vue'
import { isAuthenticated } from '../utils/auth'

const router = createRouter({
  history: createWebHistory(),
  scrollBehavior(to, from, savedPosition) {
    if (to.name === 'trip-planner') {
      return { top: 0, left: 0, behavior: 'auto' }
    }

    if (savedPosition) {
      return savedPosition
    }

    return { top: 0, left: 0, behavior: 'auto' }
  },
  routes: [
    { path: '/', name: 'landing', component: Landing, meta: { hideLayout: true } },
    { path: '/home', name: 'home', component: HomeView },
    { path: '/login', name: 'login', component: LoginView, meta: { hideLayout: true } },
    { path: '/register', name: 'register', component: RegisterView, meta: { hideLayout: true } },
    { path: '/explore', name: 'explore', component: ExploreView },
    { path: '/country/norway', name: 'country-norway', component: NorwayView },
    { path: '/country/sweden', name: 'country-sweden', component: SwedenView },
    { path: '/country/finland', name: 'country-finland', component: FinlandView },
    { path: '/country/iceland', name: 'country-iceland', component: IcelandView },
    { path: '/country/denmark', name: 'country-denmark', component: DenmarkView },
    { path: '/search', name: 'search', component: SearchView },
    { path: '/community', name: 'community', component: ForumView, meta: { requiresAuth: true } },
    { path: '/community/:id', name: 'community-post-detail', component: CommunityPostDetailView, meta: { requiresAuth: true } },
    { path: '/profile', name: 'profile', component: ProfileView, meta: { requiresAuth: true } },
    { path: '/dashboard', redirect: '/profile' },
    { path: '/trip-planner', name: 'trip-planner', component: ItineraryView, meta: { requiresAuth: true } },
  ],
})

router.beforeEach((to) => {
  const isLoggedIn = isAuthenticated()
  const isAuthPage = to.path === '/login' || to.path === '/register'
  const requiresAuth = to.matched.some((route) => route.meta.requiresAuth)

  if (!isLoggedIn && requiresAuth) {
    return {
      path: '/login',
      query: { redirect: to.fullPath },
    }
  }

  if (isLoggedIn && isAuthPage) {
    return '/home'
  }

  return true
})

export default router
