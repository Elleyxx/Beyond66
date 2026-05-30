<template>
  <section class="home-about">
    <div class="about-container">
      <div class="about-content">
        <div class="about-left">
          <div class="about-header">
            <h2>ABOUT</h2>
          </div>

          <div class="about-copy">
            <p>
              Beyond 66 is a Nordic travel discovery platform designed to inspire and guide travelers through the breathtaking landscapes, rich cultures, and unique experiences of Northern Europe. Covering Iceland, Norway, Sweden, Finland, and Denmark, the platform provides destination guides, travel insights, and curated recommendations to help users explore the beauty and diversity of the Nordic region.
            </p>
            <p>
              Beyond destination exploration, Beyond 66 offers personalized trip planning tools that help users create meaningful travel experiences tailored to their interests and preferences. Whether you are seeking the northern lights, outdoor adventures, cultural landmarks, or local Nordic traditions, this platform serves as a trusted companion throughout your journey.
            </p>
          </div>
        </div>

        <figure class="about-image-wrap">
          <img src="/assets/images/map.png" alt="Nordic map and destination highlights" />
        </figure>
      </div>
    </div>
  </section>
</template>

<style scoped>
.home-about {
  position: relative;
  padding: 90px 8vw 110px;
  background: transparent;
  overflow: hidden;
}

.about-container {
  position: relative;
  z-index: 2;
  padding: 28px;
  backdrop-filter: blur(10px);
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
  color: rgba(var(--v-theme-on-surface), 0.08);
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
}

.about-image-wrap {
  margin: 0;
  overflow: hidden;
  box-shadow: 0 20px 44px rgba(var(--v-theme-background), 0.22);
}

.about-image-wrap img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  display: block;
}

@media (max-width: 820px) {
  .home-about {
    padding: 64px 24px 80px;
  }

  .about-container {
    padding: 20px;
  }

  .about-content {
    grid-template-columns: 1fr;
  }

  .about-image-wrap img {
    min-height: 280px;
  }
}
</style>
