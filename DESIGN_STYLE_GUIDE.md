# Industrial Properties v2 - Design Style Guide

## 🎨 Общ дизайн философия

Този стил гайд дефинира цялостната дизайн система за Industrial Properties v2 проекта. Фокусът е върху чист, лек индустриален дизайн с модерни UX принципи, запазвайки простотата и функционалността.

---

## 🎯 Дизайн принципи

### 1. **Минималистичен подход**
- Чисти линии и геометрични форми
- Много бяло пространство (whitespace)
- Фокус върху съдържанието

### 2. **Индустриална естетика**
- Неутрални цветове с акценти
- Модерна типография
- Професионален, но достъпен тон

### 3. **Мобилен фокус**
- Mobile-first дизайн
- Responsive layouts
- Touch-friendly интерфейси

### 4. **Performance ориентация**
- Леки компоненти
- Оптимизирани анимации
- Fast loading times

---

## 🎨 Цветова палитра

### Основни цветове
```scss
// Primary Colors
$primary-dark: #0f172a;      // Тъмно синьо за backgrounds
$primary-medium: #1e293b;    // Средно синьо за sections
$primary-light: #334155;     // Светло синьо за accents

// Secondary Colors  
$secondary-blue: #3b82f6;    // Accent blue за buttons/links
$secondary-light: #e2e8f0;   // Светло сиво за borders
$secondary-bg: #f8fafc;      // Background color

// Text Colors
$text-primary: #1a202c;      // Основен текст
$text-secondary: #64748b;    // Вторичен текст  
$text-muted: #94a3b8;        // Приглушен текст
$text-white: #ffffff;        // Бял текст

// Status Colors
$success: #10b981;           // Успех
$warning: #f59e0b;           // Предупреждение  
$error: #ef4444;             // Грешка
$info: #3b82f6;              // Информация

// VIP Colors
$vip-gold: #fbbf24;          // VIP златисто
$vip-gradient-start: #f59e0b; // VIP gradient start
$vip-gradient-end: #fbbf24;   // VIP gradient end
```

### Градиенти
```scss
// Hero gradients
$hero-gradient: linear-gradient(135deg, #0f172a 0%, #334155 100%);
$hero-overlay: linear-gradient(rgba(15, 23, 42, 0.8), rgba(51, 65, 85, 0.6));

// Card gradients  
$card-hover-gradient: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);

// VIP gradients
$vip-gradient: linear-gradient(135deg, #f59e0b 0%, #fbbf24 100%);
```

---

## 📝 Типография

### Font стек
```scss
$font-primary: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
$font-secondary: 'Roboto', Arial, sans-serif;
$font-mono: 'JetBrains Mono', 'Fira Code', monospace;
```

### Font sizes & weights
```scss
// Font Sizes
$font-xs: 0.75rem;     // 12px
$font-sm: 0.875rem;    // 14px  
$font-base: 1rem;      // 16px
$font-lg: 1.125rem;    // 18px
$font-xl: 1.25rem;     // 20px
$font-2xl: 1.5rem;     // 24px
$font-3xl: 1.875rem;   // 30px
$font-4xl: 2.25rem;    // 36px
$font-5xl: 3rem;       // 48px

// Font Weights
$font-light: 300;
$font-normal: 400;
$font-medium: 500;
$font-semibold: 600;
$font-bold: 700;

// Line Heights
$leading-tight: 1.25;
$leading-normal: 1.5;
$leading-relaxed: 1.75;
```

### Heading стилове
```scss
.heading-1 {
    font-size: $font-5xl;
    font-weight: $font-bold;
    line-height: $leading-tight;
    color: $text-primary;
}

.heading-2 {
    font-size: $font-4xl;
    font-weight: $font-semibold;
    line-height: $leading-tight;
    color: $text-primary;
}

.heading-3 {
    font-size: $font-3xl;
    font-weight: $font-medium;
    line-height: $leading-normal;
    color: $text-primary;
}
```

---

## 🔲 Layout система

### Container размери
```scss
$container-sm: 640px;
$container-md: 768px;
$container-lg: 1024px;
$container-xl: 1280px;
$container-2xl: 1536px;
```

### Spacing система
```scss
// Spacing scale (rem units)
$space-1: 0.25rem;   // 4px
$space-2: 0.5rem;    // 8px
$space-3: 0.75rem;   // 12px
$space-4: 1rem;      // 16px
$space-5: 1.25rem;   // 20px
$space-6: 1.5rem;    // 24px
$space-8: 2rem;      // 32px
$space-10: 2.5rem;   // 40px
$space-12: 3rem;     // 48px
$space-16: 4rem;     // 64px
$space-20: 5rem;     // 80px
$space-24: 6rem;     // 96px
```

### Grid система
```scss
.grid-container {
    display: grid;
    gap: $space-6;
    
    @media (min-width: 768px) {
        grid-template-columns: repeat(2, 1fr);
        gap: $space-8;
    }
    
    @media (min-width: 1024px) {
        grid-template-columns: repeat(3, 1fr);
        gap: $space-10;
    }
}
```

---

## 🎛️ Компоненти

