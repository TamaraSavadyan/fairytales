# Генератор сказок

Проект состоит из двух компонентов:
1. **Python REST API** - сервис для генерации сказок с использованием OpenAI API
2. **Yii 2 модуль** - PHP модуль, выступающий как прокси к Python API с удобным веб-интерфейсом

## Структура проекта

```
fairytales/
├── python-api/          # Python FastAPI сервис
│   ├── app/
│   │   ├── main.py      # FastAPI приложение
│   │   ├── models.py    # Модели валидации
│   │   └── openai_client.py  # Клиент OpenAI
│   ├── Dockerfile
│   ├── requirements.txt
│   └── README.md
├── yii-module/          # Yii 2 модуль (для интеграции)
│   ├── StoryModule.php
│   ├── controllers/
│   ├── models/
│   ├── views/
│   └── README.md
├── yii-app/             # Демо Yii 2 приложение
│   ├── config/
│   ├── controllers/
│   ├── models/
│   ├── views/
│   ├── web/
│   ├── docker/
│   ├── Dockerfile
│   └── README.md
├── docker-compose.yml   # Конфигурация Docker Compose
└── README.md            
```

### Запуск через Docker (Рекомендуется)

Самый простой способ запустить приложение - использовать Docker Compose:

1. **Создайте файл `.env` в корне проекта:**
   ```bash
   # Скопируйте пример файла
   cp env.example .env
   
   # Или создайте вручную
   echo "OPENAI_API_KEY=sk-your-key-here" > .env
   ```
   
   Отредактируйте `.env` файл и укажите ваш OpenAI API ключ. Все переменные окружения описаны в файле `env.example`.

2. **Запустите все сервисы:**
   ```bash
   docker-compose up -d
   ```
   
   Это запустит:
   - **Python API** на http://localhost:8000
   - **Yii 2 приложение** на http://localhost:8080

3. **Проверьте работу:**
   ```bash
   # Python API
   curl http://localhost:8000/health
   
   # Yii 2 приложение (откройте в браузере)
   open http://localhost:8080/story
   ```

4. **Остановка контейнеров:**
   ```bash
   docker-compose down
   ```

5. **Просмотр логов:**
   ```bash
   # Логи всех сервисов
   docker-compose logs -f
   
   # Логи только Python API
   docker-compose logs -f python-api
   
   # Логи только PHP приложения
   docker-compose logs -f yii-app
   ```

**Для разработки** (с автоперезагрузкой при изменении кода):
```bash
docker-compose up
```

**Запуск только Python API:**
```bash
docker-compose up -d python-api
```

### Ручная установка (без Docker)

### 1. Настройка Python API

```bash
cd python-api

# Создайте виртуальное окружение
python3 -m venv venv
source venv/bin/activate  # На Windows: venv\Scripts\activate

# Установите зависимости
pip install -r requirements.txt

# Настройте API ключ OpenAI
export OPENAI_API_KEY=sk-your-key-here
# или создайте .env файл с переменной OPENAI_API_KEY

# Запустите сервер
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000
```

Сервис будет доступен по адресу: http://localhost:8000

**Проверка работы:**
```bash
curl http://localhost:8000/health
```

**Пример запроса:**
```bash
python3 example_request.py --age 6 --language kk --characters "Алдар Көсе" "Әйел Арстан"
```

Подробная документация: [python-api/README.md](python-api/README.md)

### 2. Настройка Yii 2 модуля

#### Через Docker (демо приложение включено)

Если используете Docker Compose, всё уже настроено! Откройте:
- **http://localhost:8080/story** - форма генерации сказок

#### Установка в существующее Yii 2 приложение

1. Скопируйте `yii-module` в `modules/story`:
   ```bash
   cp -r yii-module /path/to/your/yii2-app/modules/story
   ```

2. Зарегистрируйте модуль в `config/web.php`:

