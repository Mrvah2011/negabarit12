/* ============================================================================
   analytics.js — тонкая обёртка над dataLayer / Яндекс.Метрикой.
   Все конверсионные события идут сюда. Реальные ID метрики подставит заказчик —
   до этого события просто пишутся в dataLayer и в консоль (safe no-op).
   События (ТЗ §5): lead_calc, lead_callback, lead_magnet, lead_final, quiz_step_N.
   ============================================================================ */

const YM_ID = null; // TODO: вставить ID Яндекс.Метрики заказчика

window.dataLayer = window.dataLayer || [];

export function track(event, payload = {}) {
  window.dataLayer.push({ event, ...payload });
  // Яндекс.Метрика reachGoal, если метрика подключена
  if (YM_ID && typeof window.ym === 'function') {
    window.ym(YM_ID, 'reachGoal', event, payload);
  }
  // Видно в консоли при отладке — поможет проверить воронку до подключения метрики
  if (window.__DEBUG_ANALYTICS__) console.info('[analytics]', event, payload);
}

/** UTM-метки из URL → объект (кладём скрытыми полями в формы). */
export function getUTM() {
  const p = new URLSearchParams(location.search);
  const utm = {};
  ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'].forEach((k) => {
    if (p.get(k)) utm[k] = p.get(k);
  });
  return utm;
}
