<?php
/**
 * Класс AmoAPI. amoCRM REST API wrapper
 *
 * @author    andrey-tech
 * @copyright 2019-2020 andrey-tech
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 2.4.0
 *
 * v1.0.0 (24.04.2019) Начальный релиз
 * v1.1.0 (02.06.2019) Добавлены новые параметры, рефракторинг.
 * v1.2.0 (19.08.2019) Добавлен метод deleteObjects()
 * v1.2.1 (19.02.2020) Удален метод deleteObjects()
 * v2.0.0 (06.04.2020) Добавлена авторизация по протоколу OAuth 2.0.
 *                     Добавлены трейты AmoAPIAuth, AmoAPIOAuth2
 * v2.1.0 (10.05.2020) Добавлена проверка ответа сервера в метод saveObjects()
 * v2.2.0 (16.05.2020) Добавлен метод getItems(). Добавлен параметр $returnResponses в метод saveObjects()
 * v2.3.0 (22.05.2020) Добавлен метод deleteObjects() для удаления списков и их элементов
 * v2.3.1 (14.07.2020) Изменен порядок параметров $subdomain и $returnResponse в методах
 * v2.4.0 (09.08.2020) Добавлен метод saveObjectsWithLimit()
 *
 */

declare(strict_types=1);

namespace AmoCRM;

class AmoAPI
{
    // Трейт, формирующий GET/POST запросы к amoCRM
    use AmoAPIRequest;

    // Трейт методов для получения информации об аккаунте
    use AmoAPIGetAccount;

    // Трейт методов для получения сущностей
    use AmoAPIGetEntities;

    // Трейт методов для получения всех сущностей
    use AmoAPIGetAllEntities;

    // Трейт методов для авторизации по API-ключам пользователя
    use AmoAPIAuth;

    // Трейт методов для авторизации по протоколу OAuth 2.0
    use AmoAPIOAuth2;

    // Трейт методов для добавления и удаления webhooks
    use AmoAPIWebhooks;

    // Трейт методов для принятия или отклонение неразобранных заявок
    use AmoAPIIncomingLeads;

    /**
     * Возвращает массив параметров сущностей из ответа сервера amoCRM
     * Поддерживает структуру ответов как v2, так и v4 API
     * @param array|null $response Ответ сервера
     * @return array|null
     */
    public static function getItems($response)
    {
        if (!isset($response) || !is_array($response)) {
            return null;
        }

        // Если нет структуры _links или _embedded, возвращаем ответ как есть
        if (!isset($response['_links']) && !isset($response['_embedded'])) {
            return $response;
        }

        // Обработка вложенных сущностей из _embedded (v4 API)
        if (isset($response['_embedded'])) {
            $embedded = $response['_embedded'];

            // Приоритетная обработка основных сущностей
            if (isset($embedded['leads'])) return $embedded['leads'];
            if (isset($embedded['contacts'])) return $embedded['contacts'];
            if (isset($embedded['companies'])) return $embedded['companies'];
            if (isset($embedded['tasks'])) return $embedded['tasks'];
            if (isset($embedded['events'])) return $embedded['events'];
            if (isset($embedded['notes'])) return $embedded['notes'];
            if (isset($embedded['users'])) return $embedded['users'];
            if (isset($embedded['roles'])) return $embedded['roles'];
            if (isset($embedded['pipelines'])) return $embedded['pipelines'];
            if (isset($embedded['statuses'])) return $embedded['statuses'];
            if (isset($embedded['catalogs'])) return $embedded['catalogs'];
            if (isset($embedded['elements'])) return $embedded['elements']; // Элементы каталогов (v4)
            if (isset($embedded['unsorted'])) return $embedded['unsorted']; // Неразобранное (v4)
            if (isset($embedded['webhooks'])) return $embedded['webhooks']; // Webhooks (v4)
            if (isset($embedded['widgets'])) return $embedded['widgets'];
            if (isset($embedded['custom_fields'])) return $embedded['custom_fields'];
        }

        // Обработка для случаев, когда данные находятся в корне ответа
        // или когда используется структура v2 API
        if (isset($response['_links']['self']['href'])) {
            $href = $response['_links']['self']['href'];
            // Для некоторых эндпоинтов v4 данные возвращаются в корне ответа
            if (str_contains($href, 'contacts') && !isset($response['_embedded'])) {
                return $response;
            }
            if (str_contains($href, 'leads') && !isset($response['_embedded'])) {
                return $response;
            }
        }

        // Если ничего не найдено, возвращаем весь ответ
        return $response;
    }

