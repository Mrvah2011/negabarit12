/* ============================================================================
   ui.js — глобальные UI-взаимодействия:
   sticky-header (появляется после hero), модалки, FAQ-аккордеон,
   фильтр кейсов, прокидывание CTA в калькулятор.
   ============================================================================ */

import { scrollToEl, prefersReducedMotion } from './smoothScroll.js';
import { prefillCargo } from './quiz.js';
import { track } from './analytics.js';

/* --- Sticky header: показываем, когда проскроллили hero --- */
export function initHeader() {
  const header = document.querySelector('.header');
  const hero = document.getElementById('hero');
  if (!header || !hero) return;
  const io = new IntersectionObserver(([e]) => {
    header.classList.toggle('is-visible', !e.isIntersecting);
  }, { rootMargin: '-80px 0px 0px 0px' });
  io.observe(hero);
}

/* --- CTA-навигация: любой [data-scroll="#id"] плавно ведёт к секции --- */
export function initScrollLinks() {
  document.querySelectorAll('[data-scroll]').forEach((el) => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      scrollToEl(el.dataset.scroll, -70);
    });
  });
  // карточки с типом груза → предзаполнить калькулятор
  document.querySelectorAll('[data-cargo]').forEach((el) => {
    el.addEventListener('click', (e) => {
      e.preventDefault();
      prefillCargo(el.dataset.cargo, el.dataset.cargoLabel);
    });
  });
}

/* --- Модальные окна --- */
export function initModals() {
  let lastFocused = null;

  const open = (id) => {
    const modal = document.getElementById(id);
    if (!modal) return;
    lastFocused = document.activeElement;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
    const first = modal.querySelector('input, button, [tabindex]');
    if (first) setTimeout(() => first.focus(), 50);
    track('modal_open', { id });
  };
  const close = (modal) => {
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
    if (lastFocused) lastFocused.focus();
  };

  document.querySelectorAll('[data-modal-open]').forEach((btn) =>
    btn.addEventListener('click', (e) => { e.preventDefault(); open(btn.dataset.modalOpen); }));

  document.querySelectorAll('.modal').forEach((modal) => {
    modal.addEventListener('click', (e) => {
      // клик по подложке или по [data-modal-close] закрывает
      if (e.target === modal || e.target.closest('[data-modal-close]')) close(modal);
    });
  });
  // Esc закрывает (escape-route, доступность)
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      const openModal = document.querySelector('.modal.is-open');
      if (openModal) close(openModal);
    }
  });
}

/* --- FAQ-аккордеон: плавное раскрытие через max-height, +/× rotate --- */
export function initFaq() {
  document.querySelectorAll('.faq__item').forEach((item) => {
    const btn = item.querySelector('.faq__q');
    const ans = item.querySelector('.faq__a');
    btn.addEventListener('click', () => {
      const isOpen = item.classList.toggle('is-open');
      btn.setAttribute('aria-expanded', String(isOpen));
      if (prefersReducedMotion) { ans.style.maxHeight = isOpen ? 'none' : '0'; return; }
      ans.style.maxHeight = isOpen ? ans.scrollHeight + 'px' : '0';
    });
  });
}

/* --- Фильтр кейсов по типу груза --- */
export function initCaseFilter() {
  const filterBar = document.querySelector('.cases__filter');
  if (!filterBar) return;
  const cards = [...document.querySelectorAll('.case-card')];
  filterBar.querySelectorAll('button').forEach((btn) => {
    btn.addEventListener('click', () => {
      filterBar.querySelectorAll('button').forEach((b) => b.classList.remove('is-active'));
      btn.classList.add('is-active');
      const f = btn.dataset.filter;
      cards.forEach((c) => {
        const show = f === 'all' || c.dataset.type === f;
        c.style.display = show ? '' : 'none';
      });
    });
  });
}

export function initUI() {
  initHeader();
  initScrollLinks();
  initModals();
  initFaq();
  initCaseFilter();
}