### Бутони
```scss
// Primary Button
.btn-primary {
    background: $secondary-blue;
    color: white;
    padding: $space-3 $space-6;
    border-radius: 8px;
    font-weight: $font-medium;
    transition: all 0.3s ease;
    border: none;
    cursor: pointer;
    
    &:hover {
        background: darken($secondary-blue, 10%);
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(59, 130, 246, 0.3);
    }
}

// Secondary Button  
.btn-secondary {
    background: transparent;
    color: $secondary-blue;
    border: 2px solid $secondary-blue;
    padding: $space-3 $space-6;
    border-radius: 8px;
    font-weight: $font-medium;
    transition: all 0.3s ease;
    
    &:hover {
        background: $secondary-blue;
        color: white;
        transform: translateY(-2px);
    }
}

// VIP Button
.btn-vip {
    background: $vip-gradient;
    color: white;
    padding: $space-3 $space-6;
    border-radius: 8px;
    font-weight: $font-semibold;
    transition: all 0.3s ease;
    border: none;
    
    &:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(251, 191, 36, 0.4);
    }
}
```

### Карти (Cards)
```scss
.card {
    background: white;
    border-radius: 12px;
    padding: $space-6;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
    border: 1px solid $secondary-light;
    
    &:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        background: $card-hover-gradient;
    }
}

.card-header {
    border-bottom: 1px solid $secondary-light;
    padding-bottom: $space-4;
    margin-bottom: $space-4;
}

.card-title {
    font-size: $font-xl;
    font-weight: $font-semibold;
    color: $text-primary;
    margin-bottom: $space-2;
}

.card-description {
    color: $text-secondary;
    line-height: $leading-relaxed;
}
```

### Форми
```scss
.form-group {
    margin-bottom: $space-6;
}

.form-label {
    display: block;
    font-weight: $font-medium;
    color: $text-primary;
    margin-bottom: $space-2;
}

.form-input {
    width: 100%;
    padding: $space-3 $space-4;
    border: 2px solid $secondary-light;
    border-radius: 8px;
    font-size: $font-base;
    transition: all 0.3s ease;
    
    &:focus {
        outline: none;
        border-color: $secondary-blue;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }
    
    &::placeholder {
        color: $text-muted;
    }
}
```

---

## ✨ Анимации и преходи

### Основни transitions
```scss
$transition-fast: 0.15s ease;
$transition-normal: 0.3s ease;
$transition-slow: 0.5s ease;

// Hover ефекти
.hover-lift {
    transition: transform $transition-normal;
    
    &:hover {
        transform: translateY(-4px);
    }
}

.hover-scale {
    transition: transform $transition-normal;
    
    &:hover {
        transform: scale(1.05);
    }
}
```

### Keyframe анимации
```scss
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideInLeft {
    from {
        opacity: 0;
        transform: translateX(-30px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: 0.8;
    }
}
```

---

## 📱 Responsive Breakpoints и Mobile оптимизации

```scss
// Mobile devices
$mobile: 'max-width: 767px';
$mobile-small: 'max-width: 480px';

// Tablet devices  
$tablet: 'min-width: 768px';
$tablet-only: 'min-width: 768px and max-width: 1023px';

// Desktop devices
$desktop: 'min-width: 1024px';

// Large desktop devices
$desktop-large: 'min-width: 1280px';

// Extra large desktop devices
$desktop-xl: 'min-width: 1536px';
```

### Responsive utility classes
```scss
.mobile-only {
    @media ($tablet) {
        display: none;
    }
}

.desktop-only {
    @media ($mobile) {
        display: none;
    }
}

.tablet-only {
    @media ($mobile), ($desktop) {
        display: none;
    }
}

.text-center-mobile {
    @media ($mobile) {
        text-align: center;
    }
}

.full-width-mobile {
    @media ($mobile) {
        width: 100%;
        margin-left: 0;
        margin-right: 0;
    }
}
```

### Mobile-first approach
```scss
// Основни стилове са за мобилни устройства
.responsive-component {
    // Mobile styles (base)
    padding: $space-4;
    font-size: $font-sm;
    grid-template-columns: 1fr;
    
    // Tablet styles
    @media ($tablet) {
        padding: $space-6;
        font-size: $font-base;
        grid-template-columns: repeat(2, 1fr);
    }
    
    // Desktop styles
    @media ($desktop) {
        padding: $space-8;
        font-size: $font-lg;
        grid-template-columns: repeat(3, 1fr);
    }
    
    // Large desktop styles
    @media ($desktop-large) {
        padding: $space-10;
        grid-template-columns: repeat(4, 1fr);
    }
}
```

### Touch-friendly интерфейси
```scss
.touch-target {
    min-height: 44px; // Apple HIG recommendation
    min-width: 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    
    @media ($mobile) {
        min-height: 48px; // Android recommendation
        min-width: 48px;
    }
}

.mobile-button {
    @media ($mobile) {
        padding: $space-4 $space-6;
        font-size: $font-base;
        min-height: 48px;
        
        // Increase touch area without visual change
        &::after {
            content: '';
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
        }
    }
}
```

