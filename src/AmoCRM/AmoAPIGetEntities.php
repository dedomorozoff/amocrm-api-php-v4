<?php
/**
 * Трейт AmoAPIGetEntities. Содержит методы для получения списка сущностей.
 *
 * @author    andrey-tech
 * @copyright 2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.2.2
 *
 * v1.0.0 (24.04.2019) Начальный релиз.
 * v1.1.0 (08.08.2019) Добавлен метод getCatalogElementsQuantityInLead().
 * v1.1.1 (24.02.2020) Удален метод getCatalogElementsQuantityInLead() как более не поддерживаемый.
 * v1.2.0 (16.05.2020) Добавлен параметр $returnResponse во все методы
 * v1.2.1 (14.07.2020) Изменен порядок параметров $subdomain и $returnResponse в методах
 * v1.2.2 (11.08.2020) Исправлен метод getIncomingLeadsSummary()
 *
 */

declare(strict_types=1);

namespace AmoCRM;

trait AmoAPIGetEntities
{
    /**
     * Загружает компании
     * @return array | null
     */
    public static function getCompanies(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request(AmoCompany::URL, 'GET', $params, $subdomain);
        if (!$returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает сделки
     * @return array | null
     */
    public static function getLeads(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request(AmoLead::URL, 'GET', $params, $subdomain);
        if (!$returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает роли
     * @return array | null
     */
    public static function getRoles(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request(AmoRole::URL, 'GET', $params, $subdomain);
        if (!$returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает пользователей
     * @return array | null
     */
    public static function getUsers(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request(AmoUser::URL, 'GET', $params, $subdomain);
        if (!$returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает контакты
     * @return array | null
     */
    public static function getContacts(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request(AmoContact::URL, 'GET', $params, $subdomain);
        if (!$returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает задачи
     * @return array | null
     */
    public static function getTasks(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request(AmoTask::URL, 'GET', $params, $subdomain);
        if (!$returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает события
     * @return array | null
     */
    public static function getEvents(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request(AmoEvent::URL, 'GET', $params, $subdomain);
        if (!$returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает WebHooks
     * @return array | null
     */
    public static function getWebhooks(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request('/api/v4/webhooks', 'GET', $params, $subdomain);
        if (!$returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает Виджеты
     * @return array | null
     */
    public static function getWidgets(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request('/api/v4/widgets', 'GET', $params, $subdomain);
        if (!$returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает Виджет по коду
     * @return array | null
     */
    public static function getWidget(string $widgetCode)
    {
        $response = self::request("/api/v4/widgets/{$widgetCode}", 'GET');
        return $response;
    }


    /**
     * Устанавливает Виджет
     * @return array | null
     */
    public static function installWidgets(string $widgetCode)
    {
        $response = self::request("/api/v4/widgets/{$widgetCode}", 'POST');
        return $response;
    }

    /**
     * Удаляет Виджет
     * @return array | null
     */
    public static function deleteWidget(string $widgetCode)
    {
        $response = self::request("/api/v4/widgets/{$widgetCode}", 'DELETE');
        return $response;
    }


    /**
     * Запрашивает поля
     * @return array | null
     */
    public static function getFields($params = [], string $entityName = '', $subdomain = null)
    {
        $response = self::request("/api/v4/{$entityName}/custom_fields", 'GET', $params, $subdomain);
        return $response;
    }

    /**
     * Создает поле
     * @return array | null
     */
    public static function createField(array $params = [], string $entityName = '', $subdomain = null)
    {
        $response = self::request("/api/v4/{$entityName}/custom_fields", 'POST', $params, $subdomain);
        return $response;
    }


    /**
     * Редактирует поле
     * @return array | null
     */
    public static function updateField(array $params = [], string $entityName = '', $subdomain = null)
    {
        $response = self::request("/api/v4/{$entityName}/custom_fields", 'PATCH', $params, $subdomain);
        return $response;
    }


    /**
     * Удаляет поле
     * @return array | null
     */
    public static function deleteField(string $entityName = '', string $fieldID = '', $subdomain = null)
    {
        $response = self::request("/api/v4/{$entityName}/custom_fields/{$fieldID}", 'DELETE', [], $subdomain);
        return $response;
    }


    /**
     * Загружает неразобранные сделки
     * @return array | null
     */
    public static function getIncomingLeads(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request('/api/v4/leads/unsorted', 'GET', $params, $subdomain);
        if (!$returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает сводную информацию о неразобранных сделках
     * ВНИМАНИЕ: Этот метод использует API v2, так как аналог в v4 может отсутствовать или иметь другую структуру
     * @return array | null
     */
    public static function getIncomingLeadsSummary(array $params = [], bool $returnResponse = true, $subdomain = null)
    {
        return self::request('/api/v2/incoming_leads/summary', 'GET', $params, $subdomain);
    }

    /**
     * Загружает воронки продаж
     * @return array | null
     */
    public static function getPipelines(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request('/api/v4/leads/pipelines', 'GET', $params, $subdomain);
        if (!$returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает воронки продаж
     * @return array | null
     */
    public static function getStatuses(string $pipelineID, array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request("/api/v4/leads/pipelines/{$pipelineID}/statuses", 'GET', $params, $subdomain);
        if (!$returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает перечень каталогов аккаунта
     * @return array | null
     */
    public static function getCatalogs(array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request('/api/v4/catalogs', 'GET', $params, $subdomain);
        if (!$returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * Загружает перечень элементов каталога
     * @param int $catalogId ID каталога (обязательный параметр для v4 API)
     * @param array $params Параметры запроса
     * @param bool $returnResponse Вернуть ответ сервера amoCRM
     * @param string $subdomain Поддомен amoCRM
     * @return array | null
     */
    public static function getCatalogElements(int $catalogId, array $params = [], bool $returnResponse = false, $subdomain = null)
    {
        $response = self::request("/api/v4/catalogs/{$catalogId}/elements", 'GET', $params, $subdomain);
        if (!$returnResponse) {
            return self::getItems($response);
        }
        return $response;
    }

    /**
     * @throws AmoAPIException
     */
    public static function getGroups($params = [], string $entityName = '', $subdomain = null): ?array
    {
        return self::request("/api/v4/{$entityName}/custom_fields/groups", 'GET', $params, $subdomain);
    }
}
