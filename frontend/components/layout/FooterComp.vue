<template>
  <footer class="nordic-footer" :class="{ 'nordic-footer-home': isHome }">
    <v-container fluid class="footer-content px-6 px-md-10 px-lg-14">
      <v-row class="footer-top footer-desktop" dense>
        <v-col cols="12" sm="6" md="3">
          <h4 class="footer-title">{{ t('footer.explore') }}</h4>
          <div class="footer-line"></div>
          <ul class="footer-list">
            <li><router-link to="/country/norway">{{ t('countryNames.norway') }}</router-link></li>
            <li><router-link to="/country/sweden">{{ t('countryNames.sweden') }}</router-link></li>
            <li><router-link to="/country/finland">{{ t('countryNames.finland') }}</router-link></li>
            <li><router-link to="/country/iceland">{{ t('countryNames.iceland') }}</router-link></li>
            <li><router-link to="/country/denmark">{{ t('countryNames.denmark') }}</router-link></li>
          </ul>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <h4 class="footer-title">{{ t('footer.planner') }}</h4>
          <div class="footer-line"></div>
          <ul class="footer-list">
            <li><router-link to="/trip-planner">{{ t('footer.journeyPlanner') }}</router-link></li>
            <li><router-link to="/dashboard">{{ t('footer.myTrips') }}</router-link></li>
            <li><router-link to="/dashboard">{{ t('footer.savedPlaces') }}</router-link></li>
            <li><router-link to="/trip-planner">{{ t('footer.travelTips') }}</router-link></li>
          </ul>
        </v-col>

        <v-col cols="12" sm="6" md="3">
          <h4 class="footer-title">{{ t('footer.community') }}</h4>
          <div class="footer-line"></div>
          <ul class="footer-list">
            <li><router-link to="/community">{{ t('footer.communityFeed') }}</router-link></li>
            <li><router-link to="/community">{{ t('footer.travelStories') }}</router-link></li>
            <li><router-link to="/community">{{ t('footer.photosGuides') }}</router-link></li>
            <li><router-link to="/community">{{ t('footer.communityRules') }}</router-link></li>
          </ul>
        </v-col>

        <v-col cols="12" sm="6" md="3" class="brand-section">
          <h2 class="brand-logo">{{ t('brand.name') }}</h2>
          <div class="footer-line brand-line"></div>

          <p>{{ t('brand.tagline') }}</p>
          <p>hello@beyond66.com</p>
          <p>+60 12-345 6789</p>

          <div class="socials mt-3">
            <v-btn icon variant="text" class="social-btn" aria-label="Facebook">
              <v-icon>mdi-facebook</v-icon>
            </v-btn>
            <v-btn icon variant="text" class="social-btn" aria-label="Instagram">
              <v-icon>mdi-instagram</v-icon>
            </v-btn>
            <v-btn icon variant="text" class="social-btn" aria-label="Twitter">
              <v-icon>mdi-twitter</v-icon>
            </v-btn>
          </div>
        </v-col>
      </v-row>

      <div class="footer-mobile">
        <h2 class="mobile-brand-logo">{{ t('brand.name') }}</h2>

        <div class="mobile-links">
          <router-link to="/explore">
            {{ t('footer.explore') }}
          </router-link>

          <router-link to="/trip-planner">
            {{ t('footer.planner') }}
          </router-link>

          <router-link to="/community">
            {{ t('footer.community') }}
          </router-link>
        </div>
      </div>

      <div class="footer-divider"></div>

      <div class="footer-bottom">
        <p>{{ t('footer.copyright') }}</p>

        <div class="bottom-links">
          <a href="#" @click.prevent>{{ t('footer.privacy') }}</a>
          <a href="#" @click.prevent>{{ t('footer.terms') }}</a>
          <a href="#" @click.prevent>{{ t('footer.cookie') }}</a>
        </div>

        <p>{{ t('footer.madeFor') }}</p>
      </div>
    </v-container>
  </footer>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

defineProps({
  isHome: {
    type: Boolean,
    default: false,
  },
})

const { t } = useI18n()
</script>

<style scoped>
.nordic-footer {
  position: relative;
  isolation: isolate;
  min-height: 550px;
  background: rgb(var(--v-theme-background));
}

.nordic-footer::before {
  content: '';
  position: absolute;
  inset: 0;
  z-index: 0;
  background-image: url('@/assets/images/glacier_footer.png');
  background-size: cover;
  background-position: center center;
  background-repeat: no-repeat;
}

.nordic-footer::after {
  content: '';
  position: absolute;
  inset: 0;
  z-index: 1;
  background:
    linear-gradient(
      to bottom,
      rgba(0, 0, 0, 0) 0%,
      rgba(0, 0, 0, 0.25) 25%,
      rgba(0, 0, 0, 0.82) 62%,
      rgba(0, 0, 0, 0.96) 100%
    );
}

.nordic-footer-home {
  background: transparent;
  margin-top: -1px;
}

.footer-overlay,
.footer-content {
  position: relative;
  z-index: 2;
}

