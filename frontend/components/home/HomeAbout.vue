<template>
  <section class="home-about">
    <div class="about-copy">
      <p class="eyebrow"><i class="bi bi-snow2"></i> BEYOND 66°</p>
      <h2>Plan a Nordic route around the places you actually want to feel.</h2>
      <p>
        Move between fjords, forests, cities, glacier lagoons, and Arctic light with one
        calm place to explore countries, compare destinations, and start building your trip.
      </p>
    </div>

    <div class="about-stats" aria-label="Nordic travel highlights">
      <article>
        <strong>5</strong>
        <span>Nordic countries</span>
      </article>
      <article>
        <strong>60+</strong>
        <span>Featured places</span>
      </article>
      <article>
        <strong>66°</strong>
        <span>Arctic inspiration</span>
      </article>
    </div>
  </section>
</template>

<style scoped>
.home-about {
  display: grid;
  grid-template-columns: minmax(0, 1.15fr) minmax(280px, 0.85fr);
  gap: 48px;
  align-items: center;
  padding: 88px 8vw 110px;
  background: rgb(var(--v-theme-background));
}

.about-copy {
  max-width: 760px;
}

.eyebrow {
  margin: 0 0 14px;
  color: rgb(var(--v-theme-primary));
  font-size: 0.78rem;
  font-weight: 800;
  letter-spacing: 0.18em;
}

.about-copy h2 {
  margin: 0;
  max-width: 720px;
  color: rgb(var(--v-theme-text));
  font-size: clamp(2rem, 4rem, 4rem);
  line-height: 1.02;
  font-weight: 900;
}

.about-copy p:last-child {
  max-width: 620px;
  margin: 24px 0 0;
  color: rgb(var(--v-theme-muted));
  line-height: 1.75;
}

.about-stats {
  display: grid;
  gap: 14px;
}

.about-stats article {
  display: flex;
  align-items: baseline;
  justify-content: space-between;
  gap: 24px;
  padding: 22px 0;
  border-bottom: 1px solid rgba(var(--v-theme-text), 0.16);
}

.about-stats strong {
  color: rgb(var(--v-theme-primary));
  font-size: 2.4rem;
  line-height: 1;
}

.about-stats span {
  color: rgb(var(--v-theme-text));
  font-weight: 700;
  text-align: right;
}

@media (max-width: 820px) {
  .home-about {
    grid-template-columns: 1fr;
    padding: 64px 24px 80px;
  }

  .about-copy h2 {
    font-size: 2.4rem;
  }
}
</style>
