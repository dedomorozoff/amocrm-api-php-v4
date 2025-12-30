@echo off
REM Скрипт для сборки dev релиза для тестирования (Windows)
REM Использование: build\build-dev-release.bat [версия]
REM или из корня проекта: build\build-dev-release.bat [версия]

setlocal enabledelayedexpansion

set VERSION=%1
if "%VERSION%"=="" set VERSION=dev
set RELEASE_NAME=amocrm-api-php-v4-%VERSION%
set SCRIPT_DIR=%~dp0
set PROJECT_ROOT=%SCRIPT_DIR%..
set BUILD_DIR=%SCRIPT_DIR%releases
set RELEASE_DIR=%BUILD_DIR%\%RELEASE_NAME%

echo ==========================================
echo Сборка dev релиза: %RELEASE_NAME%
echo ==========================================

REM Создаем директорию для сборки
if exist "%BUILD_DIR%" rmdir /s /q "%BUILD_DIR%"
mkdir "%BUILD_DIR%"
mkdir "%RELEASE_DIR%"

REM Копируем необходимые файлы
echo Копирование файлов...
xcopy /E /I /Y "%PROJECT_ROOT%\src" "%RELEASE_DIR%\src\"
xcopy /E /I /Y "%PROJECT_ROOT%\tests" "%RELEASE_DIR%\tests\"
copy /Y "%PROJECT_ROOT%\composer.json" "%RELEASE_DIR%\"
if exist "%PROJECT_ROOT%\composer.lock" copy /Y "%PROJECT_ROOT%\composer.lock" "%RELEASE_DIR%\"
copy /Y "%PROJECT_ROOT%\phpunit.xml" "%RELEASE_DIR%\"
copy /Y "%PROJECT_ROOT%\README.md" "%RELEASE_DIR%\"
copy /Y "%PROJECT_ROOT%\LICENSE" "%RELEASE_DIR%\"

REM Копируем assets если они есть
if exist "%PROJECT_ROOT%\assets" xcopy /E /I /Y "%PROJECT_ROOT%\assets" "%RELEASE_DIR%\assets\"

REM Устанавливаем зависимости
echo Установка зависимостей...
cd "%RELEASE_DIR%"
call composer install --no-dev --optimize-autoloader --no-interaction
cd "%SCRIPT_DIR%"

REM Создаем архив (требуется PowerShell или 7-Zip)
echo Создание архива...
set ARCHIVE_NAME=%RELEASE_NAME%.zip

REM Проверяем наличие PowerShell
where powershell >nul 2>&1
if %ERRORLEVEL% EQU 0 (
    powershell -Command "Compress-Archive -Path '%RELEASE_DIR%' -DestinationPath '%BUILD_DIR%\%ARCHIVE_NAME%' -Force"
    echo ✓ Архив создан: %BUILD_DIR%\%ARCHIVE_NAME%
) else (
    REM Проверяем наличие 7-Zip
    where 7z >nul 2>&1
    if %ERRORLEVEL% EQU 0 (
        7z a "%BUILD_DIR%\%ARCHIVE_NAME%" "%RELEASE_DIR%" >nul
        echo ✓ Архив создан: %BUILD_DIR%\%ARCHIVE_NAME%
    ) else (
        echo ⚠ PowerShell или 7z не найдены. Создайте архив вручную из директории %RELEASE_DIR%
    )
)

echo.
echo ==========================================
echo Dev релиз собран успешно!
echo ==========================================
echo Директория: %RELEASE_DIR%
if exist "%BUILD_DIR%\%ARCHIVE_NAME%" (
    echo Архив: %BUILD_DIR%\%ARCHIVE_NAME%
)
echo.
echo Для тестирования:
echo 1. Распакуйте архив в тестовую директорию
echo 2. Установите зависимости: composer install
echo 3. Запустите тесты: vendor\bin\phpunit
echo ==========================================

endlocal