### Мобилна навигация
```scss
.mobile-nav {
    @media ($mobile) {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0, 0, 0, 0.8);
        backdrop-filter: blur(10px);
        z-index: 1000;
        display: flex;
        align-items: center;
        justify-content: center;
        transform: translateX(-100%);
        transition: transform 0.3s ease;
        
        &.open {
            transform: translateX(0);
        }
        
        .nav-content {
            background: white;
            border-radius: 20px;
            padding: $space-8;
            max-width: 90%;
            max-height: 80%;
            overflow-y: auto;
        }
        
        .nav-item {
            padding: $space-4 0;
            border-bottom: 1px solid $secondary-light;
            
            &:last-child {
                border-bottom: none;
            }
            
            a {
                font-size: $font-lg;
                font-weight: $font-medium;
                color: $text-primary;
                text-decoration: none;
                display: block;
                padding: $space-2 0;
            }
        }
    }
}
```

### Container адаптивни размери
```scss
.container {
    max-width: 100%;
    margin: 0 auto;
    padding: 0 $space-4;
    
    @media ($tablet) {
        padding: 0 $space-6;
        max-width: $container-md;
    }
    
    @media ($desktop) {
        padding: 0 $space-8;
        max-width: $container-lg;
    }
    
    @media ($desktop-large) {
        max-width: $container-xl;
    }
    
    @media ($desktop-xl) {
        max-width: $container-2xl;
    }
}

.container-fluid {
    max-width: 100%;
    margin: 0 auto;
    padding: 0 $space-4;
    
    @media ($tablet) {
        padding: 0 $space-6;
    }
    
    @media ($desktop) {
        padding: 0 $space-8;
    }
}
```

---

## 🏗️ Page Sections

### Hero секция
```scss
.hero-section {
    background: $hero-gradient;
    color: white;
    padding: $space-20 0;
    position: relative;
    overflow: hidden;
    
    &::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: $hero-overlay;
        z-index: 1;
    }
    
    .hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        max-width: 800px;
        margin: 0 auto;
    }
    
    .hero-title {
        font-size: $font-5xl;
        font-weight: $font-bold;
        margin-bottom: $space-6;
        line-height: $leading-tight;
    }
    
    .hero-subtitle {
        font-size: $font-xl;
        font-weight: $font-light;
        margin-bottom: $space-8;
        opacity: 0.9;
    }
}
```

### Content секции
```scss
.content-section {
    padding: $space-20 0;
    
    &.bg-light {
        background: $secondary-bg;
    }
    
    .section-header {
        text-align: center;
        margin-bottom: $space-16;
        
        .section-title {
            font-size: $font-4xl;
            font-weight: $font-semibold;
            color: $text-primary;
            margin-bottom: $space-4;
        }
        
        .section-subtitle {
            font-size: $font-lg;
            color: $text-secondary;
            max-width: 600px;
            margin: 0 auto;
            line-height: $leading-relaxed;
        }
    }
}
```

---

## 🎯 Специализирани компоненти

### VIP Badge
```scss
.vip-badge {
    background: $vip-gradient;
    color: white;
    padding: $space-1 $space-3;
    border-radius: 20px;
    font-size: $font-sm;
    font-weight: $font-semibold;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-block;
    animation: pulse 2s infinite;
}
```

### Property Type Icons
```scss
.property-icon {
    width: 60px;
    height: 60px;
    background: $secondary-blue;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto $space-4;
    color: white;
    font-size: $font-2xl;
    transition: all $transition-normal;
    
    &:hover {
        background: darken($secondary-blue, 10%);
        transform: scale(1.1);
    }
}
```

### Statistics Display
```scss
.stat-item {
    text-align: center;
    padding: $space-6;
    
    .stat-number {
        font-size: $font-4xl;
        font-weight: $font-bold;
        color: $secondary-blue;
        display: block;
        margin-bottom: $space-2;
    }
    
    .stat-label {
        font-size: $font-lg;
        color: $text-secondary;
        font-weight: $font-medium;
    }
}
```

---

## 🔧 Utility Classes

### Spacing utilities
```scss
.m-0 { margin: 0; }
.m-1 { margin: $space-1; }
.m-2 { margin: $space-2; }
.m-4 { margin: $space-4; }
.m-6 { margin: $space-6; }
.m-8 { margin: $space-8; }

.p-0 { padding: 0; }
.p-1 { padding: $space-1; }
.p-2 { padding: $space-2; }
.p-4 { padding: $space-4; }
.p-6 { padding: $space-6; }
.p-8 { padding: $space-8; }

.mt-auto { margin-top: auto; }
.mb-auto { margin-bottom: auto; }
.mx-auto { margin-left: auto; margin-right: auto; }
```

### Text utilities
```scss
.text-center { text-align: center; }
.text-left { text-align: left; }
.text-right { text-align: right; }

.text-primary { color: $text-primary; }
.text-secondary { color: $text-secondary; }
.text-muted { color: $text-muted; }
.text-white { color: $text-white; }

.font-light { font-weight: $font-light; }
.font-normal { font-weight: $font-normal; }
.font-medium { font-weight: $font-medium; }
.font-semibold { font-weight: $font-semibold; }
.font-bold { font-weight: $font-bold; }
```

