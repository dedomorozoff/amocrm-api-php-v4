<?php
/**
 * Класс AmoCompany. Содерит методы для работы с компаниями.
 *
 * @author    andrey-tech, dedomorozoff
 * @copyright 2020 andrey-tech, 2024 dedomorozoff
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.2.0
 *
 * v1.0.0 (24.04.2019) Начальный релиз.
 * v1.1.0 (19.05.2020) Добавлена поддержка параметра $subdomain в конструктор
 * v1.2.0 (20.05.2020) Добавлены методы getPhone(), getEmail()
 *
 */

declare(strict_types = 1);

namespace AmoCRM;

class AmoCompany extends AmoObject
{
    /**
     * Путь для запроса к API
     * @var string
     */
    const URL = '/api/v4/companies';

    /**
     * @var array
     */
    public $leads = [];

    /**
     * @var array
     */
    public $contacts = [];

    /**
     * @var array
     */
    public $customers = [];

    /**
     * @var int
     */
    public $closest_task_at;

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
     * @return array
     */
    public function getParams() :array
    {
        $params = [];

        if (isset($this->closest_task_at)) {
            $params['closest_task_at'] = $this->closest_task_at;
        }

        if (count($this->leads)) {
            $params['leads_id'] = $this->leads['id'];
        }
        
        if (count($this->contacts)) {
            $params['contacts_id'] = $this->contacts['id'];
        }
        
        if (count($this->customers)) {
            $params['customers_id'] = $this->customers['id'];
        }
        
        return array_merge(parent::getParams(), $params);
    }

    /**
     * Добавляет задачи
     * @param array | int $leads
     * @return AmoCompany
     *
     */
    public function addLeads($leads) :AmoCompany
    {
        if (! is_array($leads)) {
            $leads = [ $leads ];
        }
        
        if (isset($this->leads['id'])) {
            foreach ($leads as $id) {
                if (! in_array($id, $this->leads['id'])) {
                    $this->leads['id'][] = $id;
                }
            }
        } else {
            $this->leads['id'] = $leads;
        }

        return $this;
    }

    /**
     * Добавляет контакты
     * @param array | int $contacts
     * @return AmoCompany
     *
     */
    public function addContacts($contacts) :AmoCompany
    {
        if (! is_array($contacts)) {
            $contacts = [ $contacts ];
        }
        
        if (isset($this->contacts['id'])) {
            foreach ($contacts as $id) {
                if (! in_array($id, $this->contacts['id'])) {
                    $this->contacts['id'][] = $id;
                }
            }
        } else {
            $this->contacts['id'] = $contacts;
        }

        return $this;
    }

    /**
     * Добавляет покупателей
     * @param array | int $customers
     * @return AmoCompany
     *
     */
    public function addCustomers($customers) :AmoCompany
    {
        if (! is_array($customers)) {
            $customers = [ $customers ];
        }
        
        if (isset($this->customers['id'])) {
            foreach ($customers as $id) {
                if (! in_array($id, $this->customers['id'])) {
                    $this->customers['id'][] = $id;
                }
            }
        } else {
            $this->customers['id'] = $customers;
        }

        return $this;
    }

    /**
     * Возвращает первый номер телефона из дополнительных полей
     * Обновлено для работы с API v4: используется custom_fields_values
     * @return string|null
     */
    public function getPhone()
    {
        // В v4 используется custom_fields_values, но при получении данных может быть custom_fields
        $fields = $this->custom_fields_values ?? [];
        
        foreach ($fields as $customField) {
            // Проверяем по code или field_id
            if (isset($customField['field_code']) && $customField['field_code'] === 'PHONE') {
                if (isset($customField['values'][0]['value'])) {
                    return $customField['values'][0]['value'];
                }
            }
            // Альтернативная проверка по структуре значений
            if (isset($customField['values']) && is_array($customField['values'])) {
                foreach ($customField['values'] as $value) {
                    if (isset($value['value']) && preg_match('/^\+?\d/', $value['value'])) {
                        return $value['value'];
                    }
                }
            }
        }

        return null;
    }

    /**
     * Возвращает первый адрес электронной почты из дополнительных полей
     * Обновлено для работы с API v4: используется custom_fields_values
     * @return string|null
     */
    public function getEmail()
    {
        // В v4 используется custom_fields_values, но при получении данных может быть custom_fields
        $fields = $this->custom_fields_values ?? [];
        
        foreach ($fields as $customField) {
            // Проверяем по code или field_id
            if (isset($customField['field_code']) && $customField['field_code'] === 'EMAIL') {
                if (isset($customField['values'][0]['value'])) {
                    return $customField['values'][0]['value'];
                }
            }
            // Альтернативная проверка по структуре значений (email содержит @)
            if (isset($customField['values']) && is_array($customField['values'])) {
                foreach ($customField['values'] as $value) {
                    if (isset($value['value']) && strpos($value['value'], '@') !== false) {
                        return $value['value'];
                    }
                }
            }
        }

        return null;
    }
}