.footer-content {
  min-height: 560px;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  padding-top: 500px;
  padding-bottom: 12px;
}

.footer-top {
  row-gap: 24px;
  margin-top: auto;
  margin-bottom: 24px;
}

.footer-title {
  font-size: 0.92rem;
  letter-spacing: 0.22em;
  font-weight: 700;
  color: rgb(var(--v-theme-footer));
  margin: 0;
}

.footer-line {
  width: 52px;
  height: 2px;
  background: rgb(var(--v-theme-primary));
  margin: 12px 0 18px;
}

.footer-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.footer-list li {
  margin-bottom: 12px;
}

.footer-list a {
  color: rgba(var(--v-theme-footer), 0.82);
  text-decoration: none;
  transition: color 0.2s ease, transform 0.2s ease;
  display: inline-block;
}

.footer-list a:hover {
  color: rgb(var(--v-theme-accent));
  transform: translateX(3px);
}

.brand-section {
  border-left: 1px solid rgba(var(--v-theme-footer), 0.2);
  padding-left: 30px;
}

.brand-logo {
  margin: 0;
  letter-spacing: 0.24em;
  font-size: 1.2rem;
  font-weight: 800;
  color: rgb(var(--v-theme-footer));
}

.brand-line {
  width: 76px;
}

.brand-section p {
  margin: 0 0 8px;
  color: rgba(var(--v-theme-footer), 0.82);
  font-size: 0.95rem;
}

.socials {
  display: flex;
  gap: 8px;
}

.social-btn {
  color: rgba(var(--v-theme-footer), 0.92) !important;
  background: rgba(var(--v-theme-on-surface), 0.07);
}

.social-btn:hover {
  color: rgb(var(--v-theme-accent)) !important;
  background: rgba(var(--v-theme-on-surface), 0.14);
}

.footer-divider {
  height: 1px;
  background: rgba(var(--v-theme-on-surface), 0.2);
  margin: 18px 0 8px;
}

.footer-bottom {
  display: flex;
  justify-content: space-between;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
  color: rgba(var(--v-theme-footer), 0.72);
  font-size: 0.88rem;
}

.footer-bottom p {
  margin: 0;
}

.bottom-links {
  display: flex;
  gap: 20px;
  flex-wrap: wrap;
}

.bottom-links a {
  color: rgba(var(--v-theme-footer), 0.72);
  text-decoration: none;
  transition: color 0.2s ease;
}

.bottom-links a:hover {
  color: rgb(var(--v-theme-accent));
}

.footer-mobile {
  display: none;
}

.mobile-brand-logo {
  margin: 0;
  text-align: center;
  color: rgb(var(--v-theme-footer));
  font-size: 1.35rem;
  font-weight: 800;
  letter-spacing: 0.22em;
}

.mobile-links {
  display: flex;
  justify-content: center;
  gap: 36px;
  margin-top: 20px;
}

.mobile-links a {
  color: rgb(var(--v-theme-footer));
  text-decoration: none;
  font-size: 0.95rem;
  font-weight: 700;
  letter-spacing: 0.12em;
  text-transform: uppercase;
  transition: opacity 0.2s ease;
}

.mobile-links a:hover {
  opacity: 0.8;
}

@media (max-width: 1250px) {
  .nordic-footer {
    min-height: 640px;
  }

  .nordic-footer::after {
    background:
      linear-gradient(
        to bottom,
        rgba(0, 0, 0, 0.05) 0%,
        rgba(0, 0, 0, 0.35) 35%,
        rgba(0, 0, 0, 0.86) 68%,
        rgba(0, 0, 0, 0.98) 100%
      );
  }

  .footer-content {
    min-height: 640px;
    padding-top: 220px;
    padding-bottom: 14px;
    justify-content: flex-end;
  }

  .brand-section {
    border-left: none;
    padding-left: 0;
    padding-top: 8px;
  }
}

@media (max-width: 900px) {
  .nordic-footer {
    min-height: 520px;
  }

  .footer-content {
    min-height: 520px;
    padding-top: 220px;
    padding-bottom: 18px;
    justify-content: flex-end;
  }

  .footer-desktop {
    display: none;
  }

  .footer-mobile {
    display: block;
    margin-bottom: 28px;
  }

  .footer-bottom {
    justify-content: center;
    text-align: center;
    gap: 14px;
  }

  .bottom-links {
    justify-content: center;
  }
}

/* =========================
   <=600px
========================= */

@media (max-width: 600px) {
  .nordic-footer {
    min-height: 430px;
  }

  .footer-content {
    min-height: 430px;
    padding-top: 160px;
    padding-bottom: 12px;
  }

  .mobile-brand-logo {
    font-size: 1.05rem;
    letter-spacing: 0.18em;
  }

  .mobile-links {
    gap: 18px;
    flex-wrap: wrap;
  }

  .mobile-links a {
    font-size: 0.78rem;
  }

  .footer-bottom {
    flex-direction: column;
    align-items: center;
    gap: 10px;
    font-size: 0.8rem;
  }
}
</style>