### Display utilities
```scss
.hidden { display: none; }
.block { display: block; }
.inline { display: inline; }
.inline-block { display: inline-block; }
.flex { display: flex; }
.grid { display: grid; }

.justify-center { justify-content: center; }
.justify-between { justify-content: space-between; }
.justify-end { justify-content: flex-end; }

.items-center { align-items: center; }
.items-start { align-items: flex-start; }
.items-end { align-items: flex-end; }
```

---

## 🎨 Dark Mode Support

```scss
@media (prefers-color-scheme: dark) {
    :root {
        --bg-primary: #{$primary-dark};
        --bg-secondary: #{$primary-medium};
        --text-primary: #{$text-white};
        --text-secondary: #cbd5e1;
        --border-color: #475569;
    }
    
    .card {
        background: var(--bg-secondary);
        border-color: var(--border-color);
        color: var(--text-primary);
    }
}
```

---

## ♿ Accessibility Guidelines

### Focus states
```scss
.focusable {
    &:focus {
        outline: 2px solid $secondary-blue;
        outline-offset: 2px;
    }
    
    &:focus-visible {
        outline: 2px solid $secondary-blue;
        outline-offset: 2px;
    }
}
```

### High contrast support
```scss
@media (prefers-contrast: high) {
    .card {
        border: 2px solid $text-primary;
    }
    
    .btn-primary {
        border: 2px solid currentColor;
    }
}
```

### Motion preferences
```scss
@media (prefers-reduced-motion: reduce) {
    * {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
    }
}
```

---

## 📝 Best Practices

### 1. **CSS Organization**
- Използвай BEM методология за CSS класове
- Групирай стиловете логически (layout, components, utilities)
- Използвай SCSS variables за всички повтарящи се стойности

### 2. **Performance**
- Минимизирай CSS размера с unused code removal
- Използвай CSS containment където е възможно
- Оптимизирай критичния CSS path

### 3. **Responsive Design**
- Mobile-first подход
- Използвай relative units (rem, em, %)
- Тествай на различни device sizes

### 4. **Accessibility**
- Осигури достатъчен цветови контраст (4.5:1 ratio)
- Добави focus states за всички интерактивни елементи
- Използвай semantic HTML structure

### 5. **Browser Support**
- Тествай в Chrome, Firefox, Safari, Edge
- Използвай autoprefixer за vendor prefixes
- Graceful degradation за по-стари browsers

---

## 🚀 Implementation Notes

### SCSS Architecture
```
assets/styles/
├── abstracts/
│   ├── _variables.scss
│   ├── _mixins.scss
│   └── _functions.scss
├── base/
│   ├── _reset.scss
│   ├── _typography.scss
│   └── _utilities.scss
├── components/
│   ├── _buttons.scss
│   ├── _cards.scss
│   ├── _forms.scss
│   └── _navigation.scss
├── layout/
│   ├── _header.scss
│   ├── _footer.scss
│   └── _sections.scss
└── pages/
    ├── _home.scss
    ├── _property.scss
    └── _contact.scss
```

### Build Process
- Компилирай SCSS файловете с Webpack
- Минифицирай CSS за production
- Генерирай source maps за development
- Използвай PostCSS за autoprefixing

---

## 📋 Checklist за нови страници

- [ ] Използвам утвърдената цветова палитра
- [ ] Следвам typography hierarchy
- [ ] Имплементирам responsive design
- [ ] Добавям подходящи hover ефекти
- [ ] Осигурявам accessibility compliance
- [ ] Тествам на мобилни устройства
- [ ] Оптимизирам за performance
- [ ] Валидирам HTML/CSS

---

---

## 🎪 Интерактивни ефекти и анимации

### Ripple Effect за бутони
```scss
.ripple-effect {
    position: relative;
    overflow: hidden;
    
    &::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.5);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    &:active::before {
        width: 300px;
        height: 300px;
    }
}
```

### Loader анимации
```scss
.skeleton-loader {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

.pulse-loader {
    animation: pulse-loading 1.5s ease-in-out infinite;
}

@keyframes pulse-loading {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
```

### Scroll-triggered анимации
```scss
.fade-in-up {
    opacity: 0;
    transform: translateY(30px);
    transition: all 0.6s ease;
    
    &.visible {
        opacity: 1;
        transform: translateY(0);
    }
}

.slide-in-left {
    opacity: 0;
    transform: translateX(-30px);
    transition: all 0.6s ease;
    
    &.visible {
        opacity: 1;
        transform: translateX(0);
    }
}
```

---

## 🏗️ Модернизирани компоненти

