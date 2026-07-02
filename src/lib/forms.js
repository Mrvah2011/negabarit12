/* ============================================================================
   forms.js — валидация, маска телефона, отправка, экран благодарности.
   Один обработчик на все формы с классом .jsform.
   Правила ТЗ §4: минимум обязательных (Имя+Телефон), маска телефона,
   валидация на blur, honeypot от спама, кнопки-глаголом, экран «Готово».
   Точка приёма заявок задаётся FORM_ENDPOINT — заказчик подставит webhook/CRM.
   Пока endpoint пустой — заявка логируется и сразу показывается благодарность
   (чтобы UX можно было проверить без бэкенда).
   ============================================================================ */

import { track, getUTM } from './analytics.js';

// Web3Forms — заявки уходят на info@negabarit12.com. Ключ привязан к этой почте.
// Получить: web3forms.com → ввести info@negabarit12.com → вставить access-key сюда.
const WEB3FORMS_KEY = ''; // TODO: вставить access-key Web3Forms
const WEB3FORMS_URL = 'https://api.web3forms.com/submit';

/* --- Маска телефона РФ: +7 (XXX) XXX-XX-XX --- */
export function maskPhone(input) {
  const format = (digits) => {
    let d = digits.replace(/\D/g, '');
    if (d.startsWith('8')) d = '7' + d.slice(1);
    if (!d.startsWith('7')) d = '7' + d;
    d = d.slice(0, 11);
    const p = d.slice(1);
    let out = '+7';
    if (p.length) out += ' (' + p.slice(0, 3);
    if (p.length >= 3) out += ') ' + p.slice(3, 6);
    if (p.length >= 6) out += '-' + p.slice(6, 8);
    if (p.length >= 8) out += '-' + p.slice(8, 10);
    return out;
  };
  input.addEventListener('input', () => { input.value = format(input.value); });
  input.addEventListener('focus', () => { if (!input.value) input.value = '+7 '; });
  input.addEventListener('blur', () => { if (input.value.trim() === '+7' || input.value.trim() === '+7 (') input.value = ''; });
}

const isPhoneValid = (v) => v.replace(/\D/g, '').length === 11;

/* --- Валидация одного поля. Показываем ошибку под полем. --- */
function validateField(field) {
  const wrap = field.closest('.field') || field.parentElement;
  let msg = '';
  if (field.required && !field.value.trim()) msg = 'Заполните поле';
  else if (field.type === 'tel' && field.value && !isPhoneValid(field.value)) msg = 'Проверьте номер телефона';
  else if (field.type === 'checkbox' && field.required && !field.checked) msg = 'Нужно согласие';

  wrap.classList.toggle('field--error', !!msg);
  const err = wrap.querySelector('.field__error');
  if (err) err.textContent = msg;
  return !msg;
}

function validateForm(form) {
  let ok = true; let firstBad = null;
  form.querySelectorAll('input[required], textarea[required], input[type="tel"]').forEach((f) => {
    if (!validateField(f) && ok) { ok = false; firstBad = f; }
  });
  if (firstBad) firstBad.focus(); // focus-management: курсор на первую ошибку
  return ok;
}

async function submitForm(form) {
  const data = Object.fromEntries(new FormData(form).entries());
  Object.assign(data, getUTM());                 // UTM скрытыми полями
  data.page = location.pathname;
  const eventName = form.dataset.event || 'lead_final';

  if (WEB3FORMS_KEY) {
    // тема письма по типу формы — чтобы в почте было видно источник заявки
    const subjects = {
      lead_calc: 'Заявка с калькулятора — Негабарит 12',
      lead_callback: 'Заказ звонка — Негабарит 12',
      lead_magnet: 'Запрос чек-листа — Негабарит 12',
      lead_final: 'Заявка с сайта — Негабарит 12',
    };
    const res = await fetch(WEB3FORMS_URL, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify({
        access_key: WEB3FORMS_KEY,
        subject: subjects[eventName] || 'Заявка с сайта — Негабарит 12',
        from_name: 'Сайт Негабарит 12',
        ...data,
      }),
    });
    const json = await res.json().catch(() => ({}));
    if (!res.ok || json.success === false) throw new Error('Web3Forms ' + res.status);
  } else {
    // ключ ещё не задан — не блокируем демонстрацию UX
    console.info('[lead] (Web3Forms key не задан) ', eventName, data);
    await new Promise((r) => setTimeout(r, 500));
  }
  track(eventName, { cargo: data.cargo_type, route: data.route_from });
}

/** Подмена формы на экран благодарности с конкретикой (ТЗ §4). */
function showThanks(form) {
  const thanks = form.querySelector('[data-thanks]') || form.closest('[data-form-host]')?.querySelector('[data-thanks]');
  if (thanks) {
    form.querySelectorAll(':scope > *:not([data-thanks])').forEach((el) => { el.style.display = 'none'; });
    thanks.hidden = false;
  } else {
    form.innerHTML = '<div class="thanks"><svg class="icon icon--success" viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg><h3>Готово. Логист перезвонит в течение 15 минут.</h3><p class="muted">Без спама и навязывания. Если срочно — звоните 8 (836) 249-53-20.</p></div>';
  }
}

export function initForms() {
  document.querySelectorAll('.jsform').forEach((form) => {
    // маска телефонов
    form.querySelectorAll('input[type="tel"]').forEach(maskPhone);
    // валидация на blur (не на каждый keystroke — меньше раздражает)
    form.querySelectorAll('input, textarea').forEach((f) => {
      f.addEventListener('blur', () => validateField(f));
    });

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      // honeypot: если скрытое поле заполнено — это бот, тихо «успешно» выходим
      const hp = form.querySelector('input[name="company_site"]');
      if (hp && hp.value) { showThanks(form); return; }
      if (!validateForm(form)) return;

      const btn = form.querySelector('button[type="submit"]');
      const label = btn ? btn.innerHTML : '';
      if (btn) { btn.disabled = true; btn.innerHTML = '<span class="btn__spin"></span> Отправляем…'; }
      try {
        await submitForm(form);
        showThanks(form);
      } catch (err) {
        if (btn) { btn.disabled = false; btn.innerHTML = label; }
        const generic = form.querySelector('.form__error');
        if (generic) { generic.textContent = 'Не удалось отправить. Позвоните: 8 (836) 249-53-20'; generic.hidden = false; }
      }
    });
  });
}
