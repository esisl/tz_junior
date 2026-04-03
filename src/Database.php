<?php
// src/Database.php

class Database
{
private static ?Database $instance = null;
private ?PDO $connection = null;

    // Конструктор скрыт для предотвращения создания экземпляра через new
    private function __construct()
    {
        $config = require __DIR__ . '/../config/database.php';

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['dbname'],
            $config['charset']
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Бросать исключения при ошибках
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Возвращать массивы по умолчанию
            PDO::ATTR_EMULATE_PREPARES   => false,                  // Использовать нативные подготовленные выражения
        ];

        try {
            $this->connection = new PDO($dsn, $config['username'], $config['password'], $options);
        } catch (PDOException $e) {
            // В продакшене ошибку нужно логировать в файл, а не выводить пользователю
            error_log('Database connection failed: ' . $e->getMessage());
            throw new RuntimeException('Database connection failed');
        }
    }

    // Запрет на клонирование
    private function __clone() {}

    // Запрет на десериализацию
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }

    // Получение единственного экземпляра
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Геттер для подключения
    public function getConnection(): PDO
    {
        return $this->connection;
    }
}