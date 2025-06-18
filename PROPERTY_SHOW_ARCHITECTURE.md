# Property Show Assets Architecture

## 📁 Файлова структура

Съгласно добрите практики за архитектура, CSS и JavaScript кодът за property show страницата е изнесен в отделни файлове:

### SCSS Файлове
- `assets/styles/property-show.scss` - Основните стилове за property show страницата

### JavaScript Файлове  
- `assets/js/property-show-main.js` - Основната логика за property show страницата (Fancybox, form validation, etc.)

### Webpack конфигурация
Добавен е нов entry point в `webpack.config.js`:
```javascript
.addEntry('property-show-main', [
    './assets/js/property-show-main.js',
    './assets/styles/property-show.scss'
])
```

## 🎨 CSS Архитектура

### Съдържание на `property-show.scss`:
- **Basic styles** - Body, container, title styling
- **Carousel styles** - Улеснена навигация с controls
- **360° viewer** - Стилове за 360° изгледи
- **Property details** - Секция с детайли за имота
- **Share buttons** - Социални бутони за споделяне
- **Property description** - Описание на имота
- **Map section** - HERE Maps интеграция
- **Contact card** - Формата за контакт
- **Gallery styles** - Fancybox галерия
- **Mobile responsiveness** - Responsive дизайн
- **Accessibility** - Достъпност и performance оптимизации

### Подобрения:
- ✅ Добавена Safari съвместимост с `-webkit-backdrop-filter`
- ✅ Обединени всички стилове в един файл
- ✅ Запазена йерархията и организацията

## 💼 JavaScript Архитектура

### Съдържание на `property-show-main.js`:
- **PropertyShow class** - Основен клас за управление на функционалностите
- **Fancybox initialization** - Robust инициализация с fallbacks
- **Copy to clipboard** - ClipboardJS интеграция
- **Print functionality** - Печат на страницата
- **Form validation** - GDPR и reCAPTCHA валидация
- **AJAX form submission** - Изпращане на форми
- **Toast notifications** - Toastr интеграция

### Подобрения:
- ✅ OOP подход с ES6 classes
- ✅ Error handling и fallbacks
- ✅ Modular архитектура
- ✅ Separation of concerns

## 🔧 Как да използвате

### 1. Webpack Build
```bash
# Development
npm run dev

# Production
npm run build

# Watch mode
npm run watch
```

### 2. Twig Template
В `templates/property/show.html.twig`:
```twig
{% block stylesheets %}
    {{ parent() }}
    <!-- External libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css"/>
    <!-- ... other external CSS ... -->
    
    <!-- Project assets -->
    {{ encore_entry_link_tags('property-show') }}
    {{ encore_entry_link_tags('property-show-main') }}
{% endblock %}

{% block javascripts %}
    {{ parent() }}
    <!-- External libraries -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- ... other external JS ... -->
    
    <!-- Project assets -->
    {{ encore_entry_script_tags('property-show') }}
    {{ encore_entry_script_tags('property-show-main') }}
{% endblock %}
```

## 🔄 CSS Консолидация (Декември 2024)

### Проблемът:
Преди консолидацията имаше дублиране на CSS стилове между два файла:
- `assets/styles/property-show.scss` (основен файл - 666 реда)
- `assets/styles/components/property/show.scss` (компонентен файл - 287 реда)

### Дублирани стилове:
- `.gallery-overlay` - различна opacity (0.3 vs 0.5)
- `.property-main-image` - липсваше в основния файл
- `.property-thumbnail` - липсваше в основния файл 
- Print styles - липсваха в основния файл
- Map dimensions - липсваха в основния файл

### Решението:
✅ **Консолидирани всички стилове в `property-show.scss`**
✅ **Добавени липсващи стилове от inline CSS в template-а**
✅ **Изчистен `components/property/show.scss` (запазен за структура)**
✅ **Запазена webpack конфигурация (без промени)**
✅ **Запазени всички template файлове (без промени)**

### Подобрения:
- **Премахнато дублиране** - CSS стиловете са обединени
- **Добавени inline стилове** като CSS класове:
  ```scss
  .property-main-image {
      width: 100%; height: 400px; 
      object-fit: cover; cursor: pointer; 
      border-radius: 8px;
  }
  
  .property-thumbnail {
      width: 100%; height: 120px; 
      object-fit: cover; cursor: pointer; 
      border-radius: 4px;
  }
  
  .more-images-indicator {
      height: 120px; background: rgba(0,0,0,0.7); 
      color: white; border-radius: 4px; cursor: pointer;
  }
  ```

### Резултат:
- ✅ **Webpack build успешен** (Exit code: 0)
- ✅ **Без промени в структурата**
- ✅ **Без CSS конфликти**
- ✅ **По-добра производителност** (по-малко дублиращи се правила)

## 🎯 Предимства

### Производителност:
- **Кеширане** - Отделните файлове могат да се кешират независимо
- **Минификация** - Webpack автоматично минифицира в production
- **Code splitting** - По-ефективно зареждане

### Поддръжка:
- **Separation of concerns** - CSS и JS са разделени логически
- **Reusability** - Компонентите могат да се преизползват
- **Debugging** - По-лесно намиране на грешки

### Скалируемост:
- **Modularity** - Всеки компонент е в отделен файл
- **Organization** - Ясна файлова структура
- **Extensibility** - Лесно добавяне на нови функционалности

## 🔄 Миграция

Всички inline стилове и скриптове са премествени в external файлове без промяна на:
- ✅ Функционалността
- ✅ Структурата на HTML
- ✅ Логиката на приложението
- ✅ Existing код

## 📱 Съвместимост

- ✅ Всички съвременни браузъри
- ✅ Safari (с webkit prefixes)
- ✅ Mobile devices
- ✅ Accessibility features
- ✅ Performance optimizations
