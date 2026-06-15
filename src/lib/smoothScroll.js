/* ============================================================================
   smoothScroll.js — плавный скролл (Lenis) + связка с GSAP ScrollTrigger.
   ВАЖНО: ScrollTrigger должен читать позицию из Lenis, иначе скраб секвенции
   и pinned-блоки рассинхронятся. Поэтому связываем их один раз тут.
   На prefers-reduced-motion инерцию отключаем — страница скроллится нативно.
   ============================================================================ */

export const prefersReducedMotion =
  window.matchMedia('(prefers-reduced-motion: reduce)').matches;

export const isMobile = () => window.matchMedia('(max-width: 768px)').matches;

// Save-Data / медленная сеть — повод деградировать тяжёлые эффекты.
export const saveData =
  (navigator.connection && navigator.connection.saveData) || false;

let lenis = null;

export function initSmoothScroll() {
  if (prefersReducedMotion) return null;          // уважаем системную настройку

  // eslint-disable-next-line no-undef
  lenis = new Lenis({
    // lerp вместо duration — колесо ощущается отзывчивее (меньше «ваты»), без залипания
    lerp: 0.12,
    wheelMultiplier: 1.1,
    smoothWheel: true,
    // тач оставляем нативным: на мобильном инерционный скролл системы лучше и не лагает
    smoothTouch: false,
  });

  // Синхронизация Lenis ↔ ScrollTrigger
  lenis.on('scroll', () => window.ScrollTrigger && window.ScrollTrigger.update());
  // eslint-disable-next-line no-undef
  gsap.ticker.add((time) => lenis.raf(time * 1000)); // Lenis тикает от GSAP — один rAF на всё
  // eslint-disable-next-line no-undef
  gsap.ticker.lagSmoothing(0);

  return lenis;
}

/** Плавный скролл к элементу по селектору (для CTA и якорей). */
export function scrollToEl(target, offset = -10) {
  const el = typeof target === 'string' ? document.querySelector(target) : target;
  if (!el) return;
  if (lenis) lenis.scrollTo(el, { offset, duration: 1.2 });
  else el.scrollIntoView({ behavior: prefersReducedMotion ? 'auto' : 'smooth', block: 'start' });
}

export function getLenis() { return lenis; }
