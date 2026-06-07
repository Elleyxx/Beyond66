<template>
  <section class="home-about">
    <div class="about-container">
      <div class="about-content">
        <div class="about-left">
          <div class="about-header">
            <h2>{{ t('home.about.title') }}</h2>
          </div>
          <div class="about-copy">
            <p>{{ t('home.about.paragraphOne') }}</p>
            <p>{{ t('home.about.paragraphTwo') }}</p>
          </div>
        </div>

        <figure class="about-image-wrap">
          <img src="/assets/images/map.png" :alt="t('home.about.mapAlt')" />
        </figure>
      </div>
    </div>
  </section>
</template>

<script setup>
import { useI18n } from 'vue-i18n'

const { t } = useI18n()
</script>

<style scoped>
.home-about {
  position: relative;
  padding: 0 8vw 110px;
  background: transparent;
  overflow: hidden;
}

.about-container {
  position: relative;
  z-index: 2;
  padding: 28px;
}

.about-content {
  display: grid;
  grid-template-columns: 6fr 4fr;
  gap: 34px;
  align-items: start;
}

.about-left {
  display: flex;
  flex-direction: column;
}

.about-header {
  margin-bottom: 34px;
}

.about-header h2 {
  font-size: clamp(4.5rem, 9vw, 8rem);
  line-height: 0.8;
  color: rgba(var(--v-theme-on-surface), 0.35);
  font-weight: 900;
  margin: 0;
  padding: 0 0 24px;
}

.about-copy p {
  margin: 0 25px 0 0;
  padding: 0 0 24px;
  color: rgb(var(--v-theme-text));
  line-height: 1.8;
  font-size: 1.1rem;
  text-align: justify;
  font-family: 'Inter', sans-serif;
}

.about-image-wrap {
  position: relative;
  margin: 0;
  overflow: visible;
  padding: 18px;
}

.about-image-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

/* top-left orange corner */
.about-image-wrap::before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 160px;
  height: 140px;
  border-top: 6px solid rgb(var(--v-theme-primary));
  border-left: 6px solid rgb(var(--v-theme-primary));
  z-index: 2;
}

/* bottom-right orange corner */
.about-image-wrap::after {
  content: "";
  position: absolute;
  right: 0;
  bottom: 0;
  width: 160px;
  height: 140px;
  border-right: 6px solid rgb(var(--v-theme-primary));
  border-bottom: 6px solid rgb(var(--v-theme-primary));
  z-index: 2;
}

@media (max-width: 1250px) {
  .home-about {
    padding: 70px 6vw 90px;
  }

  .about-container {
    padding: 20px;
  }

  .about-content {
    display: flex;
    flex-direction: column;
    gap: 24px;
  }

  .about-header {
    order: 1;
    margin-bottom: 0;
    text-align: left;
  }

  .about-image-wrap {
    order: 2;
    width: min(100%, 760px);
    margin: 0 auto;
    padding: 14px;
  }

  .about-copy {
    order: 3;
  }

  .about-copy p {
    margin-right: 0;
  }
}

@media (max-width: 900px) {
  .home-about {
    padding: 60px 5vw 80px;
  }

  .about-container {
    padding: 12px;
  }

  .about-content {
    gap: 20px;
  }

  .about-image-wrap {
    width: min(100%, 560px);
    padding: 12px;
  }

  .about-header h2 {
    font-size: clamp(3.8rem, 10vw, 6rem);
    padding-bottom: 18px;
  }

  .about-copy p {
    font-size: 1rem;
    line-height: 1.75;
    padding-bottom: 18px;
  }

  .about-image-wrap::before {
    width: 130px;
    height: 110px;
  }

  .about-image-wrap::after {
    width: 130px;
    height: 110px;
  }
}

@media (max-width: 600px) {
  .home-about {
    padding: 50px 20px 70px;
  }

  .about-container {
    padding: 0;
  }

  .about-content {
    gap: 16px;
  }

  .about-header {
    text-align: left;
  }

  .about-header h2 {
    font-size: clamp(2.8rem, 12vw, 4.2rem);
    padding-bottom: 12px;
  }

  .about-image-wrap {
    width: min(100%, 360px);
    padding: 10px;
  }

  .about-copy p {
    font-size: 0.92rem;
    line-height: 1.65;
    padding-bottom: 16px;
    text-align: left;
  }

  .about-image-wrap::before {
    width: 80px;
    height: 70px;
    border-top-width: 4px;
    border-left-width: 4px;
  }

  .about-image-wrap::after {
    width: 80px;
    height: 70px;
    border-right-width: 4px;
    border-bottom-width: 4px;
  }
}
</style>
