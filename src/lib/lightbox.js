/* ============================================================================
   lightbox.js — полноэкранный просмотр фото/видео с листанием.
   Клик по медиа → открывается крупно. Закрытие: крестик (виден и на ПК, и на
   мобильном — фикс сверху с safe-area), Esc, клик по фону. Листание — стрелки
   ◀ ▶ и клавиши. Группы независимы (автопарк / видео / кейсы / галерея).
   ============================================================================ */

// Группы: селектор контейнера-элемента + тип медиа внутри.
const GROUPS = [
  { name: 'autopark', item: '.autopark__item',   media: 'img' },
  { name: 'videos',   item: '.video-strip__item', media: 'video' },
  { name: 'cases',    item: '.case-card',          media: 'img' },
  { name: 'gallery',  item: '.gallery__item',      media: 'img' },
];

let overlay, stage, counter, current = [], index = 0, lastFocused = null;

function buildOverlay() {
  overlay = document.createElement('div');
  overlay.className = 'lightbox';
  overlay.setAttribute('aria-hidden', 'true');
  overlay.setAttribute('role', 'dialog');
  overlay.setAttribute('aria-modal', 'true');
  overlay.innerHTML =
    '<button class="lightbox__close" aria-label="Закрыть" data-lb-close>&times;</button>' +
    '<button class="lightbox__nav lightbox__prev" aria-label="Предыдущее" data-lb-prev>&#8249;</button>' +
    '<button class="lightbox__nav lightbox__next" aria-label="Следующее" data-lb-next>&#8250;</button>' +
    '<div class="lightbox__stage" data-lb-stage></div>' +
    '<div class="lightbox__counter" data-lb-counter></div>';
  document.body.appendChild(overlay);
  stage = overlay.querySelector('[data-lb-stage]');
  counter = overlay.querySelector('[data-lb-counter]');

  overlay.addEventListener('click', (e) => {
    if (e.target === overlay || e.target.closest('[data-lb-close]')) close();
    else if (e.target.closest('[data-lb-prev]')) go(-1);
    else if (e.target.closest('[data-lb-next]')) go(1);
  });
  document.addEventListener('keydown', (e) => {
    if (!overlay.classList.contains('is-open')) return;
    if (e.key === 'Escape') close();
    else if (e.key === 'ArrowLeft') go(-1);
    else if (e.key === 'ArrowRight') go(1);
  });
}

function render() {
  const it = current[index];
  stage.innerHTML = '';
  if (it.type === 'video') {
    const v = document.createElement('video');
    v.src = it.src; v.poster = it.poster || ''; v.controls = true;
    v.autoplay = true; v.loop = true; v.muted = true; v.playsInline = true;
    stage.appendChild(v);
  } else {
    const img = document.createElement('img');
    img.src = it.src; img.alt = it.alt || '';
    stage.appendChild(img);
  }
  counter.textContent = (index + 1) + ' / ' + current.length;
  // одиночный элемент — стрелки не нужны
  const multi = current.length > 1;
  overlay.querySelector('[data-lb-prev]').style.display = multi ? '' : 'none';
  overlay.querySelector('[data-lb-next]').style.display = multi ? '' : 'none';
  counter.style.display = multi ? '' : 'none';
}

function go(dir) {
  index = (index + dir + current.length) % current.length; // зациклено
  render();
}

function open(items, i) {
  current = items; index = i;
  lastFocused = document.activeElement;
  render();
  overlay.classList.add('is-open');
  overlay.setAttribute('aria-hidden', 'false');
  document.body.style.overflow = 'hidden';
  overlay.querySelector('[data-lb-close]').focus();
}

function close() {
  overlay.classList.remove('is-open');
  overlay.setAttribute('aria-hidden', 'true');
  document.body.style.overflow = '';
  stage.innerHTML = ''; // выгружаем видео
  if (lastFocused) lastFocused.focus();
}

export function initLightbox() {
  buildOverlay();

  GROUPS.forEach((g) => {
    const els = [...document.querySelectorAll(g.item)];
    if (!els.length) return;
    // формируем данные группы один раз
    const data = els.map((el) => {
      const m = el.querySelector(g.media);
      if (g.media === 'video') {
        return { type: 'video', src: m.dataset.src || m.src, poster: m.getAttribute('poster') || '' };
      }
      return { type: 'image', src: m.currentSrc || m.src, alt: m.alt || '' };
    });
    els.forEach((el, i) => {
      el.classList.add('lb-clickable');
      el.addEventListener('click', (e) => {
        // не мешаем ссылкам/кнопкам внутри карточки
        if (e.target.closest('a, button')) return;
        // для картинок берём актуальный currentSrc (мог смениться из-за <picture>)
        const m = el.querySelector(g.media);
        if (data[i].type === 'image' && m.currentSrc) data[i].src = m.currentSrc;
        open(data, i);
      });
    });
  });
}
