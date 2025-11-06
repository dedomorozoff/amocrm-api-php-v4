# Публикация dev релиза на Packagist.org

## Подготовка к публикации

### 1. Проверка composer.json

Убедитесь, что `composer.json` валиден:

```bash
composer validate
```

Текущий `composer.json` уже валиден ✓

### 2. Проверка Git репозитория

Убедитесь, что все изменения закоммичены и запушены в GitHub:

```bash
git status
git add .
git commit -m "Подготовка dev релиза"
git push origin dev  # или ваша ветка
```

## Публикация на Packagist.org

### Вариант 1: Пакет уже опубликован на Packagist

Если пакет уже существует на [Packagist](https://packagist.org/packages/dedomorozoff/amocrm-api-php-v4):

1. **Войдите на Packagist.org** и перейдите на страницу вашего пакета
2. **Нажмите "Update"** - Packagist автоматически обновит информацию из GitHub
3. Или дождитесь автоматического обновления (обычно происходит каждые несколько минут)

### Вариант 2: Публикация нового пакета

Если пакет еще не опубликован:

1. **Зарегистрируйтесь/войдите** на [Packagist.org](https://packagist.org/)
2. **Нажмите "Submit"** в меню
3. **Введите URL вашего GitHub репозитория:**
   ```
   https://github.com/dedomorozoff/amocrm-api-php-v4
   ```
4. Packagist автоматически обнаружит `composer.json` и опубликует пакет

## Создание dev версии для тестирования

### Способ 1: Использование dev ветки (рекомендуется для dev версий)

Packagist автоматически создает dev версии для всех веток, которые не являются тегами:

```bash
# Убедитесь, что вы на нужной ветке (например, dev)
git checkout dev

# Запушьте изменения
git push origin dev
```

После этого пакет будет доступен как:
```bash
composer require dedomorozoff/amocrm-api-php-v4:dev-dev
```

### Способ 2: Создание dev тега

Для более структурированного подхода создайте dev тег:

```bash
# Создайте тег с версией dev
git tag -a dev-1.0.0 -m "Dev release 1.0.0"
git push origin dev-1.0.0
```

После этого пакет будет доступен как:
```bash
composer require dedomorozoff/amocrm-api-php-v4:dev-1.0.0
```

### Способ 3: Использование конкретной ветки

Если вы хотите использовать конкретную ветку для тестирования:

```bash
# Создайте ветку для тестирования
git checkout -b feature/test-branch
git push origin feature/test-branch
```

После обновления на Packagist пакет будет доступен как:
```bash
composer require dedomorozoff/amocrm-api-php-v4:dev-feature/test-branch
```

## Установка dev версии в проекте

После публикации на Packagist, установите dev версию в вашем проекте:

### Для dev ветки:
```bash
composer require dedomorozoff/amocrm-api-php-v4:dev-dev
```

### Для конкретного dev тега:
```bash
composer require dedomorozoff/amocrm-api-php-v4:dev-1.0.0
```

### Для конкретной ветки:
```bash
composer require dedomorozoff/amocrm-api-php-v4:dev-feature/test-branch
```

### В composer.json:
```json
{
    "require": {
        "dedomorozoff/amocrm-api-php-v4": "dev-dev"
    }
}
```

Затем выполните:
```bash
composer update dedomorozoff/amocrm-api-php-v4
```

## Настройка автоматического обновления на Packagist

Для автоматического обновления пакета при каждом push в GitHub:

1. Перейдите на страницу вашего пакета на Packagist
2. Нажмите "Settings"
3. Включите GitHub Hook (если еще не включен)
4. Или добавьте webhook вручную в настройках GitHub репозитория:
   - URL: `https://packagist.org/api/github?username=ВАШ_ПОЛЬЗОВАТЕЛЬ`
   - Content type: `application/json`
   - Secret: получите на странице настроек пакета на Packagist

## Проверка публикации

После публикации проверьте:

1. **На странице пакета на Packagist** должны появиться все версии (включая dev)
2. **Проверьте установку:**
   ```bash
   composer show dedomorozoff/amocrm-api-php-v4
   ```
3. **Проверьте доступные версии:**
   ```bash
   composer show dedomorozoff/amocrm-api-php-v4 --all
   ```

## Важные замечания

- **Минимальная стабильность:** По умолчанию Composer не устанавливает dev версии. Чтобы разрешить dev версии, добавьте в `composer.json`:
  ```json
  {
      "minimum-stability": "dev",
      "prefer-stable": true
  }
  ```

- **Версионирование:** Для production используйте семантическое версионирование (например, `1.0.0`, `1.0.1`). Dev версии должны иметь префикс `dev-` или быть в dev ветках.

- **Обновление:** Packagist автоматически обновляет информацию о пакете при каждом push в GitHub (если настроен webhook).

## Текущее состояние

- ✅ `composer.json` валиден
- ✅ Git репозиторий настроен: `https://github.com/dedomorozoff/amocrm-api-php-v4.git`
- ✅ Текущая ветка: `dev`
- ✅ Пакет уже существует на Packagist (судя по README.md)

## Быстрые команды

```bash
# 1. Проверить статус
git status

# 2. Закоммитить изменения (если есть)
git add .
git commit -m "Описание изменений"

# 3. Запушить в GitHub
git push origin dev

# 4. Обновить на Packagist (вручную или автоматически)
# Перейдите на https://packagist.org/packages/dedomorozoff/amocrm-api-php-v4
# и нажмите "Update"

# 5. Установить dev версию в проекте
composer require dedomorozoff/amocrm-api-php-v4:dev-dev
```

## Дополнительные ресурсы

- [Packagist Documentation](https://packagist.org/)
- [Composer Documentation](https://getcomposer.org/doc/)
- [Semantic Versioning](https://semver.org/)

