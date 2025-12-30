#!/bin/bash

# Скрипт для сборки dev релиза для тестирования
# Использование: ./build/build-dev-release.sh [версия]
# или из корня проекта: ./build/build-dev-release.sh [версия]

set -e

VERSION=${1:-dev}
RELEASE_NAME="amocrm-api-php-v4-${VERSION}"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(cd "${SCRIPT_DIR}/.." && pwd)"
BUILD_DIR="${SCRIPT_DIR}/releases"
RELEASE_DIR="${BUILD_DIR}/${RELEASE_NAME}"

echo "=========================================="
echo "Сборка dev релиза: ${RELEASE_NAME}"
echo "=========================================="

# Создаем директорию для сборки
rm -rf "${BUILD_DIR}"
mkdir -p "${RELEASE_DIR}"

# Копируем необходимые файлы
echo "Копирование файлов..."
cp -r "${PROJECT_ROOT}/src" "${RELEASE_DIR}/"
cp -r "${PROJECT_ROOT}/tests" "${RELEASE_DIR}/"
cp "${PROJECT_ROOT}/composer.json" "${RELEASE_DIR}/"
cp "${PROJECT_ROOT}/composer.lock" "${RELEASE_DIR}/" 2>/dev/null || true
cp "${PROJECT_ROOT}/phpunit.xml" "${RELEASE_DIR}/"
cp "${PROJECT_ROOT}/README.md" "${RELEASE_DIR}/"
cp "${PROJECT_ROOT}/LICENSE" "${RELEASE_DIR}/"

# Копируем assets если они есть
if [ -d "${PROJECT_ROOT}/assets" ]; then
    cp -r "${PROJECT_ROOT}/assets" "${RELEASE_DIR}/"
fi

# Устанавливаем зависимости без dev зависимостей для production релиза
# Для dev релиза устанавливаем все зависимости
echo "Установка зависимостей..."
cd "${RELEASE_DIR}"
composer install --no-dev --optimize-autoloader --no-interaction
cd "${SCRIPT_DIR}"

# Создаем архив
echo "Создание архива..."
ARCHIVE_NAME="${RELEASE_NAME}.zip"
cd "${BUILD_DIR}"
if command -v zip &> /dev/null; then
    zip -r "${ARCHIVE_NAME}" "${RELEASE_NAME}" > /dev/null
    echo "✓ Архив создан: ${BUILD_DIR}/${ARCHIVE_NAME}"
elif command -v 7z &> /dev/null; then
    7z a "${ARCHIVE_NAME}" "${RELEASE_NAME}" > /dev/null
    echo "✓ Архив создан: ${BUILD_DIR}/${ARCHIVE_NAME}"
else
    echo "⚠ zip или 7z не найдены. Создайте архив вручную из директории ${RELEASE_DIR}"
fi
cd "${SCRIPT_DIR}"

# Показываем информацию о релизе
echo ""
echo "=========================================="
echo "Dev релиз собран успешно!"
echo "=========================================="
echo "Директория: ${RELEASE_DIR}"
if [ -f "${BUILD_DIR}/${ARCHIVE_NAME}" ]; then
    echo "Архив: ${BUILD_DIR}/${ARCHIVE_NAME}"
    ls -lh "${BUILD_DIR}/${ARCHIVE_NAME}" | awk '{print "Размер: " $5}'
fi
echo ""
echo "Для тестирования:"
echo "1. Распакуйте архив в тестовую директорию"
echo "2. Установите зависимости: composer install"
echo "3. Запустите тесты: vendor/bin/phpunit"
echo "=========================================="