### Advance Property Cards
```scss
.property-card {
    background: white;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    border-top: 4px solid transparent;
    position: relative;
    
    &::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, $secondary-blue, #1976d2, #ff6b35);
        transform: scaleX(0);
        transition: transform 0.4s ease;
        transform-origin: left;
    }
    
    &:hover {
        transform: translateY(-12px) scale(1.02);
        box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
        
        &::before {
            transform: scaleX(1);
        }
        
        .property-image {
            transform: scale(1.1);
        }
    }
    
    .property-image {
        width: 100%;
        height: 220px;
        object-fit: cover;
        transition: transform 0.6s ease;
    }
    
    .property-content {
        padding: $space-6;
    }
    
    .property-price {
        font-size: $font-2xl;
        font-weight: $font-bold;
        color: $secondary-blue;
        margin-bottom: $space-2;
    }
    
    .property-title {
        font-size: $font-lg;
        font-weight: $font-semibold;
        color: $text-primary;
        margin-bottom: $space-3;
        line-height: $leading-tight;
    }
    
    .property-location {
        display: flex;
        align-items: center;
        color: $text-secondary;
        font-size: $font-sm;
        margin-bottom: $space-4;
        
        i {
            margin-right: $space-2;
            color: $secondary-blue;
        }
    }
    
    .property-features {
        display: flex;
        justify-content: space-between;
        padding: $space-3 0;
        border-top: 1px solid $secondary-light;
        margin-top: $space-4;
        
        .feature {
            display: flex;
            align-items: center;
            font-size: $font-sm;
            color: $text-secondary;
            
            i {
                margin-right: $space-1;
                color: $secondary-blue;
            }
        }
    }
}
```

### VIP Property Card
```scss
.property-card.vip {
    border: 2px solid $vip-gold;
    position: relative;
    
    &::after {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        background: $vip-gradient;
        border-radius: 18px;
        z-index: -1;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    &:hover::after {
        opacity: 0.1;
    }
    
    .vip-badge {
        position: absolute;
        top: $space-4;
        right: $space-4;
        background: $vip-gradient;
        color: white;
        padding: $space-2 $space-3;
        border-radius: 20px;
        font-size: $font-xs;
        font-weight: $font-bold;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        z-index: 2;
        animation: vip-glow 2s ease-in-out infinite alternate;
    }
    
    .property-price {
        background: $vip-gradient;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
}

@keyframes vip-glow {
    from { box-shadow: 0 0 5px rgba(251, 191, 36, 0.5); }
    to { box-shadow: 0 0 20px rgba(251, 191, 36, 0.8); }
}
```

### Modern Property Type Cards
```scss
.property-type-card {
    background: white;
    border-radius: 20px;
    padding: $space-8;
    text-align: center;
    transition: all 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    border: 1px solid $secondary-light;
    position: relative;
    overflow: hidden;
    
    &::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.1), transparent);
        transition: left 0.5s ease;
    }
    
    &:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        border-color: $secondary-blue;
        
        &::before {
            left: 100%;
        }
        
        .type-icon {
            transform: scale(1.2) rotate(5deg);
            background: linear-gradient(135deg, $secondary-blue, #1976d2);
        }
        
        .type-count {
            color: $secondary-blue;
        }
    }
    
    .type-icon {
        width: 80px;
        height: 80px;
        background: linear-gradient(135deg, $primary-medium, $primary-light);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto $space-6;
        color: white;
        font-size: $font-3xl;
        transition: all 0.4s ease;
    }
    
    .type-title {
        font-size: $font-xl;
        font-weight: $font-semibold;
        color: $text-primary;
        margin-bottom: $space-3;
    }
    
    .type-description {
        color: $text-secondary;
        line-height: $leading-relaxed;
        margin-bottom: $space-4;
        font-size: $font-sm;
    }
    
    .type-count {
        font-size: $font-lg;
        font-weight: $font-bold;
        color: $text-primary;
        transition: color 0.3s ease;
    }
}
```

---

## 🎯 Специализирани секции

### Hero Section с геометрични елементи
```scss
.hero-section {
    background: $hero-gradient;
    color: white;
    padding: $space-24 0;
    position: relative;
    overflow: hidden;
    min-height: 100vh;
    display: flex;
    align-items: center;
    
    &::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: $hero-overlay;
        z-index: 1;
    }
    
    &::after {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 80%;
        height: 200%;
        background: url("data:image/svg+xml,%3Csvg width='100' height='100' xmlns='http://www.w3.org/2000/svg'%3E%3Cdefs%3E%3Cpattern id='grid' width='20' height='20' patternUnits='userSpaceOnUse'%3E%3Cpath d='M 20 0 L 0 0 0 20' fill='none' stroke='rgba(255,255,255,0.1)' stroke-width='1'/%3E%3C/pattern%3E%3C/defs%3E%3Crect width='100' height='100' fill='url(%23grid)'/%3E%3C/svg%3E");
        transform: rotate(45deg);
        z-index: 1;
        animation: float 20s ease-in-out infinite;
    }
    
    .hero-content {
        position: relative;
        z-index: 2;
        text-align: center;
        max-width: 900px;
        margin: 0 auto;
    }
    
    .hero-title {
        font-size: clamp(2.5rem, 5vw, 4rem);
        font-weight: 200;
        margin-bottom: $space-6;
        line-height: 1.1;
        letter-spacing: -0.02em;
        
        .highlight {
            background: linear-gradient(45deg, #60a5fa, #34d399);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    }
    
    .hero-subtitle {
        font-size: $font-xl;
        font-weight: 300;
        margin-bottom: $space-8;
        opacity: 0.9;
        max-width: 600px;
        margin-left: auto;
        margin-right: auto;
        line-height: $leading-relaxed;
    }
    
    .hero-actions {
        display: flex;
        gap: $space-4;
        justify-content: center;
        flex-wrap: wrap;
        
        @media (max-width: 768px) {
            flex-direction: column;
            align-items: center;
        }
    }
}

@keyframes float {
    0%, 100% { transform: rotate(45deg) translateY(0px); }
    50% { transform: rotate(45deg) translateY(-20px); }
}
```

