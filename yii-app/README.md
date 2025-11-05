# Yii 2 Demo Application

Минимальное Yii 2 приложение для демонстрации модуля генерации сказок.

## Структура

```
yii-app/
├── config/          # Конфигурация приложения
├── models/          # Модели
├── modules/          # Модули (story модуль монтируется из ../yii-module)
├── web/             # Web документы
├── runtime/         # Runtime файлы (создаются автоматически)
├── vendor/          # Composer зависимости (устанавливаются в Docker)
└── docker/          # Docker конфигурация
```

## Запуск через Docker

Приложение автоматически запускается через docker-compose из корня проекта:

```bash
docker-compose up -d yii-app
```

Приложение будет доступно на http://localhost:8080

## Установка в существующее Yii 2 приложение

Если у вас уже есть Yii 2 приложение:

1. Скопируйте модуль:
   ```bash
   cp -r yii-module /path/to/your/yii2-app/modules/story
   ```

2. Добавьте в `config/web.php`:
   ```php
   'modules' => [
       'story' => [
           'class' => 'app\modules\story\StoryModule',
           'pythonApiUrl' => getenv('PYTHON_API_URL') ?: 'http://python-api:8000',
           'timeout' => 300,
       ],
   ],
   ```

3. Добавьте URL правила:
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

## Разработка

Для разработки файлы монтируются как volumes, изменения применяются сразу.

Установка зависимостей (если нужно):
```bash
docker-compose exec yii-app composer install
```

## Примечания

Это минимальная демонстрационная структура. В продакшене:
- Используйте базу данных для сессий и кеша
- Настройте правильные права доступа
- Отключите debug режим
- Используйте HTTPS

