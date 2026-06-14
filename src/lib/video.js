/* ============================================================================
   video.js — ленивые фоновые видео перевозок (Экран 8 и врезки по странице).
   Правило ТЗ §8: muted, loop, playsinline, preload=none + постер, грузим при
   подходе к вьюпорту через IntersectionObserver. На мобильном — не автоплеим
   тяжёлое: показываем постер, видео стартует только когда реально видно.
   Разметка: <video data-src="..." poster="..." muted loop playsinline preload="none">
   ============================================================================ */

export function initLazyVideos() {
  const vids = document.querySelectorAll('video[data-src]');
  if (!vids.length) return;

  const io = new IntersectionObserver((entries) => {
    entries.forEach((e) => {
      const v = e.target;
      if (e.isIntersecting) {
        // подставляем src один раз
        if (!v.src && v.dataset.src) {
          v.src = v.dataset.src;
          v.load();
        }
        // автоплей только пока в зоне видимости (экономим батарею/CPU)
        const p = v.play();
        if (p && p.catch) p.catch(() => { /* автоплей заблокирован — остаётся постер, не критично */ });
      } else {
        if (!v.paused) v.pause();
      }
    });
  }, { threshold: 0.25, rootMargin: '100px 0px' });

  vids.forEach((v) => io.observe(v));
}