### CTA Section с индустриални елементи
```scss
.cta-section {
    background: linear-gradient(135deg, $primary-dark 0%, $primary-medium 100%);
    color: white;
    padding: $space-20 0;
    position: relative;
    overflow: hidden;
    
    &::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='rgba(255,255,255,0.03)' fill-opacity='1'%3E%3Ccircle cx='30' cy='30' r='2'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        z-index: 1;
    }
    
    .cta-content {
        position: relative;
        z-index: 2;
        text-align: center;
        max-width: 800px;
        margin: 0 auto;
    }
    
    .cta-title {
        font-size: $font-4xl;
        font-weight: $font-semibold;
        margin-bottom: $space-6;
        line-height: $leading-tight;
    }
    
    .cta-description {
        font-size: $font-lg;
        font-weight: 300;
        margin-bottom: $space-8;
        opacity: 0.9;
        line-height: $leading-relaxed;
    }
    
    .cta-button {
        background: linear-gradient(45deg, #ffffff, #f8fafc);
        color: $primary-dark;
        padding: $space-4 $space-8;
        border-radius: 50px;
        font-weight: $font-semibold;
        font-size: $font-lg;
        text-decoration: none;
        display: inline-block;
        transition: all 0.4s ease;
        border: none;
        cursor: pointer;
        position: relative;
        overflow: hidden;
        
        &::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(59, 130, 246, 0.4), transparent);
            transition: left 0.5s ease;
        }
        
        &:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            
            &::before {
                left: 100%;
            }
        }
    }
}
```

---

## 📊 Статистики и индикатори

### Animated Counter
```scss
.stats-section {
    background: $secondary-bg;
    padding: $space-20 0;
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: $space-8;
        max-width: 1200px;
        margin: 0 auto;
    }
    
    .stat-item {
        text-align: center;
        padding: $space-8;
        background: white;
        border-radius: 16px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        
        &:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.12);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, $secondary-blue, #1976d2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto $space-4;
            color: white;
            font-size: $font-2xl;
        }
        
        .stat-number {
            font-size: $font-4xl;
            font-weight: $font-bold;
            color: $secondary-blue;
            display: block;
            margin-bottom: $space-2;
            counter-reset: num var(--num);
            
            &::after {
                content: counter(num);
            }
        }
        
        .stat-label {
            font-size: $font-lg;
            color: $text-secondary;
            font-weight: $font-medium;
        }
    }
}
```

### Status Indicators
```scss
.status-indicator {
    display: inline-flex;
    align-items: center;
    padding: $space-1 $space-3;
    border-radius: 20px;
    font-size: $font-sm;
    font-weight: $font-medium;
    
    &::before {
        content: '';
        width: 8px;
        height: 8px;
        border-radius: 50%;
        margin-right: $space-2;
    }
    
    &.available {
        background: rgba(16, 185, 129, 0.1);
        color: #059669;
        
        &::before {
            background: #10b981;
            animation: pulse-green 2s infinite;
        }
    }
    
    &.sold {
        background: rgba(239, 68, 68, 0.1);
        color: #dc2626;
        
        &::before {
            background: #ef4444;
        }
    }
    
    &.reserved {
        background: rgba(245, 158, 11, 0.1);
        color: #d97706;
        
        &::before {
            background: #f59e0b;
            animation: pulse-yellow 2s infinite;
        }
    }
}

@keyframes pulse-green {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}

@keyframes pulse-yellow {
    0%, 100% { opacity: 1; }
    50% { opacity: 0.5; }
}
```

---

## ⚡ SEO и Performance оптимизации

### Critical CSS стратегия
```scss
/* Критичен CSS за първо зареждане - inline в <head> */
.critical-styles {
    // Hero section основни стилове
    .hero-section {
        background: $hero-gradient;
        color: white;
        min-height: 100vh;
        display: flex;
        align-items: center;
    }
    
    // Navigation основни стилове
    .header {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 1000;
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(10px);
    }
    
    // Container за първоначално layout
    .container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 $space-4;
    }
}
```

### Lazy Loading стилове
```scss
.lazy-load {
    opacity: 0;
    transition: opacity 0.3s ease;
    
    &.loaded {
        opacity: 1;
    }
}

.lazy-image {
    background: $secondary-light;
    position: relative;
    
    &::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 40px;
        height: 40px;
        margin: -20px 0 0 -20px;
        border: 3px solid $secondary-light;
        border-top-color: $secondary-blue;
        border-radius: 50%;
        animation: spin 1s linear infinite;
    }
    
    img {
        opacity: 0;
        transition: opacity 0.3s ease;
        
        &.loaded {
            opacity: 1;
        }
    }
}

@keyframes spin {
    to { transform: rotate(360deg); }
}
```

