<?php
/**
 * Класс DatabaseStorage. Реализует хранение токенов в базе данных
 *
 * @author    dedomorozoff
 * @copyright 2024 dedomorozoff
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.0.0
 *
 * v1.0.0 (2024) Начальный релиз
 */
namespace AmoCRM\TokenStorage;

use AmoCRM\DataBase\DataBaseConnection;

class DatabaseStorage implements TokenStorageInterface
{
    protected $integrationCode;
    private $DBconnection;

    public function __construct(array $dataBaseConfig, string $integrationCode) 
    {
        $this->integrationCode = $integrationCode;
        $this->DBconnection = new DataBaseConnection($dataBaseConfig);
    }

    /**
     * Сохраняет токены
     * @param  array  $tokens Токены для сохранения
     * @param  string $domain Полный домен amoCRM
     * @return void
     */
    public function save(array $tokens, string $domain)
    {
        $jsonTokens = json_encode($tokens, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
        if ($jsonTokens === false) {
            $errorMessage = json_last_error_msg();
            throw new TokenStorageException("Ошибка JSON-кодирования токенов ({$errorMessage}): " . print_r($tokens, true));
        }

        if (! $this->DBconnection->addTokens($domain, $jsonTokens, $this->integrationCode)) {
            throw new TokenStorageException("Не удалось записать в базу данных");
        }
    }

    /**
     * Загружает токены
     * @param  string $domain Полный домен amoCRM
     * @return array|null
     */
    public function load(string $domain)
    {
        $tokensJSON = $this->DBconnection->getTokens($domain, $this->integrationCode);
        if ($tokensJSON === false) {
            throw new TokenStorageException("Не удалось извлечь токены из базы данных");
        }
        $tokens = json_decode($tokensJSON, true);
        if (is_null($tokens)) {
            $errorMessage = json_last_error_msg();
            throw new TokenStorageException(
                "Ошибка JSON-декодирования содержимого файла токенов ({$errorMessage})"
            );
        }

        return $tokens;
    }

    /**
     * Проверяет: существуют ли токены для заданного домена amoCRM,
     * то есть была ли выполнена первичная авторизация
     * @param  string  $domain Полный домен amoCRM
     * @return boolean
     */
    public function hasTokens(string $domain) :bool
    {
        $tokensJSON = $this->DBconnection->getTokens($domain, $this->integrationCode);
        if ($tokensJSON === false) {
            return false;
        }
        return true;
    }
}