<template>
  <footer class="nordic-footer" :class="{ 'nordic-footer-home': isHome }">
    <v-container fluid class="footer-content px-6 px-md-10 px-lg-14">
      <v-row class="footer-top" dense>
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

@media (max-width: 960px) {
  .footer-content {
    min-height: 620px;
    padding-top: 0;
  }

  .brand-section {
    border-left: none;
    padding-left: 0;
    padding-top: 8px;
  }
}

@media (max-width: 600px) {
  .footer-content {
    min-height: 520px;
    padding-top: 0;
    padding-bottom: 10px;
  }

  .footer-title {
    font-size: 0.86rem;
  }

  .brand-logo {
    font-size: 1.05rem;
    letter-spacing: 0.18em;
  }

  .footer-bottom {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  .bottom-links {
    gap: 10px 16px;
  }
}
</style>