### Font Loading оптимизация
```scss
// Font display strategy
@font-face {
    font-family: 'Inter';
    src: url('/fonts/inter-var.woff2') format('woff2');
    font-display: swap; // Показва fallback font докато зарежда
    font-weight: 100 900;
}

// Fallback font stack
.font-loading {
    font-family: 'Inter', system-ui, -apple-system, sans-serif;
    
    // Prevent layout shift while loading
    font-size-adjust: 0.5;
}
```

### Performance utility classes
```scss
.gpu-accelerated {
    transform: translateZ(0);
    backface-visibility: hidden;
    perspective: 1000px;
}

.optimize-animations {
    will-change: transform;
    contain: layout;
}

// Reset will-change след анимация
.animation-complete {
    will-change: auto;
}
```

### SEO-friendly стилове
```scss
// Structured data визуализация
.structured-data {
    .property-schema {
        position: relative;
        
        // Скрити SEO данни
        .schema-data {
            position: absolute;
            left: -9999px;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }
    }
}

// Print стилове за подобрено SEO
@media print {
    .no-print {
        display: none !important;
    }
    
    .print-only {
        display: block !important;
    }
    
    body {
        background: white !important;
        color: black !important;
        font-size: 12pt;
        line-height: 1.4;
    }
    
    .property-card {
        break-inside: avoid;
        border: 1px solid #ccc;
        padding: 10pt;
        margin-bottom: 10pt;
    }
}
```

---

## 🔒 Security и Privacy стилове

### GDPR Cookie Notice
```scss
.cookie-notice {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(15, 23, 42, 0.95);
    color: white;
    padding: $space-4;
    z-index: 2000;
    backdrop-filter: blur(10px);
    transform: translateY(100%);
    transition: transform 0.3s ease;
    
    &.visible {
        transform: translateY(0);
    }
    
    .cookie-content {
        max-width: 1200px;
        margin: 0 auto;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: $space-4;
        
        @media ($mobile) {
            flex-direction: column;
            text-align: center;
        }
    }
    
    .cookie-text {
        flex: 1;
        font-size: $font-sm;
        line-height: $leading-relaxed;
        
        a {
            color: #60a5fa;
            text-decoration: underline;
            
            &:hover {
                color: #93c5fd;
            }
        }
    }
    
    .cookie-actions {
        display: flex;
        gap: $space-2;
        
        @media ($mobile) {
            width: 100%;
            justify-content: center;
        }
    }
}
```

### Privacy-focused анимации
```scss
// Respect reduced motion preference
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
}

// High contrast mode support
@media (prefers-contrast: high) {
    .card {
        border: 2px solid;
        background: ButtonFace;
        color: ButtonText;
    }
    
    .btn-primary {
        background: ButtonText;
        color: ButtonFace;
        border: 2px solid ButtonText;
    }
}

// Dark mode preference
@media (prefers-color-scheme: dark) {
    :root {
        --bg-primary: #{$primary-dark};
        --bg-secondary: #{$primary-medium};
        --text-primary: #{$text-white};
        --text-secondary: #cbd5e1;
        --border-color: #475569;
    }
    
    body {
        background: var(--bg-primary);
        color: var(--text-primary);
    }
    
    .card {
        background: var(--bg-secondary);
        border-color: var(--border-color);
        color: var(--text-primary);
    }
}
```

---

## 🎯 Advanced Interactive Components

### Search Component
```scss
.advanced-search {
    background: white;
    border-radius: 16px;
    padding: $space-6;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.1);
    border: 1px solid $secondary-light;
    
    .search-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: $space-6;
        
        .search-title {
            font-size: $font-xl;
            font-weight: $font-semibold;
            color: $text-primary;
        }
        
        .search-toggle {
            background: none;
            border: none;
            color: $secondary-blue;
            cursor: pointer;
            font-weight: $font-medium;
            
            &:hover {
                text-decoration: underline;
            }
        }
    }
    
    .search-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: $space-4;
        
        @media ($mobile) {
            grid-template-columns: 1fr;
        }
    }
    
    .search-field {
        position: relative;
        
        .search-input {
            width: 100%;
            padding: $space-3 $space-4;
            border: 2px solid $secondary-light;
            border-radius: 8px;
            font-size: $font-base;
            transition: all 0.3s ease;
            
            &:focus {
                outline: none;
                border-color: $secondary-blue;
                box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
            }
        }
        
        .search-icon {
            position: absolute;
            right: $space-3;
            top: 50%;
            transform: translateY(-50%);
            color: $text-muted;
            pointer-events: none;
        }
    }
    
    .search-filters {
        display: flex;
        flex-wrap: wrap;
        gap: $space-2;
        margin-top: $space-4;
        padding-top: $space-4;
        border-top: 1px solid $secondary-light;
    }
    
    .filter-tag {
        background: rgba(59, 130, 246, 0.1);
        color: $secondary-blue;
        padding: $space-1 $space-3;
        border-radius: 20px;
        font-size: $font-sm;
        font-weight: $font-medium;
        display: flex;
        align-items: center;
        gap: $space-2;
        
        .remove-filter {
            background: none;
            border: none;
            color: $secondary-blue;
            cursor: pointer;
            font-size: $font-xs;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            
            &:hover {
                background: rgba(59, 130, 246, 0.2);
            }
        }
    }
}
```

