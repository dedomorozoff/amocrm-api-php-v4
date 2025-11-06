<?php
/**
 * Класс AmoObject. Абстрактный базовый класс для работы с сущностями amoCRM.
 *
 * @author    andrey-tech, dedomorozoff
 * @copyright 2020 andrey-tech, 2024 dedomorozoff
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.8.2
 *
 * v1.0.0 (24.04.2019) Первоначальная версия
 * v1.0.1 (09.08.2019) Добавлено 5 секунд к updated_at
 * v1.1.0 (19.08.2019) Добавлен метод delete()
 * v1.1.1 (13.11.2019) Добавлено исключение в метод fillById()
 * v1.2.0 (13.11.2019) Добавлен метод getCustomFieldValueById()
 * v1.2.1 (22.02.2020) Удален метод delete(), как более не поддерживаемый
 * v1.3.0 (10.05.2020) Добавлена проверка ответа сервера в метод save(). Добавлено свойство request_id
 * v1.4.0 (16.05.2020) Добавлен параметр $returnResponse в метод save()
 * v1.5.0 (19.05.2020) Добавлен параметр $subdomain в конструктор
 * v1.6.0 (21.05.2020) Добавлена поддержка параметра AmoAPI::$updatedAtDelta
 * v1.6.1 (25.05.2020) Рефракторинг
 * v1.7.0 (26.05.2020) Добавлена блокировка сущностей при обновлении (update) методом save()
 * v1.7.1 (23.07.2020) Исправлен тип параметра $returnResponse в методе save()
 * v1.8.0 (10.08.2020) Добавлены новые параметры в метод getCustomFieldValueById()
 * v1.8.1 (10.08.2020) Удалены неиспользуемые свойства. Рефракторинг
 * v1.8.2 (11.08.2020) Исправлена проверка ID в методе fillById()
 *
 */

declare(strict_types=1);

namespace AmoCRM;

abstract class AmoObject
{
    /**
     * Путь для запроса к API (определяется в дочерних классах)
     * @var string
     */
    const URL = '';

    /**
     * Типы привязываемых элементов
     * @var int
     */
    const CONTACT_TYPE = 1;
    const LEAD_TYPE = 2;
    const COMPANY_TYPE = 3;
    const TASK_TYPE = 4;
    const CUSTOMER_TYPE = 12;

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var int
     */
    public $responsible_user_id;

    /**
     * @var int
     */
    public $created_by;

    /**
     * @var int
     */
    public $updated_by;

    /**
     * @var int
     */
    public $created_at;

    /**
     * @var int
     */
    public $updated_at;

    /**
     * @var int
     */
    public $account_id;

    /**
     * @var array
     */
    public $custom_fields_values = [];

    /**
     * @var array
     */
    public $tags = [];

    /**
     * @var array
     */
    public $contacts = [];

    /**
     * @var array
     */
    public $_embedded = [];

    /**
     * @var int
     */
    public $group_id;

    /**
     * @var int
     */
    public $request_id;

    /**
     * @var int
     */
    public $price;

    /**
     * Текущий поддомен для доступа к API
     * @var string
     */
    protected $subdomain;

    /**
     * Конструктор
     * @param array $params Параметры модели
     * @param string $subdomain Поддомен amoCRM
     */
    public function __construct(array $params = [], $subdomain = null)
    {
        $this->subdomain = $subdomain;
        $this->fill($params);
    }

