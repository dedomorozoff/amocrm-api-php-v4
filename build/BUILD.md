# Инструкция по сборке и публикации dev релиза

## Публикация на Packagist.org

Для публикации dev версии на Packagist, чтобы ее можно было устанавливать через `composer require`, см. файл [PUBLISH.md](PUBLISH.md).

## Быстрая сборка локального релиза

Для сборки dev релиза используйте скрипт из папки `build/`:

**Linux/Mac/Git Bash:**
```bash
./build/build-dev-release.sh [версия]
```

**Windows:**
```cmd
build\build-dev-release.bat [версия]
```

Например:
```bash
./build/build-dev-release.sh dev-1.0.0
```

Если версия не указана, будет использована версия `dev`.

## Ручная сборка

### 1. Установка зависимостей

```bash
composer install
```

### 2. Запуск тестов

```bash
vendor/bin/phpunit
```

### 3. Создание архива для релиза

Создайте архив со следующими файлами и директориями:
- `src/` - исходный код библиотеки
- `composer.json` - файл зависимостей
- `composer.lock` - файл блокировки версий (опционально)
- `README.md` - документация
- `LICENSE` - лицензия
- `phpunit.xml` - конфигурация тестов (для dev релиза)
- `tests/` - тесты (для dev релиза)
- `assets/` - ресурсы (если есть)

**Исключите из архива:**
- `vendor/` - зависимости (устанавливаются через composer)
- `build/` - директория сборки
- `.git/` - git репозиторий
- `.idea/` - настройки IDE
- `logs/`, `*.log` - логи
- `tokens/`, `cookies/`, `lock/` - временные файлы

### 4. Установка dev релиза в тестовом проекте

#### Вариант 1: Через локальный путь

Добавьте в `composer.json` вашего проекта:

```json
{
    "repositories": [
        {
            "type": "path",
            "url": "/путь/к/распакованному/релизу"
        }
    ],
    "require": {
        "dedomorozoff/amocrm-api-php-v4": "@dev"
    }
}
```

Затем выполните:
```bash
composer update dedomorozoff/amocrm-api-php-v4
```

#### Вариант 2: Через архив

Распакуйте архив в директорию `vendor/dedomorozoff/amocrm-api-php-v4` вашего проекта и выполните:

```bash
composer dump-autoload
```

#### Вариант 3: Через Git (если есть доступ к репозиторию)

```bash
composer require dedomorozoff/amocrm-api-php-v4:dev-branch-name
```

## Проверка установки

После установки проверьте, что библиотека работает:

```php
<?php
require 'vendor/autoload.php';

use AmoCRM\AmoAPI;

// Проверка загрузки классов
if (class_exists('AmoCRM\AmoAPI')) {
    echo "✓ Библиотека успешно установлена\n";
} else {
    echo "✗ Ошибка загрузки библиотеки\n";
}
```

## Запуск тестов после установки

```bash
cd vendor/dedomorozoff/amocrm-api-php-v4
composer install
vendor/bin/phpunit
```

## Структура dev релиза

```
amocrm-api-php-v4-dev/
├── src/
│   └── AmoCRM/
│       ├── AmoAPI.php
│       ├── AmoContact.php
│       └── ... (другие классы)
├── tests/
│   └── Unit/
│       └── AmoCRM/
│           └── ... (тесты)
├── composer.json
├── composer.lock
├── phpunit.xml
├── README.md
└── LICENSE
```

## Примечания

- Для production релиза используйте `composer install --no-dev` чтобы исключить dev зависимости
- Для dev релиза оставьте `tests/` и `phpunit.xml` для возможности запуска тестов
- Убедитесь, что все зависимости указаны в `composer.json`
- Проверьте, что версия PHP соответствует требованиям (>= 8.0)