### Testimonials Carousel
```scss
.testimonials-carousel {
    padding: $space-20 0;
    background: $secondary-bg;
    overflow: hidden;
    
    .carousel-container {
        position: relative;
        max-width: 1000px;
        margin: 0 auto;
    }
    
    .testimonial-item {
        background: white;
        border-radius: 20px;
        padding: $space-8;
        text-align: center;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
        margin: 0 $space-4;
        
        .testimonial-content {
            font-size: $font-lg;
            line-height: $leading-relaxed;
            color: $text-primary;
            margin-bottom: $space-6;
            font-style: italic;
            
            &::before,
            &::after {
                content: '"';
                font-size: $font-3xl;
                color: $secondary-blue;
                font-weight: $font-bold;
            }
        }
        
        .testimonial-author {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: $space-4;
            
            .author-avatar {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                object-fit: cover;
                border: 3px solid $secondary-light;
            }
            
            .author-info {
                text-align: left;
                
                .author-name {
                    font-weight: $font-semibold;
                    color: $text-primary;
                    margin-bottom: $space-1;
                }
                
                .author-title {
                    font-size: $font-sm;
                    color: $text-secondary;
                }
            }
        }
    }
    
    .carousel-nav {
        display: flex;
        justify-content: center;
        gap: $space-2;
        margin-top: $space-8;
        
        .nav-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: rgba(59, 130, 246, 0.3);
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            
            &.active {
                background: $secondary-blue;
                transform: scale(1.2);
            }
        }
    }
}
```

---

## 🔧 Performance оптимизации

### Critical CSS
```scss
/* Критичен CSS за първото зареждане */
.above-fold {
    contain: layout;
    
    .hero-section,
    .property-types-section {
        will-change: transform;
    }
}

/* Lazy loading за некритични стилове */
.below-fold {
    contain: content;
}
```

### CSS Grid оптимизации
```scss
.optimized-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: $space-6;
    contain: layout;
    
    @supports (container-type: inline-size) {
        container-type: inline-size;
    }
    
    .grid-item {
        contain: layout style;
        
        @container (min-width: 350px) {
            .item-content {
                padding: $space-8;
            }
        }
    }
}
```

---

## 🎨 Advanced Visual Effects

### Backdrop Effects
```scss
.glass-effect {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    
    @supports not (backdrop-filter: blur(10px)) {
        background: rgba(255, 255, 255, 0.8);
    }
}

.frosted-glass {
    background: rgba(255, 255, 255, 0.25);
    backdrop-filter: blur(15px) saturate(180%);
    -webkit-backdrop-filter: blur(15px) saturate(180%);
    border: 1px solid rgba(255, 255, 255, 0.3);
    box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
}
```

### Modern Gradients
```scss
.gradient-mesh {
    background: 
        radial-gradient(circle at 20% 80%, rgba(59, 130, 246, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 80% 20%, rgba(16, 185, 129, 0.3) 0%, transparent 50%),
        radial-gradient(circle at 40% 40%, rgba(251, 191, 36, 0.2) 0%, transparent 50%);
}

.animated-gradient {
    background: linear-gradient(-45deg, #3b82f6, #1976d2, #10b981, #059669);
    background-size: 400% 400%;
    animation: gradient-shift 15s ease infinite;
}

@keyframes gradient-shift {
    0% { background-position: 0% 50%; }
    50% { background-position: 100% 50%; }
    100% { background-position: 0% 50%; }
}
```

---

## 🚀 PWA и Service Worker интеграция

### Manifest интеграция
```scss
.pwa-install-prompt {
    position: fixed;
    bottom: $space-4;
    left: $space-4;
    right: $space-4;
    background: white;
    border-radius: 12px;
    padding: $space-4;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    z-index: 1000;
    transform: translateY(100px);
    transition: transform 0.3s ease;
    
    &.visible {
        transform: translateY(0);
    }
    
    .install-content {
        display: flex;
        align-items: center;
        gap: $space-4;
    }
    
    .app-icon {
        width: 60px;
        height: 60px;
        border-radius: 12px;
        flex-shrink: 0;
    }
    
    .install-text {
        flex: 1;
        
        .install-title {
            font-weight: $font-semibold;
            color: $text-primary;
            margin-bottom: $space-1;
        }
        
        .install-description {
            font-size: $font-sm;
            color: $text-secondary;
        }
    }
    
    .install-actions {
        display: flex;
        gap: $space-2;
    }
}
```

---

**Последно обновление:** Декември 2024
**Версия:** 3.0
**Статус:** ✅ Пълно модернизиран стил гайд с всички най-нови подобрения

---

> **Забележка:** Този обновен стил гайд включва всички модернизации, интерактивни ефекти, performance оптимизации и advanced визуални елементи, които бяха имплементирани в процеса на преработката на началната страница. Всички компоненти са тествани и готови за използване в цялостния проект.