    /**
     * Заполняет модель значениями из массива data
     * @param array $params Параметры модели
     * @return void
     */
    protected function fill(array $params = [])
    {
        foreach ($params as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Возвращает параметры модели в формате для передачи в API
     * @return array
     */
    public function getParams(): array
    {
        $params = [];
        $properties = ['id', 'field_id', 'name', 'responsible_user_id', 'created_by', 'created_at',
            'updated_by', 'account_id', 'group_id', 'request_id', 'price'];
        foreach ($properties as $property) {
            if (isset($this->$property)) {
                $params[$property] = $this->$property;
            }
        }

        if (count($this->custom_fields_values)) {
            $params['custom_fields_values'] = $this->custom_fields_values;
        }

        if (count($this->tags)) {
            $params['tags'] = array_column($this->tags, 'name');
        }

        // Если обновление сущности, то добавляем обязательный параметр 'updated_at'
        if (!isset($this->enums)) {
            if (isset($this->id)) {
                $params['updated_at'] = time() + AmoAPI::$updatedAtDelta;
            }
        }


        return $params;
    }

    /**
     * Заполняет модель по ID сущности
     * @param int|string $id ID сущности
     * @param array $params Дополнительные параметры запроса, передаваемые при GET-запросе к amoCRM
     * @return AmoObject
     * @throws AmoAPIException
     */
    public function fillById($id, array $params = [])
    {
//        $params = array_merge(['id' => $id], $params);
        $response = AmoAPI::request($this::URL . '/' . $id, 'GET', $params, $this->subdomain);
        $items = AmoAPI::getItems($response);

        $className = get_class($this);
        if (empty($items)) {
            throw new AmoAPIException("Не найдена сущность {$className} с ID {$id}");
        }
        if (!key_exists('enums', $items) && !key_exists('responsible_user_id', $response)) {
            $item = array_shift($items);
        } else
            $item = $response;
        if ($item['id'] != $id) {
            throw new AmoAPIException("Нет сущности {$className} с ID {$id}");
        }

        $this->fill($item);

        return $this;
    }

    /**
     * Возвращает значение дополнительного поля по его ID
     * @param int|string $id ID дополнительного поля
     * @param bool $returnFirst Вернуть только первое значение
     * @param string $returnValue Имя параметра, значение которого возвращается
     * @return mixed
     */
    public function getCustomFieldValueById($id, bool $returnFirst = true, string $returnValue = 'value')
    {
        $index = array_search($id, array_column($this->custom_fields_values, 'field_id'));
        if ($index === false) {
            return null;
        }

        $list = [];
        foreach ($this->custom_fields_values[$index]['values'] as $item) {
            if (is_array($item)) {
                if (isset($item[$returnValue])) {
                    $list[] = $item[$returnValue];
                }
            } else {
                $list[] = $item;
            }
        }

        if ($returnFirst) {
            return array_shift($list);
        }

        return $list;
    }

    /**
     * Возвращает массив дополнительных полей по их id
     * @param array|int $ids
     * @return array
     */
    public function getCustomFields($ids)
    {
        if (!is_array($ids)) {
            $ids = [$ids];
        }

        return array_intersect_key(
            $this->custom_fields_values,
            array_intersect(
                array_column($this->custom_fields_values, 'id'),
                $ids
            )
        );
    }

    /**
     * Устанавливает значение дополнительных полей
     * @param array $params Значения дополнительных полей
     * @return AmoObject
     */
    public function setCustomFields(array $params)
    {
        foreach ($params as $key => $value) {
            if (is_array($value)) {
                $field = [
                    'field_id' => $key,
                    'values' => $value
                ];
            } else {
                $field = [
                    'field_id' => $key,
                    'values' => [
                        ['value' => $value]
                    ]
                ];
            }

            $i = array_search($key, array_column($this->custom_fields_values, 'id'));
            if ($i !== false) {
                $this->custom_fields_values[$i]['values'] = $field['values'];
            } else {
                $this->custom_fields_values[] = $field;
            }
        }

        return $this;
    }

    public function setCustomFieldsSelect(array $params)
    {
        foreach ($params as $key => $value) {
            $field_id = array_keys($value)[0];
            if (is_array($value)) {
                $field = [
                    'field_id' => $field_id,
                    'values' => [
                        ['value' => $value[$field_id]]
                    ]
                ];
            } else {
                $field = [
                    'field_id' => $field_id,
                    'values' => [
                        ['value' => $value[$field_id]]
                    ]
                ];
            }

            $i = array_search($key, array_column($this->custom_fields_values, 'id'));
            if ($i !== false) {
                $this->custom_fields_values[$i]['values'] = $field['values'];
            } else {
                $this->custom_fields_values[] = $field;
            }
        }

        return $this;
    }

    public function setEnum(int $field_id, string $value)
    {
        $enums = $this->enums;
        $name = $this->name;
        unset($this->account_id);
        unset($this->entity_type);
        unset($this->responsible_user_id);
        unset($this->created_by);
        unset($this->updated_by);
        unset($this->created_at);
        unset($this->updated_at);
        $this->enums = array_merge($enums, [['value' => $value, 'sort' => count($enums)]]);
        return $this;
    }

    /**
     * Добавляет тэги
     * @param array | string $tags
     * @return AmoObject
     *
     */
    public function addTags($tags): AmoObject
    {
        if (!is_array($tags)) {
            $tags = [$tags];
        }

        foreach ($tags as $value) {
            $tag = [
                'name' => $value
            ];

            if (!in_array($value, array_column($this->tags, 'name'))) {
                $this->tags[] = $tag;
            }
        }

        return $this;
    }

    /**
     * Удаляет тэги
     * @param array | string $tags
     * @return AmoObject
     *
     */
    public function delTags($tags): AmoObject
    {
        if (!is_array($tags)) {
            $tags = [$tags];
        }
        $this->tags = array_diff_key($this->tags, array_intersect(array_column($this->tags, 'name'), $tags));

        return $this;
    }

    /**
     * Обновляет или добавляет объект в amoCRM
     * Обновлено для работы с API v4: используется POST для создания и PATCH для обновления
     * Данные передаются напрямую как массив объектов без обертки add/update
     * @param bool $returnResponse Вернуть ответ сервера вместо ID сущности
     * @return mixed
     *
     * @throws AmoAPIException
     */
    public function save(bool $returnResponse = false)
    {
        if (isset($this->id)) {
            $lock = AmoAPI::lockEntity($this);
            $params = [$this->getParams()];
            $typeHTTPRequest = 'PATCH';
        } else {
            $lock = null;
            $params = [$this->getParams()];
            $typeHTTPRequest = 'POST';
        }
        $response = AmoAPI::request($this::URL, $typeHTTPRequest, $params, $this->subdomain);
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
            // В v4 ответ обрабатывается через getItems(), который возвращает массив сущностей
            // Проверяем различные варианты структуры ответа
            if (is_array($items) && !empty($items)) {
                // Если это массив сущностей, возвращаем ID первой
                if (isset($items[0]['id'])) {
                    return $items[0]['id'];
                }
                // Если это одна сущность (не массив)
                if (isset($items['id'])) {
                    return $items['id'];
                }
            }
            // Fallback: проверяем полный ответ на случай, если getItems() вернул не то
            if (isset($response['_embedded'])) {
                $embedded = $response['_embedded'];
                if (isset($embedded['leads'][0]['id'])) return $embedded['leads'][0]['id'];
                if (isset($embedded['contacts'][0]['id'])) return $embedded['contacts'][0]['id'];
                if (isset($embedded['companies'][0]['id'])) return $embedded['companies'][0]['id'];
                if (isset($embedded['tasks'][0]['id'])) return $embedded['tasks'][0]['id'];
                if (isset($embedded['events'][0]['id'])) return $embedded['events'][0]['id'];
                if (isset($embedded['notes'][0]['id'])) return $embedded['notes'][0]['id'];
                if (isset($embedded['catalogs'][0]['id'])) return $embedded['catalogs'][0]['id'];
                if (isset($embedded['custom_fields'][0]['id'])) return $embedded['custom_fields'][0]['id'];
            }
            // Если ничего не найдено, пробуем вернуть первый элемент массива
            if (is_array($items) && count($items) > 0) {
                return reset($items)['id'] ?? null;
            }
            throw new AmoAPIException("Не удалось получить ID созданной/обновленной сущности из ответа");
        }

        return $response;
    }
}
