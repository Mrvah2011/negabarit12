/* ============================================================================
   main.js — точка сборки. Поднимает smooth-scroll, секвенцию hero,
   pinned-этапы, reveal-анимации, формы, квиз, видео, UI.
   Порядок важен: smooth-scroll и ScrollTrigger — до любых pin/scrub.
   ============================================================================ */

import { initSmoothScroll, isMobile, prefersReducedMotion, saveData } from './lib/smoothScroll.js';
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
const HERO_SEQ = {
  path: 'assets/sequence/hero/hero_',
  count: 150,
  pad: 4,
  ext: '.webp',
};

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

/* --- ЭТАПЫ РАБОТЫ: pinned scrollytelling (Экран 7) ---
   Desktop: секция пинится, шаги сменяются по прогрессу, артефакт справа.
   Mobile: деградация в обычный вертикальный список карточек (reveal). */
function initProcess() {
  const section = document.getElementById('process');
  if (!section || !window.ScrollTrigger) return;
  if (isMobile() || prefersReducedMotion) return; // мобильная деградация — список (CSS)

  const stepsEls = [...section.querySelectorAll('.process__step')];
  const artefacts = [...section.querySelectorAll('.process__art')];
  const indicators = [...section.querySelectorAll('.process__dot')];
  const n = stepsEls.length;
  if (!n) return;

  const setActive = (i) => {
    stepsEls.forEach((s, idx) => s.classList.toggle('is-active', idx === i));
    artefacts.forEach((a, idx) => a.classList.toggle('is-active', idx === i));
    indicators.forEach((d, idx) => d.classList.toggle('is-active', idx === i));
  };
  setActive(0);

  // eslint-disable-next-line no-undef
  ScrollTrigger.create({
    trigger: section,
    start: 'top top',
    end: `+=${n * 100}%`,        // по экрану на шаг
    pin: section.querySelector('.process__pin'),
    scrub: true,
    onUpdate: (self) => {
      const i = Math.min(n - 1, Math.floor(self.progress * n));
      setActive(i);
    },
  });
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

  if (!isMobile() && !prefersReducedMotion && !saveData) {
    initParallax();
    initProcess();
  }

  // пересчёт триггеров после полной загрузки ассетов (шрифты/картинки меняют высоты)
  window.addEventListener('load', () => window.ScrollTrigger && window.ScrollTrigger.refresh());
}

if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
else boot();
