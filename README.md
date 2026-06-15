# Лендинг ООО «Негабарит 12»

Конверсионный одностраничник по перевозке негабаритных грузов с покадровым видеоскроллом в hero (canvas-секвенция), плавным скроллом и scroll-triggered анимациями. Mobile-first.

## Стек
Vanilla HTML/CSS/JS (ES-модули, без сборки) · GSAP + ScrollTrigger · Lenis (CDN). Статика — деплой на Vercel как есть.

## Структура
```
index.html
src/
  main.js              — сборка: hero-pin, pinned-этапы, инициализация модулей
  lib/
    smoothScroll.js    — Lenis + связка с ScrollTrigger
    sequence.js        — canvas-секвенция hero (свопабельная, см. HERO_SEQ в main.js)
    reveal.js          — reveal/counters/parallax
    quiz.js            — квиз-калькулятор
    forms.js           — валидация/маска/отправка/благодарность
    video.js           — ленивые loop-видео
    ui.js              — header/модалки/FAQ/фильтр кейсов
    analytics.js       — dataLayer / Метрика
  styles/{tokens,base,sections}.css
assets/
  sequence/hero/       — 150 кадров WebP видеоскролла
  img/ video/ logo/ docs/
```

## Локальный запуск
ES-модули требуют http (не file://):
```
python -m http.server 5501
# открыть http://127.0.0.1:5501
```

## Hero-видеоскролл (свопабельно)
Кадры в `assets/sequence/hero/hero_0001..0150.webp`. Чтобы поменять клип — перегенерировать кадры в ту же папку и обновить `count` в `HERO_SEQ` (`src/main.js`). На mobile/reduced-motion/save-data секвенция не грузится → постер.

## Перед боевым запуском (TODO)
- `FORM_ENDPOINT` в `src/lib/forms.js` — точка приёма заявок (email/Telegram/CRM).
- `YM_ID` в `src/lib/analytics.js` — Яндекс.Метрика.
- Цифры «12 000+ перевозок», «30+ техники» — сейчас примеры, подтвердить.
- Артефакты (скан КТГ, договор, GPS) — заменить плейсхолдеры.
