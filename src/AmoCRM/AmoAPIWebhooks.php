<?php
/**
 * Трейт AmoAPIWebhook. Содержит методы для добавления и удаления webhooks
 *
 * @author    andrey-tech, dedomorozoff
 * @copyright 2020 andrey-tech, 2024 dedomorozoff
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.0.0
 *
 * v1.0.0 (14.07.2020) Начальный релиз.
 */

declare(strict_types = 1);

namespace AmoCRM;

trait AmoAPIWebhooks
{
    /**
     * Добавляет один webhook или несколько webhooks (не более 100)
     * Обновлено для работы с API v4: обновлен эндпоинт на /api/v4/webhooks/subscribe
     * @param array $params Параметры webhooks
     * @param bool $returnResponse Вернуть ответ сервера amoCRM
     * @param string $subdomian Поддомен amoCRM
     * @return array|null
     * @see https://www.amocrm.com/developers/content/api/webhooks/
     */
    public static function addWebhooks(array $params, bool $returnResponse = false, $subdomain = null)
    {
        // Приведение к массиву webhooks
        if (! self::isNumericArray($params)) {
            $params = [ $params ];
        }
        $params = [
            'subscribe' => $params
        ];

        $response = self::request('/api/v4/webhooks/subscribe', 'POST', $params, $subdomain);

        if (! $returnResponse) {
            return self::getItems($response);
        }

        return $response;
    }

    /**
     * Удаляет один webhook или несколько webhooks (не более 100)
     * Обновлено для работы с API v4: обновлен эндпоинт на /api/v4/webhooks/unsubscribe
     * @param array $params Параметры webhooks
     * @param bool $returnResponse Вернуть ответ сервера amoCRM
     * @param string $subdomian Поддомен amoCRM
     * @return array|null
     * @see https://www.amocrm.com/developers/content/api/webhooks/
     */
    public static function deleteWebhooks(array $params, bool $returnResponse = false, $subdomain = null)
    {
        // Приведение к массиву webhooks
        if (! self::isNumericArray($params)) {
            $params = [ $params ];
        }
        $params = [
            'unsubscribe' => $params
        ];

        $response = self::request('/api/v4/webhooks/unsubscribe', 'POST', $params, $subdomain);

        if (! $returnResponse) {
            return self::getItems($response);
        }

        return $response;
    }

    /**
     * Проверяет является ли переменная НЕ ассоциативным массивом
     * @param  mixed  $variable Переменная
     * @return boolean
     */
    protected static function isNumericArray($variable) :bool
    {
        if (! is_array($variable)) {
            return false;
        }

        foreach (array_keys($variable) as $key) {
            if ($key !== (int) $key) {
                return false;
            }
        }

        return true;
    }
}