    /**
     * Сохраняет (добавляет или обновляет) объекты AmoObject с ограничением на число сущностей в одном запросе к API amoCRM
     * @param array|object $amoObjects Массив объектов AmoObject или объект AmoObject
     * @param bool $returnResponses Возвращать массив ответов сервера amoCRM вместо массива параметров сущностей
     * @param string $subdomain Поддомен amoCRM
     * @param int $limit Максимальное число сущностей в одном запросе к API amoCRM
     * @return array
     * @throws AmoAPIException
     */
    public static function saveObjectsWithLimit(
        $amoObjects,
        bool $returnResponses = false,
        $subdomain = null,
        $limit = 250
    ): array
    {
        if (!is_array($amoObjects)) {
            $amoObjects = [$amoObjects];
        }

        if (count($amoObjects) < $limit) {
            return self::saveObjects($amoObjects, $returnResponses, $subdomain);
        }

        $responses = [];
        $amoObjectsChunks = array_chunk($amoObjects, $limit);
        foreach ($amoObjectsChunks as $amoObjectsChunk) {
            $responses = array_merge($responses, self::saveObjects($amoObjectsChunk, $returnResponses, $subdomain));
        }

        return $responses;
    }

    /**
     * Сохраняет (добавляет или обновляет) объекты AmoObject
     * Обновлено для работы с API v4: данные передаются напрямую без обертки add/update
     * @param array|object $amoObjects Массив объектов AmoObject или объект AmoObject
     * @param bool $returnResponses Возвращать массив ответов сервера amoCRM вместо массива параметров сущностей
     * @param string $subdomain Поддомен amoCRM
     * @return array
     * @throws AmoAPIException
     */
    public static function saveObjects($amoObjects, bool $returnResponses = false, $subdomain = null): array
    {
        if (!is_array($amoObjects)) {
            $amoObjects = [$amoObjects];
        }

        // Разделяем объекты по URL и типу операции (создание/обновление)
        $toAdd = [];      // Объекты для создания (POST)
        $toUpdate = [];   // Объекты для обновления (PATCH)

        foreach ($amoObjects as $object) {
            $url = $object::URL;
            $params = $object->getParams();

            if (isset($object->id)) {
                $toUpdate[$url][] = $params;
            } else {
                $toAdd[$url][] = $params;
            }
        }

        $responses = [];

        // Отправляем POST запросы для создания новых сущностей
        foreach ($toAdd as $url => $params) {
            $response = AmoAPI::request($url, 'POST', $params, $subdomain);
            if (empty($response)) {
                throw new AmoAPIException(
                    "Не удалось пакетно добавить сущности (пустой ответ) по запросу {$url}: " . print_r($params, true)
                );
            }
            $responses[] = $response;
        }

        // Отправляем PATCH запросы для обновления существующих сущностей
        foreach ($toUpdate as $url => $params) {
            $response = AmoAPI::request($url, 'PATCH', $params, $subdomain);
            if (empty($response)) {
                throw new AmoAPIException(
                    "Не удалось пакетно обновить сущности (пустой ответ) по запросу {$url}: " . print_r($params, true)
                );
            }
            $responses[] = $response;
        }

        if (!$returnResponses) {
            $items = [];
            foreach ($responses as $response) {
                $items = array_merge($items, self::getItems($response));
            }
            return $items;
        }

        return $responses;
    }

    /**
     * Удаляет объекты AmoObject (списки или элементы списков)
     * Обновлено для работы с API v4: для удаления используется DELETE метод напрямую к URL сущности
     * @param array|object $amoObjects Массив объектов AmoObject или объект AmoObject
     * @param bool $returnResponses Возвращать массив ответов сервера amoCRM вместо массива параметров сущностей
     * @param string $subdomain Поддомен amoCRM
     * @return array
     * @throws AmoAPIException
     */
    public static function deleteObjects($amoObjects, bool $returnResponses = false, $subdomain = null): array
    {
        if (!is_array($amoObjects)) {
            $amoObjects = [$amoObjects];
        }

        // Группируем объекты по URL для пакетного удаления
        $objectsByUrl = [];
        foreach ($amoObjects as $object) {
            $params = $object->getParams();
            $id = $params['id'] ?? null;
            if (!$id) {
                throw new AmoAPIException("Для удаления сущности требуется свойство id: " . print_r($params, true));
            }

            $url = $object::URL;
            // Для элементов каталогов URL формируется динамически
            if ($object instanceof AmoCatalogElement) {
                if (empty($object->catalog_id)) {
                    throw new AmoAPIException("Для удаления элемента каталога требуется указать catalog_id");
                }
                $url = $url . '/' . $object->catalog_id . '/elements';
            }

            if (!isset($objectsByUrl[$url])) {
                $objectsByUrl[$url] = [];
            }
            $objectsByUrl[$url][] = $id;
        }

        $responses = [];
        foreach ($objectsByUrl as $url => $ids) {
            // В v4 для пакетного удаления передаем массив ID в теле запроса
            // DELETE запрос с массивом ID в теле
            $response = AmoAPI::request($url, 'DELETE', $ids, $subdomain);
            if ($response === null && !$returnResponses) {
                // Для DELETE запросов может быть пустой ответ (204 No Content)
                continue;
            }
            $responses[] = $response;
        }

        if (!$returnResponses) {
            $items = [];
            foreach ($responses as $response) {
                if ($response !== null) {
                    $responseItems = self::getItems($response);
                    if (is_array($responseItems)) {
                        $items = array_merge($items, $responseItems);
                    }
                }
            }
            return $items;
        }

        return $responses;
    }
}
