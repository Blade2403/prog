<?php
// Файл: prognozai/backend/Core/DatabaseConnection.php

declare(strict_types=1);

namespace PrognozAi\Core; // Общий неймспейс для ядра, если Database - подмодуль, то PrognozAi\Core\Database

use PDO;
use PDOException;
use RuntimeException; // Используем RuntimeException для более общих ошибок

class DatabaseConnection
{
    private static ?PDO $pdoInstance = null;
    private static array $config = [];

    /**
     * Приватный конструктор, чтобы предотвратить создание экземпляра через new.
     */
    private function __construct() {}

    /**
     * Предотвращает клонирование экземпляра.
     */
    private function __clone() {}

    /**
     * Предотвращает десериализацию экземпляра.
     * @throws RuntimeException
     */
    public function __wakeup()
    {
        throw new RuntimeException("Cannot unserialize a singleton.");
    }

    /**
     * Инициализирует конфигурацию БД.
     * Вызывается один раз перед первым получением PDO.
     * @throws RuntimeException если файл конфигурации не найден или константы не определены.
     */
    private static function loadConfig(): void
    {
        if (empty(self::$config)) {
            // Путь к файлу конфигурации относительно текущего файла
            // __DIR__ -> prognozai/backend/core/
            // ../ -> prognozai/backend/
            // ../config/ -> prognozai/backend/config/
            $configPath = __DIR__ . '/../config/db_config.php';

            if (!file_exists($configPath)) {
                // Попытка получить абсолютный путь для более информативного сообщения об ошибке
                $resolvedPath = realpath($configPath) ?: $configPath;
                throw new RuntimeException("Файл конфигурации БД не найден: {$resolvedPath}");
            }
            
            require $configPath; // Загружаем константы

            $requiredConstants = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_CHARSET'];
            foreach ($requiredConstants as $const) {
                if (!defined($const)) {
                    throw new RuntimeException("Константа БД '$const' не определена в файле: {$configPath}");
                }
            }

            self::$config = [
                'host' => DB_HOST,
                'name' => DB_NAME,
                'user' => DB_USER,
                'pass' => DB_PASS,
                'charset' => DB_CHARSET,
            ];
        }
    }

    /**
     * Возвращает единственный экземпляр PDO для подключения к базе данных.
     *
     * @return PDO
     * @throws PDOException если произошла ошибка подключения.
     * @throws RuntimeException если конфигурация БД не загружена или неверна.
     */
    public static function getPDO(): PDO
    {
        if (self::$pdoInstance === null) {
            self::loadConfig(); // Убедимся, что конфигурация загружена

            $dsn = sprintf(
                "mysql:host=%s;dbname=%s;charset=%s",
                self::$config['host'],
                self::$config['name'],
                self::$config['charset']
            );

            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                // Можно добавить PDO::ATTR_PERSISTENT => true, если требуется постоянное соединение,
                // но это нужно делать с осторожностью и пониманием последствий.
            ];

            try {
                self::$pdoInstance = new PDO($dsn, self::$config['user'], self::$config['pass'], $options);
            } catch (PDOException $e) {
                // Логируем ошибку перед тем, как выбросить ее дальше
                error_log("Критическая ошибка подключения к БД: " . $e->getMessage() . " (DSN: {$dsn})");
                // Можно выбросить более общее исключение, чтобы скрыть детали подключения от внешнего кода,
                // но для отладки PDOException информативнее.
                throw $e;
            }
        }

        return self::$pdoInstance;
    }
}