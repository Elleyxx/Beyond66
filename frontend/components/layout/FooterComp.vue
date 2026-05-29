<template>
  <footer class="nordic-footer">
    <v-container fluid class="footer-content px-6 px-md-10 px-lg-14">
        <v-row class="footer-top" dense>
          <v-col cols="12" sm="6" md="3">
            <h4 class="footer-title">EXPLORE</h4>
            <div class="footer-line"></div>
            <ul class="footer-list">
              <li><router-link to="/country/norway">Norway</router-link></li>
              <li><router-link to="/country/sweden">Sweden</router-link></li>
              <li><router-link to="/country/finland">Finland</router-link></li>
              <li><router-link to="/country/iceland">Iceland</router-link></li>
              <li><router-link to="/country/denmark">Denmark</router-link></li>
            </ul>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <h4 class="footer-title">PLANNER</h4>
            <div class="footer-line"></div>
            <ul class="footer-list">
              <li><router-link to="/trip-planner">Journey Planner</router-link></li>
              <li><router-link to="/dashboard">My Trips</router-link></li>
              <li><router-link to="/dashboard">Saved Places</router-link></li>
              <li><router-link to="/trip-planner">Travel Tips</router-link></li>
            </ul>
          </v-col>

          <v-col cols="12" sm="6" md="3">
            <h4 class="footer-title">COMMUNITY</h4>
            <div class="footer-line"></div>
            <ul class="footer-list">
              <li><router-link to="/community">Community Feed</router-link></li>
              <li><router-link to="/community">Travel Stories</router-link></li>
              <li><router-link to="/community">Photos & Guides</router-link></li>
              <li><router-link to="/community">Community Rules</router-link></li>
            </ul>
          </v-col>

          <v-col cols="12" sm="6" md="3" class="brand-section">
            <h2 class="brand-logo">BEYOND 66°</h2>
            <div class="footer-line brand-line"></div>

            <p>Arctic Circle of Exploration</p>
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
          <p>© 2026 Beyond 66°. All rights reserved.</p>

          <div class="bottom-links">
            <a href="#" @click.prevent>Privacy Policy</a>
            <a href="#" @click.prevent>Terms & Conditions</a>
            <a href="#" @click.prevent>Cookie Policy</a>
          </div>

          <p>Made for Nordic explorers</p>
        </div>
    </v-container>
  </footer>
</template>

<script setup>
</script>

<style scoped>
.nordic-footer {
  position: relative;
  isolation: isolate;
  min-height: 560px;
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


