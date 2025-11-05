# Story Generator API (Python)

REST-API сервис для генерации сказок с использованием OpenAI API. Сервис возвращает потоковый ответ в формате Markdown.

## Установка

### 1. Создайте виртуальное окружение

```bash
cd python-api
python3 -m venv venv
source venv/bin/activate  # На Windows: venv\Scripts\activate
```

### 2. Установите зависимости

```bash
pip install -r requirements.txt
```

### 3. Настройте переменные окружения

Скопируйте `.env.example` в `.env` и укажите ваш OpenAI API ключ:

```bash
cp .env.example .env
```

Откройте `.env` и укажите ваш ключ:

```
OPENAI_API_KEY=sk-...
```

Или экспортируйте переменную окружения:

```bash
export OPENAI_API_KEY=sk-...
```

## Запуск

### Через Docker (Рекомендуется)

Самый простой способ запустить сервис:

```bash
# Из корня проекта
cd ..
docker-compose up -d python-api
```

Или соберите и запустите отдельно:

```bash
# Сборка образа
docker build -t story-generator-api .

# Запуск контейнера
docker run -d \
  --name story-api \
  -p 8000:8000 \
  -e OPENAI_API_KEY=sk-your-key-here \
  story-generator-api
```

### Режим разработки (без Docker)

```bash
uvicorn app.main:app --reload --host 0.0.0.0 --port 8000
```

Сервис будет доступен по адресу: http://localhost:8000

### Production режим

```bash
uvicorn app.main:app --host 0.0.0.0 --port 8000 --workers 4
```

## API Endpoints

### `GET /`

Проверка работоспособности API.

**Ответ:**
```json
{
  "message": "Story Generator API",
  "version": "1.0.0",
  "endpoints": {
    "generate_story": "POST /generate_story"
  }
}
```

### `GET /health`

Проверка здоровья сервиса и конфигурации OpenAI.

**Ответ:**
```json
{
  "status": "healthy",
  "openai_configured": true
}
```

### `POST /generate_story`

Генерирует сказку на основе входных параметров.

**Тело запроса (JSON):**
```json
{
  "age": 6,
  "language": "kk",
  "characters": ["Алдар Көсе", "Әйел Арстан"]
}
```

**Параметры:**
- `age` (integer, required): Возраст ребёнка. Должен быть больше 0.
- `language` (string, required): Язык сказки. Может быть `"ru"` (русский) или `"kk"` (казахский).
- `characters` (array of strings, required): Список персонажей. Минимум один элемент.

**Ответ:**
Потоковый ответ в формате Markdown с типом контента `text/markdown; charset=utf-8`.

**Пример ответа:**
```markdown
# Сказка для 6-летнего ребёнка

**Язык:** казахский

**Персонажи:** Алдар Көсе, Әйел Арстан


Жил-был Алдар Көсе...

---

_Сказка сгенерирована: 2025-01-21T12:34:56Z_
```

## Примеры запросов

### Используя curl

```bash
curl -X POST "http://localhost:8000/generate_story" \
  -H "Content-Type: application/json" \
  -d '{
    "age": 6,
    "language": "kk",
    "characters": ["Алдар Көсе", "Әйел Арстан"]
  }'
```

### Используя Python requests

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

## Обработка ошибок

API возвращает следующие HTTP коды:

- `200 OK` - Успешная генерация
- `400 Bad Request` - Некорректные входные данные (невалидные параметры)
- `500 Internal Server Error` - Ошибка при работе с OpenAI API или внутренняя ошибка сервера

**Пример ошибки:**
```json
{
  "detail": "Язык должен быть либо \"ru\", либо \"kk\""
}
```

## Структура проекта

```
python-api/
├── app/
│   ├── __init__.py
│   ├── main.py          # FastAPI приложение и endpoints
│   ├── models.py        # Pydantic модели для валидации
│   └── openai_client.py # Клиент для работы с OpenAI API
├── Dockerfile           # Образ Docker
├── .dockerignore        # Исключения при сборке Docker
├── requirements.txt     # Зависимости Python
├── example_request.py   # Пример использования API
└── README.md            # Документация
```

## Заметки

- Сервис использует модель `gpt-4o-mini` от OpenAI. Вы можете изменить модель в `app/openai_client.py`.
- Для работы требуется активный API ключ OpenAI.
- Потоковый ответ позволяет получать текст сказки по мере генерации, что улучшает пользовательский опыт.

