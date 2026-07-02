/* ============================================================================
   carousel.js — стрелки ◀ ▶ для горизонтальных лент (автопарк, видео).
   Свайп остаётся (нативный overflow-x). Стрелки — доп. привычный способ.
   Кнопки инжектятся в обёртку .carousel вокруг ленты; скролл на «страницу».
   Стрелки прячутся у краёв (нечего листать).
   ============================================================================ */

function initTrack(track) {
  // оборачиваем ленту в .carousel (position: relative), чтобы позиционировать стрелки
  const wrap = document.createElement('div');
  wrap.className = 'carousel';
  track.parentNode.insertBefore(wrap, track);
  wrap.appendChild(track);

  const mkBtn = (dir, cls, label, glyph) => {
    const b = document.createElement('button');
    b.className = 'carousel__arrow ' + cls;
    b.setAttribute('aria-label', label);
    b.innerHTML = glyph;
    b.addEventListener('click', () => {
      const step = Math.max(track.clientWidth * 0.85, 260);
      track.scrollBy({ left: dir * step, behavior: 'smooth' });
    });
    wrap.appendChild(b);
    return b;
  };
  const prev = mkBtn(-1, 'carousel__arrow--prev', 'Назад', '&#8249;');
  const next = mkBtn(1, 'carousel__arrow--next', 'Вперёд', '&#8250;');

  // показываем/прячем стрелки по позиции скролла
  const update = () => {
    const max = track.scrollWidth - track.clientWidth - 2;
    prev.classList.toggle('is-hidden', track.scrollLeft <= 2);
    next.classList.toggle('is-hidden', track.scrollLeft >= max);
    // если листать нечего (всё влезло) — прячем обе
    const fits = track.scrollWidth <= track.clientWidth + 4;
    prev.classList.toggle('is-off', fits);
    next.classList.toggle('is-off', fits);
  };
  track.addEventListener('scroll', () => requestAnimationFrame(update), { passive: true });
  window.addEventListener('resize', update, { passive: true });
  // пересчёт после загрузки медиа (меняют ширину)
  window.addEventListener('load', update);
  update();
}

export function initCarousels() {
  document.querySelectorAll('.autopark__track, .video-strip, .gallery').forEach(initTrack);
}
