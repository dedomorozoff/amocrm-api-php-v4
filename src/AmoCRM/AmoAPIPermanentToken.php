<?php
/**
 * Трейт AmoAPIPermanentToken. Содержит методы для авторизации по долгосрочному токену amoCRM
 *
 * @author    dedomorozoff
 * @copyright 2024 dedomorozoff
 * @see https://github.com/dedomorozoff/amocrm-api-php-v4
 * @license   MIT
 *
 * @version 1.0.0
 *
 * v1.0.0 (2024) Начальный релиз. Поддержка долгосрочных токенов amoCRM для API v4
 *
 */

declare(strict_types=1);

namespace AmoCRM;

trait AmoAPIPermanentToken
{
    /**
     * Выполняет авторизацию по долгосрочному токену amoCRM
     * Долгосрочные токены создаются в настройках аккаунта amoCRM и не требуют OAuth flow
     * 
     * @param string $subdomain Поддомен amoCRM
     * @param string $token Долгосрочный токен amoCRM
     * @return void
     * @throws AmoAPIException
     */
    public static function permanentToken(string $subdomain, string $token): void
    {
        if (empty($token)) {
            throw new AmoAPIException("Долгосрочный токен не может быть пустым");
        }

        // Сохраняем поддомен, использованный при последней авторизации
        self::$lastSubdomain = $subdomain;

        // Сохраняем данные, использованные при последней авторизации для поддомена
        self::$lastAuth[$subdomain] = [
            'is_oauth2' => false,
            'is_permanent_token' => true,
            'permanent_token' => $token
        ];
    }

    /**
     * Проверяет, используется ли долгосрочный токен для указанного поддомена
     * @param string $subdomain Поддомен amoCRM
     * @return bool
     */
    protected static function isPermanentToken(string $subdomain): bool
    {
        return isset(self::$lastAuth[$subdomain]['is_permanent_token']) 
            && self::$lastAuth[$subdomain]['is_permanent_token'] === true;
    }
}