```php
'modules' => [
    'story' => [
        'class' => 'app\modules\story\StoryModule',
        'pythonApiUrl' => 'http://python-api:8000', // В Docker сети
        // или 'http://localhost:8000', // Если на хосте
        'timeout' => 300,
    ],
],
```

3. (Опционально) Настройте URL правила в `config/web.php`:

```php
'urlManager' => [
    'enablePrettyUrl' => true,
    'showScriptName' => false,
    'rules' => [
        'story' => 'story/default/index',
        'story/result' => 'story/default/result',
        'story/stream' => 'story/default/stream',
    ],
],
```

4. Доступ к модулю: http://your-domain.com/story

**Важно:** Убедитесь, что Python API запущен и доступен по указанному адресу.

Подробная документация: [yii-module/README.md](yii-module/README.md)

## Пример использования

### Через веб-интерфейс (Yii 2 модуль)

1. Откройте форму по адресу `/story`
2. Заполните параметры:
   - Возраст: 6
   - Язык: Казахский
   - Персонажи: Алдар Көсе, Әйел Арстан
3. Нажмите "Сгенерировать сказку"
4. Сказка будет отображаться потоком по мере генерации

### Напрямую через Python API

```bash
curl -X POST "http://localhost:8000/generate_story" \
  -H "Content-Type: application/json" \
  -d '{
    "age": 6,
    "language": "kk",
    "characters": ["Алдар Көсе", "Әйел Арстан"]
  }'
```

Или через Python:

```python
import requests

response = requests.post(
    "http://localhost:8000/generate_story",
    json={
        "age": 6,
        "language": "kk",
        "characters": ["Алдар Көсе", "Әйел Арстан"]
    },
    stream=True
)

for chunk in response.iter_content(chunk_size=None, decode_unicode=True):
    if chunk:
        print(chunk, end='', flush=True)
```

## Функциональность

### Python API
- ✅ REST API endpoint `POST /generate_story`
- ✅ Валидация входных параметров (age, language, characters)
- ✅ Интеграция с OpenAI API с потоковой генерацией
- ✅ Возврат ответа в формате Markdown
- ✅ Обработка ошибок и таймаутов
- ✅ CORS поддержка для работы с фронтендом

### Yii 2 модуль
- ✅ Веб-форма для ввода параметров
- ✅ Валидация на стороне сервера
- ✅ Проксирование запросов к Python API
- ✅ Потоковый вывод результата в браузер
- ✅ Автоматическое преобразование Markdown в HTML
- ✅ Предустановленные персонажи для каждого языка
- ✅ Обработка ошибок с понятными сообщениями

## Технологии

### Python API
- FastAPI - современный веб-фреймворк
- OpenAI API - генерация текста
- Pydantic - валидация данных
- Uvicorn - ASGI сервер

### Yii 2 модуль
- Yii 2 Framework
- PHP 7.0+
- cURL для HTTP запросов
- JavaScript Fetch API для потокового чтения

## Требования

### Для запуска через Docker:
- Docker и Docker Compose
- OpenAI API ключ

### Для ручной установки:
- Python 3.8+
- OpenAI API ключ
- PHP 7.0+
- Yii 2 Framework
- cURL расширение для PHP

## Документация

- [Python API README](python-api/README.md) - подробная документация Python сервиса
- [Yii 2 модуль README](yii-module/README.md) - документация по установке и использованию модуля

## Разработка

### Тестирование Python API

```bash
cd python-api
uvicorn app.main:app --reload
```

Проверка здоровья:
```bash
curl http://localhost:8000/health
```

### Отладка

- Python API логирует ошибки в консоль
- Yii 2 модуль логирует ошибки через стандартный механизм Yii
- Проверьте конфигурацию `pythonApiUrl` если модуль не может подключиться к API

## Дополнительные возможности (не реализованы, но могут быть добавлены)

- Кеширование сказок
- История запросов
- Выбор стиля сказки
- Экспорт в PDF
- Сохранение в базу данных
- API для получения истории

## Лицензия

Проект создан в рамках тестового задания.
