/* ============================================================================
   quiz.js — мультишаг квиз-калькулятор (Экран 2, захват лида).
   5 шагов: тип груза → габариты → маршрут → срочность → контакты.
   Прогресс-бар (зебра-заливка), валидация по шагу, скрытые поля на финал.
   Финальный шаг — это .jsform: его submit обрабатывает forms.js.
   API prefillCargo() — карточки hero/JTBD прокидывают тип груза и скроллят сюда.
   ============================================================================ */

import { track } from './analytics.js';
import { scrollToEl } from './smoothScroll.js';

let root, steps, bar, data;

export function initQuiz() {
  root = document.getElementById('quiz');
  if (!root) return;
  steps = [...root.querySelectorAll('.quiz__step')];
  bar = root.querySelector('.quiz__bar-fill');
  data = {};

  // Шаг 1 и 4: карточки-кнопки с выбором значения
  root.querySelectorAll('.quiz__choice').forEach((btn) => {
    btn.addEventListener('click', () => {
      const step = btn.closest('.quiz__step');
      step.querySelectorAll('.quiz__choice').forEach((b) => b.classList.remove('is-selected'));
      btn.classList.add('is-selected');
      data[step.dataset.key] = btn.dataset.value;
      setHidden(step.dataset.key, btn.dataset.value);
      // авто-переход после выбора (короткая пауза, чтобы увидеть выделение)
      setTimeout(() => go(current() + 1), 250);
    });
  });

  // Кнопки «Далее»
  root.querySelectorAll('[data-next]').forEach((btn) =>
    btn.addEventListener('click', () => { if (validateStep(steps[current()])) go(current() + 1); }));
  // Кнопки «Назад»
  root.querySelectorAll('[data-prev]').forEach((btn) =>
    btn.addEventListener('click', () => go(current() - 1)));

  // собрать габариты/маршрут в скрытые поля при вводе
  root.querySelectorAll('.quiz__input').forEach((inp) =>
    inp.addEventListener('input', () => setHidden(inp.name, inp.value)));

  go(0);
}

const current = () => steps.findIndex((s) => s.classList.contains('is-active'));

function go(i) {
  i = Math.max(0, Math.min(steps.length - 1, i));
  steps.forEach((s, idx) => s.classList.toggle('is-active', idx === i));
  const pct = Math.round(((i + 1) / steps.length) * 100);
  if (bar) bar.style.width = pct + '%';
  const live = root.querySelector('.quiz__counter');
  if (live) live.textContent = `Шаг ${i + 1} из ${steps.length}`;
  track(`quiz_step_${i + 1}`);
  // фокус на первый инпут шага (доступность), но не на мобильном (не дёргаем клавиатуру)
  if (!window.matchMedia('(max-width:768px)').matches) {
    const f = steps[i].querySelector('input, button.quiz__choice');
    if (f) f.focus({ preventScroll: true });
  }
}

function validateStep(step) {
  // на шагах с обязательными инпутами проверяем заполненность
  const inputs = [...step.querySelectorAll('input[required]')];
  let ok = true;
  inputs.forEach((inp) => {
    const bad = !inp.value.trim();
    const field = inp.closest('.field') || inp.parentElement;
    field.classList.toggle('field--error', bad);
    if (bad) ok = false;
  });
  return ok;
}

/** Кладём значение в скрытый input финальной формы (чтобы ушло вместе с заявкой). */
function setHidden(key, value) {
  const hidden = root.querySelector(`.jsform [name="${key}"][type="hidden"], .jsform [data-collect="${key}"]`);
  if (hidden) hidden.value = value;
}

/** Внешний вызов: предзаполнить тип груза и проскроллить к квизу. */
export function prefillCargo(value, label) {
  scrollToEl('#quiz', -80);
  if (!root) return;
  data.cargo_type = value;
  setHidden('cargo_type', label || value);
  const step1 = root.querySelector('.quiz__step[data-key="cargo_type"]');
  if (step1) {
    step1.querySelectorAll('.quiz__choice').forEach((b) => {
      const match = b.dataset.value === value;
      b.classList.toggle('is-selected', match);
    });
  }
}
