/* ============================================================================
   reveal.js — scroll-triggered появления, счётчики, параллакс.
   На лёгких эффектах используем нативный IntersectionObserver (дёшево).
   GSAP/ScrollTrigger держим для pin/scrub (hero, этапы) — тяжёлая артиллерия.
   ============================================================================ */

import { prefersReducedMotion } from './smoothScroll.js';

/** Появление блоков и stagger-каскад дочерних элементов при входе в вьюпорт. */
export function initReveals() {
  const items = document.querySelectorAll('[data-reveal], [data-reveal-stagger]');
  if (prefersReducedMotion) { items.forEach((el) => el.classList.add('is-in')); return; }

  const io = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
      if (!e.isIntersecting) return;
      const el = e.target;
      // stagger: задаём задержку каждому ребёнку (30–50ms) через transition-delay
      if (el.hasAttribute('data-reveal-stagger')) {
        [...el.children].forEach((child, i) => { child.style.transitionDelay = `${i * 60}ms`; });
      }
      el.classList.add('is-in');
      io.unobserve(el);
    });
  }, { threshold: 0.15, rootMargin: '0px 0px -8% 0px' });

  items.forEach((el) => io.observe(el));
}

/** Анимированный count-up для блока цифр. data-count="200", data-suffix, data-prefix. */
export function initCounters() {
  const nums = document.querySelectorAll('[data-count]');
  if (!nums.length) return;

  const run = (el) => {
    const target = parseFloat(el.dataset.count);
    const dur = 1400;
    const prefix = el.dataset.prefix || '';
    const suffix = el.dataset.suffix || '';
    if (prefersReducedMotion) { el.textContent = prefix + target + suffix; return; }
    let start = null;
    const step = (ts) => {
      if (start === null) start = ts;
      const p = Math.min((ts - start) / dur, 1);
      const eased = 1 - Math.pow(1 - p, 3);          // easeOutCubic — набегает и тормозит
      const val = Math.round(target * eased);
      el.textContent = prefix + val.toLocaleString('ru-RU') + suffix;
      if (p < 1) requestAnimationFrame(step);
    };
    requestAnimationFrame(step);
  };

  const io = new IntersectionObserver((entries) => {
    entries.forEach((e) => { if (e.isIntersecting) { run(e.target); io.unobserve(e.target); } });
  }, { threshold: 0.5 });
  nums.forEach((el) => io.observe(el));
}

/** Лёгкий параллакс на data-parallax (доля смещения). Только desktop + не reduced. */
export function initParallax() {
  if (prefersReducedMotion || !window.ScrollTrigger) return;
  document.querySelectorAll('[data-parallax]').forEach((el) => {
    const amount = parseFloat(el.dataset.parallax) || 0.15;
    // eslint-disable-next-line no-undef
    gsap.to(el, {
      yPercent: amount * 100,
      ease: 'none',
      scrollTrigger: { trigger: el.closest('.section') || el, start: 'top bottom', end: 'bottom top', scrub: true },
    });
  });
}
