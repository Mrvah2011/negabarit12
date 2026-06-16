/* ============================================================================
   main.js — точка сборки. Поднимает smooth-scroll, секвенцию hero,
   pinned-этапы, reveal-анимации, формы, квиз, видео, UI.
   Порядок важен: smooth-scroll и ScrollTrigger — до любых pin/scrub.
   ============================================================================ */

import { initSmoothScroll, isMobile, prefersReducedMotion, saveData, getLenis } from './lib/smoothScroll.js';
import { HeroSequence } from './lib/sequence.js';
import { initReveals, initCounters, initParallax } from './lib/reveal.js';
import { initForms } from './lib/forms.js';
import { initQuiz } from './lib/quiz.js';
import { initLazyVideos } from './lib/video.js';
import { initUI } from './lib/ui.js';

// debug-флаг для аналитики в консоли (?debug в URL)
if (new URLSearchParams(location.search).has('debug')) window.__DEBUG_ANALYTICS__ = true;

// --- Конфиг hero-секвенции. СВОПАБЕЛЬНО: чтобы наложить другой клип —
//     перегенерировать кадры в эту папку и поменять count. Разметку не трогать.
//     Desktop — горизонтальный набор (150 кадров). Mobile — облегчённый
//     вертикальный (60 кадров, ~0.9 МБ): эффект есть, слабый телефон тянет.
const HERO_SEQ_DESKTOP = { path: 'assets/sequence/hero/hero_',   count: 150, pad: 4, ext: '.webp' };
const HERO_SEQ_MOBILE  = { path: 'assets/sequence/hero-m/hero_', count: 60,  pad: 4, ext: '.webp' };
const HERO_SEQ = isMobile() ? HERO_SEQ_MOBILE : HERO_SEQ_DESKTOP;

function registerGsap() {
  if (window.gsap && window.ScrollTrigger) {
    // eslint-disable-next-line no-undef
    gsap.registerPlugin(ScrollTrigger);
    window.ScrollTrigger = ScrollTrigger; // для модулей, читающих window.ScrollTrigger
    return true;
  }
  return false;
}

/* --- HERO: pin + scrub canvas-секвенции --- */
async function initHero() {
  const section = document.getElementById('hero');
  const canvas = document.getElementById('hero-canvas');
  if (!section || !canvas) return;

  const seq = new HeroSequence({
    canvas,
    section,
    path: HERO_SEQ.path,
    count: HERO_SEQ.count,
    pad: HERO_SEQ.pad,
    ext: HERO_SEQ.ext,
    preloader: document.getElementById('preloader'),
    bar: document.querySelector('#preloader .preloader__bar-fill'),
    percent: document.getElementById('preloader-pct'),
  });

  const { mode } = await seq.init();

  // Pin + scrub только в режиме секвенции (desktop). На постере — обычный скролл.
  if (mode === 'sequence' && registerGsap() && !prefersReducedMotion) {
    const sticky = section.querySelector('.hero__sticky');
    // eslint-disable-next-line no-undef
    gsap.timeline({
      scrollTrigger: {
        trigger: section,
        start: 'top top',
        end: '+=100%',          // длина pinned-зоны hero (~1 экран — не «застреваем»)
        pin: sticky,
        scrub: 0.4,             // отзывчивее, меньше залипания при быстром скролле
        onUpdate: (self) => seq.renderAtProgress(self.progress),
      },
    })
      // лёгкий fade текста к концу проезда (не мешает читать в начале)
      .to('.hero__content', { opacity: 0, y: -30, ease: 'none' }, 0.7);
  }
}

/* --- ЭТАПЫ РАБОТЫ: sticky scrollytelling (Экран 7) ---
   Desktop: правая колонка липнет (CSS position:sticky), активный шаг определяет
   IntersectionObserver — тот, что пересёк центр экрана. БЕЗ GSAP-pin (он давал
   overlay-баг с «Автопарком»). Mobile: обычный вертикальный список (CSS). */
function initProcess() {
  const section = document.getElementById('process');
  if (!section) return;
  if (isMobile() || prefersReducedMotion) return; // mobile/reduced — простой список

  const stepsEls = [...section.querySelectorAll('.process__step')];
  const artefacts = [...section.querySelectorAll('.process__art')];
  const indicators = [...section.querySelectorAll('.process__dot')];
  if (!stepsEls.length) return;

  let activeIdx = -1;
  const setActive = (i) => {
    if (i === activeIdx) return;          // не дёргаем DOM, если шаг не сменился
    activeIdx = i;
    stepsEls.forEach((s, idx) => s.classList.toggle('is-active', idx === i));
    artefacts.forEach((a, idx) => a.classList.toggle('is-active', idx === i));
    indicators.forEach((d, idx) => d.classList.toggle('is-active', idx === i));
  };

  // активный = шаг, чей центр ближе всего к центру вьюпорта. Считаем на каждый скролл —
  // всегда ровно один активный, без «дыр» между шагами.
  const pick = () => {
    const mid = window.innerHeight / 2;
    let best = 0, bestDist = Infinity;
    stepsEls.forEach((s, i) => {
      const r = s.getBoundingClientRect();
      const d = Math.abs(r.top + r.height / 2 - mid);
      if (d < bestDist) { bestDist = d; best = i; }
    });
    setActive(best);
  };
  setActive(0);

  // rAF-троттл: getBoundingClientRect×7 не на каждое событие скролла, а раз в кадр
  let ticking = false;
  const onScroll = () => { if (ticking) return; ticking = true; requestAnimationFrame(() => { pick(); ticking = false; }); };
  window.addEventListener('scroll', onScroll, { passive: true });   // Lenis двигает реальный scrollTop → событие летит
  const lenis = getLenis();
  if (lenis) lenis.on('scroll', onScroll);                          // + плавный скролл Lenis
  window.addEventListener('resize', onScroll, { passive: true });
  pick();
}

/* --- Bootstrap --- */
function boot() {
  initSmoothScroll();
  registerGsap();

  initHero();          // async, сам себя ждёт
  initUI();
  initQuiz();
  initForms();
  initReveals();
  initCounters();
  initLazyVideos();
  initProcess();       // сам отсеивает mobile/reduced; sticky — не требует ScrollTrigger

  if (!isMobile() && !prefersReducedMotion && !saveData) {
    initParallax();
  }

  // пересчёт триггеров после полной загрузки ассетов (шрифты/картинки меняют высоты)
  window.addEventListener('load', () => window.ScrollTrigger && window.ScrollTrigger.refresh());
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
else boot();
