<?php

/**
 * Класс DataBaseConnection создает соединение с базой данных
 *
 * @author    dedomorozoff
 * @copyright 2024 dedomorozoff
 * @see https://github.com/andrey-tech/amocrm-api-php
 * @license   MIT
 *
 * @version 1.0.0
 *
 * v1.0.0 (2024) Начальный релиз
 *
 * Для корректного соединения требуется передать в конструктор класса массив с конфигурационными данными базы данных с ключами:
 * host - хост на котором расположена БД
 * port = порт на котором расположена БД
 * db - название БД
 * user - имя пользователя
 * password - пароль пользователя
 */
namespace AmoCRM\DataBase;

class DataBaseConnection {

    private $connection;

    public function __construct(array $config)
    {
        $host = $config['host'];
        $port = $config['port'];
        $dataBase = $config['db'];
        $user = $config['user'];
        $password = $config['password'];
        $dsn = "mysql:host={$host};port={$port};dbname={$dataBase};";
        try {
            $this->connection = new \PDO($dsn, $user, $password);
        } catch(\PDOException $e) {
            printf('Не удалось подключиться к базе данных..');
            print_r($config);
            print_r($e);
        }
    }


    /**
     * сохраняет токены в базу данных
     *
     * @param string $domain
     * @param string $tokens
     * @param string $integrationCode
     * @return boolean
     */
    public function addTokens(string $domain, string $tokens, string $integrationCode) : bool
    {
        if ($this->getTokens($domain, $integrationCode) === false) {
            $sql = "INSERT INTO tokens (token_domain, token_json, token_integration_code, updated_at)
            VALUES (:token_domain, :token_json, :token_integration_code, :updated_at)";
            $query = $this->connection->prepare($sql);
            return $query->execute([
                'token_domain' => $domain,
                'token_json' => $tokens,
                'token_integration_code' => $integrationCode,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        } else {
            $sql = "UPDATE tokens
                    SET token_json = :token_json
                    WHERE token_domain = :token_domain
                    AND token_integration_code = :token_integration_code
                    AND updated_at = :updated_at";
            $query = $this->connection->prepare($sql);
            return $query->execute([
                'token_domain' => $domain,
                'token_json' => $tokens,
                'token_integration_code' => $integrationCode,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
        }
        
    }  


    /**
     * извлекает токены из базы данных
     *
     * @param string $domain
     * @param string $integrationCode
     * @return string|false
     */
    public function getTokens(string $domain, string $integrationCode) : string|false
    {
        $sql = "SELECT * FROM tokens
                WHERE token_domain = :token_domain
                AND token_integration_code = :token_integration_code";
        $query = $this->connection->prepare($sql);
        $query->execute([
            'token_domain' => $domain,
            'token_integration_code' => $integrationCode
        ]);
        $data = $query->fetchAll();
        if (! empty($data)) {  
            return $data[array_key_first($data)]['token_json'];
        }
        return false;
    }
}