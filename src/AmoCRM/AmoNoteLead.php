<?php
/**
 * Класс AmoNote. Содержит методы для работы с примечаниями в сделках.
 *
 * @author    andrey-tech, dedomorozoff
 * @copyright 2020 andrey-tech, 2024 dedomorozoff
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.1.0
 *
 * v1.0.0 (24.04.2019) Первоначальная версия
 * v1.1.0 (19.05.2020) Добавлена поддержка параметра $subdomain в конструктор
 *
 */

declare(strict_types=1);

namespace AmoCRM;

class AmoNoteLead extends AmoObject
{
    /**
     * Путь для запроса к API
     * @var string
     */
    const URL = '/api/v4/leads/notes';

    /**
     * @var bool
     */
    public $is_editable;

    /**
     * @var int
     */
    public $entity_id;

    /**
     * @var string
     */
    public $text;

    /**
     * @var string
     */
    public $note_type;

    /**
     * Конструктор
     * @param array $data Параметры модели
     * @param string|null $subdomain Поддомен amoCRM
     */
    public function __construct(array $data = [],string $subdomain = null)
    {
        parent::__construct($data, $subdomain);
    }

    /**
     * Приводит модель к формату для передачи в API
     * @return array
     */
    public function getParams(): array
    {
        $params = [];

        $properties = ['is_editable', 'entity_id', 'text', 'note_type'];
        foreach ($properties as $property) {
            if (isset($this->$property)) {
                $params[$property] = $this->$property;
            }
        }

        if ($this->note_type == 'common' ||
            $this->note_type == 'sms_in' ||
            $this->note_type == 'sms_out'
        ) {
            $params['params'] = ['text' => $this->text];
        }

        return array_merge(parent::getParams(), $params);
    }
}
