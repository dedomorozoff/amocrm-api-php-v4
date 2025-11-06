<?php
/**
 * Класс AmoCatalogElement. Содерит методы для работы с элементами списка (каталога).
 *
 * @author    andrey-tech, dedomorozoff
 * @copyright 2020 andrey-tech, 2024 dedomorozoff
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.1.1
 *
 * v1.0.0 (19.08.2019) Начальный релиз.
 * v1.1.0 (19.05.2020) Добавлена поддержка параметра $subdomain в конструктор
 * v1.1.1 (25.05.2020) Добавлено свойство $is_deleted
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

class AmoCatalogElement extends AmoObject
{
    /**
     * Путь для запроса к API (базовый путь, полный URL формируется динамически с учетом catalog_id)
     * @var string
     */
    const URL = '/api/v4/catalogs';

    /**
     * @var int
     */
    public $catalog_id;

    /**
     * @var bool
     */
    public $is_deleted;

    /**
     * Конструктор
     * @param array $data Параметры модели
     * @param string $subdomain Поддомен amoCRM
     */
    public function __construct(array $data = [], $subdomain = null)
    {
        parent::__construct($data, $subdomain);
    }

    /**
     * Приводит модель к формату для передачи в API
     * ВНИМАНИЕ: Для элементов каталогов в v4 используется custom_fields (не custom_fields_values)
     * @return array
     */
    public function getParams() :array
    {
        $params = [];

        $properties = [ 'id', 'name', 'catalog_id', 'is_deleted' ];
        foreach ($properties as $property) {
            if (isset($this->$property)) {
                $params[$property] = $this->$property;
            }
        }

        // Для элементов каталогов используется custom_fields (специфика v4 API)
        if (count($this->custom_fields)) {
            $params['custom_fields'] = $this->custom_fields;
        }

        return array_merge(parent::getParams(), $params);
    }

    /**
     * Заполняет модель по ID сущности
     * Переопределен для поддержки v4 API с динамическим URL на основе catalog_id
     * @param int|string $id ID сущности
     * @param array $params Дополнительные параметры запроса, передаваемые при GET-запросе к amoCRM
     * @return AmoObject
     * @throws AmoAPIException
     */
    public function fillById($id, array $params = [])
    {
        if (empty($this->catalog_id)) {
            throw new AmoAPIException("Для работы с элементами каталога требуется указать catalog_id");
        }

        $url = self::URL . '/' . $this->catalog_id . '/elements/' . $id;
        $response = AmoAPI::request($url, 'GET', $params, $this->subdomain);
        $items = AmoAPI::getItems($response);

        $className = get_class($this);
        if (empty($items)) {
            throw new AmoAPIException("Не найдена сущность {$className} с ID {$id}");
        }
        if (!key_exists('enums', $items) && !key_exists('responsible_user_id', $response)) {
            $item = array_shift($items);
        } else {
            $item = $response;
        }
        if ($item['id'] != $id) {
            throw new AmoAPIException("Нет сущности {$className} с ID {$id}");
        }

        $this->fill($item);

        return $this;
    }

    /**
     * Обновляет или добавляет объект в amoCRM
     * Переопределен для поддержки v4 API с динамическим URL на основе catalog_id
     * @param bool $returnResponse Вернуть ответ сервера вместо ID сущности
     * @return mixed
     *
     * @throws AmoAPIException
     */
    public function save(bool $returnResponse = false)
    {
        if (empty($this->catalog_id)) {
            throw new AmoAPIException("Для работы с элементами каталога требуется указать catalog_id");
        }

        $url = self::URL . '/' . $this->catalog_id . '/elements';

        if (isset($this->id)) {
            $lock = AmoAPI::lockEntity($this);
            $params = [$this->getParams()];
            $typeHTTPRequest = 'PATCH';
        } else {
            $lock = null;
            $params = [$this->getParams()];
            $typeHTTPRequest = 'POST';
        }
        $response = AmoAPI::request($url, $typeHTTPRequest, $params, $this->subdomain);
        AmoAPI::unlockEntity($lock);

        $items = AmoAPI::getItems($response);
        if (empty($items)) {
            $action = isset($this->id) ? 'обновить' : 'добавить';
            $className = get_class($this);
            throw new AmoAPIException(
                "Не удалось {$action} сущность {$className} (пустой ответ): " . print_r($params, true)
            );
        }

        if (!$returnResponse) {
            // В v4 ответ обрабатывается через getItems(), который возвращает массив элементов
            if (is_array($items) && !empty($items)) {
                // Если это массив элементов, возвращаем ID первой
                if (isset($items[0]['id'])) {
                    return $items[0]['id'];
                }
                // Если это один элемент (не массив)
                if (isset($items['id'])) {
                    return $items['id'];
                }
            }
            // Fallback: проверяем полный ответ
            if (isset($response['_embedded']['elements'][0]['id'])) {
                return $response['_embedded']['elements'][0]['id'];
            }
            // Если ничего не найдено, пробуем вернуть первый элемент массива
            if (is_array($items) && count($items) > 0) {
                return reset($items)['id'] ?? null;
            }
            throw new AmoAPIException("Не удалось получить ID созданного/обновленного элемента каталога из ответа");
        }

        return $response;
    }
}
