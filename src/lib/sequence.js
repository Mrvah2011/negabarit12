/* ============================================================================
   sequence.js — покадровый видеоскролл в hero (Apple-style image sequence).

   ПОЧЕМУ CANVAS, А НЕ <video> (см. ТЗ §1):
   iOS Safari не даёт надёжно скрабить video.currentTime по скроллу — кадр
   «прилипает» к ключевому и дёргается. Единственный стабильный способ —
   заранее нарезать клип в кадры (WebP) и рисовать нужный кадр на <canvas>
   в зависимости от прогресса скролла. Так делает Apple на продуктовых страницах.

   СВОПАБЕЛЬНОСТЬ: источник секвенции задаётся объектом config (path/count) из
   main.js. Чтобы наложить другой клип — перегенерировать кадры и поменять count,
   разметку/логику трогать не нужно.

   ДЕГРАДАЦИЯ (90% трафика мобильный):
   на мобильном / reduced-motion / save-data секвенция НЕ грузится — показываем
   статичный постер <img> и те же тексты. pin не вешаем.
   ============================================================================ */

import { isMobile, prefersReducedMotion, saveData } from './smoothScroll.js';

export class HeroSequence {
  /**
   * @param {Object} cfg
   * @param {HTMLCanvasElement} cfg.canvas
   * @param {HTMLElement} cfg.section   — секция-триггер для pin
   * @param {string} cfg.path           — паттерн пути, напр. 'assets/sequence/hero/hero_'
   * @param {number} cfg.count          — число кадров
   * @param {number} cfg.pad            — нулей в номере (hero_0001 → 4)
   * @param {string} cfg.ext            — расширение, напр. '.webp'
   * @param {HTMLElement} [cfg.preloader] — оверлей прелоадера
   * @param {HTMLElement} [cfg.bar]       — заполняемая полоса прелоадера
   * @param {HTMLElement} [cfg.percent]   — текст процента
   */
  constructor(cfg) {
    this.cfg = cfg;
    this.canvas = cfg.canvas;
    // alpha:true — при очистке canvas (resize/первый кадр) сквозь него виден постер,
    // а не чёрная заливка. Убирает «чёрный экран» при скролле.
    this.ctx = this.canvas.getContext('2d', { alpha: true });
    this.frames = [];        // HTMLImageElement[]
    this.loaded = 0;
    this.current = -1;
    this.dpr = Math.min(window.devicePixelRatio || 1, 1.5); // на ретине чётче, но не выше 1.5× — вдвое легче заливка
  }

  /** Решение: грузить секвенцию или отдать статичный постер. */
  shouldRunSequence() {
    return !isMobile() && !prefersReducedMotion && !saveData;
  }

  frameSrc(i) {
    const n = String(i + 1).padStart(this.cfg.pad, '0');
    return `${this.cfg.path}${n}${this.cfg.ext}`;
  }

  /** Точка входа. Возвращает Promise, резолвится когда hero готов к показу. */
  async init() {
    if (!this.shouldRunSequence()) {
      this.hidePreloader();      // постер уже в разметке как <img> — ничего грузить не надо
      this.canvas.style.display = 'none';
      return { mode: 'poster' };
    }

    this.resize();
    window.addEventListener('resize', () => { this.resize(); this.draw(this.current < 0 ? 0 : this.current); }, { passive: true });

    // 1-й кадр грузим первым и рисуем сразу — мгновенный first paint, без чёрного экрана.
    await this.loadFrame(0).then((img) => { this.frames[0] = img; this.draw(0); });

    // Остальные кадры догружаем с прогрессом.
    await this.preloadRest();
    this.hidePreloader();
    return { mode: 'sequence' };
  }

  loadFrame(i) {
    return new Promise((resolve, reject) => {
      const img = new Image();
      img.decoding = 'async';
      img.onload = () => resolve(img);
      img.onerror = reject;
      img.src = this.frameSrc(i);
    });
  }

  async preloadRest() {
    const total = this.cfg.count;
    this.loaded = 1;
    this.updateProgress();

    // Грузим параллельно пачками — быстрее, чем по одному, но без шторма соединений.
    const BATCH = 12;
    for (let start = 1; start < total; start += BATCH) {
      const batch = [];
      for (let i = start; i < Math.min(start + BATCH, total); i++) {
        batch.push(
          this.loadFrame(i)
            .then((img) => { this.frames[i] = img; })
            .catch(() => { /* пропущенный кадр не ломает скраб — рисуем ближайший */ })
            .finally(() => { this.loaded++; this.updateProgress(); })
        );
      }
      // eslint-disable-next-line no-await-in-loop
      await Promise.all(batch);
    }
  }

  updateProgress() {
    const pct = Math.round((this.loaded / this.cfg.count) * 100);
    if (this.cfg.bar) this.cfg.bar.style.width = pct + '%';
    if (this.cfg.percent) this.cfg.percent.textContent = pct + '%';
  }

  hidePreloader() {
    const p = this.cfg.preloader;
    if (!p) return;
    p.style.opacity = '0';
    setTimeout(() => { p.style.display = 'none'; }, 400);
  }

  /** Подгоняем буфер canvas под CSS-размер с учётом DPR. */
  resize() {
    const r = this.canvas.getBoundingClientRect();
    this.canvas.width = Math.round(r.width * this.dpr);
    this.canvas.height = Math.round(r.height * this.dpr);
  }

  /** Рисуем кадр i с cover-логикой (заполняет весь canvas, без искажений). */
  draw(i) {
    const idx = Math.max(0, Math.min(this.cfg.count - 1, Math.round(i)));
    // если точный кадр ещё не загружен — берём ближайший загруженный (плавная деградация)
    let img = this.frames[idx];
    if (!img) { for (let d = 1; d < this.cfg.count; d++) { if (this.frames[idx - d]) { img = this.frames[idx - d]; break; } if (this.frames[idx + d]) { img = this.frames[idx + d]; break; } } }
    if (!img) return;

    this.current = idx;
    const cw = this.canvas.width, ch = this.canvas.height;
    const ir = img.width / img.height, cr = cw / ch;
    let dw, dh, dx, dy;
    if (ir > cr) { dh = ch; dw = ch * ir; dx = (cw - dw) / 2; dy = 0; }
    else         { dw = cw; dh = cw / ir; dx = 0; dy = (ch - dh) / 2; }
    this.ctx.drawImage(img, dx, dy, dw, dh);
  }

  /** Привязка прогресса pinned-скролла к индексу кадра. Вызывать из ScrollTrigger. */
  renderAtProgress(progress) {
    this.draw(progress * (this.cfg.count - 1));
  }
}
