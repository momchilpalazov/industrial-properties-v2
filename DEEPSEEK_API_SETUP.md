# DeepSeek AI Setup Guide (FREE Alternative)

## 🎉 DeepSeek - Безплатна алтернатива на OpenAI

DeepSeek предлага безплатен tier с много щедри лимити - отличен избор за започване!

### ✅ Предимства на DeepSeek:
- **100% безплатно** за първите 10M tokens
- **Много високо качество** - конкурира с GPT-3.5
- **Без кредитна карта** изискване
- **Бърза регистрация** - само с email

### 🚀 Как да получите DeepSeek API ключ:

#### Стъпка 1: Създаване на акаунт
1. Отидете на: https://platform.deepseek.com/
2. Кликнете "Sign Up" 
3. Регистрирайте се с email (без карта!)
4. Потвърдете email-а си

#### Стъпка 2: Създаване на API ключ
1. След влизане, отидете на: https://platform.deepseek.com/api_keys
2. Кликнете "Create API Key"
3. Дайте име: "Industrial Properties Chatbot"
4. Копирайте ключа (започва с `sk-...`)

#### Стъпка 3: Конфигуриране в проекта
1. Отворете `.env` файла
2. Заменете:
   ```
   DEEPSEEK_API_KEY=your_deepseek_api_key_here
   ```
   с вашия истински ключ:
   ```
   DEEPSEEK_API_KEY=sk-1234567890abcdef...
   ```
3. Запазете файла
4. Рестартирайте сървъра

### 💰 Безплатни лимити DeepSeek:
- **10M input tokens** безплатно месечно
- **1M output tokens** безплатно месечно  
- **Без expiry date** - безплатното остава завинаги
- За сравнение: това са ~20,000 чатбот разговора

### 🔄 Автоматично превключване:
Системата автоматично избира:
1. **DeepSeek (безплатно)** - ако е конфигуриран
2. **OpenAI** - като fallback ако DeepSeek не работи
3. **Fallback responses** - ако и двата не работят

### 🎛️ Ръчно превключване:
В чатбот widget-а може да превключвате между:
- 🆓 **DeepSeek** (безплатно)
- 💰 **OpenAI** (платено)

### ⚡ Тестване:

```powershell
# 1. Проверка на providers
Invoke-WebRequest -Uri "http://localhost:9000/api/chatbot/providers" -Method GET

# 2. Смяна на provider
Invoke-WebRequest -Uri "http://localhost:9000/api/chatbot/provider" -Method POST -Body '{"provider":"deepseek"}' -ContentType "application/json"

# 3. Тест съобщение
Invoke-WebRequest -Uri "http://localhost:9000/api/chatbot/message" -Method POST -Body '{"message":"Търся склад в София до 100,000 евро","locale":"bg"}' -ContentType "application/json"
```

### 🆚 Сравнение OpenAI vs DeepSeek:

| Характеристика | OpenAI GPT-3.5 | DeepSeek Chat |
|---|---|---|
| **Цена** | $0.002/1K tokens | Безплатно |
| **Качество** | Отлично | Много добро |
| **Скорост** | Бързо | Бързо |
| **Лимити** | Платен лимит | 10M tokens/месец |
| **Езици** | Отлично BG/EN/DE/RU | Добро BG/EN/DE/RU |
| **Регистрация** | Карта задължителна | Само email |

### 🔧 Технически детайли:

Системата използва стандартен OpenAI-compatible API, което означава:
- Лесно добавяне на нови провайдери
- Единен интерфейс за всички AI модели
- Автоматично fallback при грешки
- Smart provider selection (предпочита безплатни)

### 📈 Използване в Production:

За production сайт препоръчваме:
1. **Започнете с DeepSeek** (безплатно)
2. **Мониторирайте употребата** чрез admin panel
3. **При достигане на лимитите** - добавете OpenAI като backup
4. **Load balancing** между провайдърите

### 🔒 Сигурност:

- API ключовете се съхраняват в `.env` (не в git)
- Rate limiting на API заявките
- Input sanitization за всички съобщения
- Error handling без експозиране на internal детайли

## 🎯 Ready to Go!

С DeepSeek може веднага да тествате пълната AI функционалност без да плащате нищо! 🚀
