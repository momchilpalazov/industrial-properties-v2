# OpenAI API Setup Guide

## 🔑 Получаване на OpenAI API ключ

### Стъпка 1: Създаване на акаунт
1. Отидете на: https://platform.openai.com/
2. Кликнете "Sign up" или "Log in"
3. Регистрирайте се с email или Google/Microsoft акаунт

### Стъпка 2: Добавяне на платежен метод
1. След влизане, отидете на: https://platform.openai.com/settings/organization/billing
2. Кликнете "Add payment method"
3. Добавете кредитна/дебитна карта
4. Заредете минимум $5 (препоръчително $10-20)

### Стъпка 3: Създаване на API ключ
1. Отидете на: https://platform.openai.com/api-keys
2. Кликнете "Create new secret key"
3. Дайте име: "Industrial Properties Chatbot"
4. Копирайте ключа (започва с `sk-...`)

**⚠️ ВАЖНО:** Запазете ключа на сигурно място - няма да можете да го видите отново!

### Стъпка 4: Конфигуриране в проекта
1. Отворете файла `.env` в корена на проекта
2. Заменете реда:
   ```
   OPENAI_API_KEY=sk-your-real-openai-api-key-here
   ```
   с вашия истински ключ:
   ```
   OPENAI_API_KEY=sk-proj-abcd1234567890...
   ```
3. Запазете файла
4. Изчистете кеша: `php bin/console cache:clear`

## 💰 Цени (към юни 2025)

### GPT-3.5-turbo (препоръчително за чатбот)
- Input: $0.50 / 1M tokens
- Output: $1.50 / 1M tokens
- Средно 1 разговор = 200-500 tokens
- $5 стигат за 5,000-10,000 съобщения

### GPT-4 (по-мощен, но по-скъп)
- Input: $5.00 / 1M tokens  
- Output: $15.00 / 1M tokens
- $5 стигат за 500-1,000 съобщения

## 🧪 Тестване

### След конфигуриране на API ключа:
```powershell
# 1. Тест за health check
Invoke-WebRequest -Uri "http://localhost:9000/api/chatbot/health" -Method GET

# 2. Тест за конфигурация
Invoke-WebRequest -Uri "http://localhost:9000/api/chatbot/config" -Method GET

# 3. Тест за съобщение
Invoke-WebRequest -Uri "http://localhost:9000/api/chatbot/message" -Method POST -Body '{"message":"Търся склад в София до 100,000 евро","locale":"bg"}' -ContentType "application/json"
```

## 🔒 Сигурност

- **НИКОГА** не споделяйте API ключа си публично
- Добавете `.env` във `.gitignore` (вече е добавен)
- Използвайте rate limiting в production
- Мониторирайте употребата редовно

## 🚀 Ready for Production

След като имате API ключ, системата поддържа:
- ✅ Многоезични отговори (BG, EN, DE, RU)
- ✅ Интелигентно търсене на имоти
- ✅ Контекстуални препоръки
- ✅ Fallback отговори при грешки
- ✅ Rate limiting и error handling
- ✅ Conversation history
- ✅ Property matching и suggestion

## 📞 Поддръжка

За въпроси относно OpenAI API:
- Документация: https://platform.openai.com/docs
- Поддръжка: https://help.openai.com/
- Общност: https://community.openai.com/
