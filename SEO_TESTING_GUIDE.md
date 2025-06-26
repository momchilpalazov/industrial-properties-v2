# SEO Optimization Testing Guide

## 🧪 Тестване на SEO функционалностите

### 1. **Sitemap тестване**

Отворете следните URLs в браузъра за да тествате sitemaps:

```
http://localhost:8000/sitemap.xml
http://localhost:8000/sitemap-main.xml
http://localhost:8000/sitemap-bg.xml
http://localhost:8000/sitemap-en.xml
http://localhost:8000/sitemap-de.xml
http://localhost:8000/sitemap-ru.xml
http://localhost:8000/sitemap-images.xml
```

### 2. **Meta Tags тестване**

#### 🏠 Homepage:
- Отворете: `http://localhost:8000/bg`
- Прегледайте source код (Ctrl+U)
- Проверете:
  - `<meta name="description">`
  - `<meta name="keywords">`
  - `<link rel="canonical">`
  - `<link rel="alternate" hreflang="...">`
  - JSON-LD structured data

#### 🏢 Property Pages:
- Отворете някой имот
- Проверете:
  - Property-specific meta description
  - Open Graph изображения
  - Structured data за RealEstate
  - Breadcrumb schema

### 3. **Robots.txt тестване**

Отворете: `http://localhost:8000/robots.txt`

Трябва да видите:
- Правила за различни bots
- AI-specific crawlers (GPTBot, OpenAI-SearchBot, etc.)
- Sitemap линкове

### 4. **Structured Data валидиране**

#### Google Rich Results Test:
1. Отидете на: https://search.google.com/test/rich-results
2. Въведете URL на имот или homepage
3. Проверете за errors/warnings

#### Schema.org Validator:
1. Отидете на: https://validator.schema.org/
2. Въведете URL или копирайте JSON-LD кода
3. Валидирайте структурата

### 5. **Open Graph тестване**

#### Facebook Debugger:
1. Отидете на: https://developers.facebook.com/tools/debug/
2. Въведете URL на имот
3. Проверете OG meta tags и изображения

#### Twitter Card Validator:
1. Отидете на: https://cards-dev.twitter.com/validator
2. Въведете URL
3. Проверете preview

### 6. **Mobile-Friendly тестване**

#### Google Mobile-Friendly Test:
1. Отидете на: https://search.google.com/test/mobile-friendly
2. Въведете URLs
3. Проверете за mobile issues

### 7. **Page Speed тестване**

#### Google PageSpeed Insights:
1. Отидете на: https://pagespeed.web.dev/
2. Въведете URLs
3. Проверете Core Web Vitals

### 8. **International SEO тестване**

Проверете:
- hreflang tags работят правилно
- Language switcher functionality
- URL structure: `/bg/property/...`, `/en/property/...`
- Content localization

---

## 🚨 **Критични проверки**

### ✅ **ЗАДЪЛЖИТЕЛНО да работят:**

1. **Sitemap Index** - `/sitemap.xml` трябва да показва всички sitemap файлове
2. **Language Sitemaps** - всеки език да има свой sitemap
3. **Property Schema** - всеки имот да има правилна RealEstate schema
4. **Canonical URLs** - всяка страница да има canonical link
5. **hreflang** - правилни alternate language links

### ⚠️ **Ако нещо не работи:**

1. **Проверете cache:** `php bin/console cache:clear`
2. **Проверете routes:** `php bin/console debug:router`
3. **Проверете services:** `php bin/console debug:container`
4. **Проверете logs:** `var/log/dev.log`

---

## 📊 **Очаквани резултати**

След правилна имплементация трябва да видите:

### Google Search Console:
- Намаляване на sitemap errors
- Увеличаване на indexed pages
- Подобрени rich results

### Performance:
- По-добри Core Web Vitals
- По-бързо зареждане на страници
- Подобрена mobile experience

### Traffic:
- 20-30% увеличение в organic traffic за 2-3 месеца
- По-добро ранжиране за target keywords
- Повече clicks от search results

---

## 🔧 **Troubleshooting**

### Ако sitemap не работи:
```bash
# Проверете дали routes са зает
php bin/console debug:router | findstr sitemap

# Проверете дали има грешки в контролера
php bin/console debug:container SitemapService
```

### Ако structured data не се показва:
- Проверете JSON syntax с jsonlint.com
- Валидирайте с schema.org validator
- Проверете twig template syntax

### Ако meta tags не се показват:
- Проверете base.html.twig template
- Проверете blocks в child templates
- Clear cache и refresh

---

**Готово за тестване! 🚀**
