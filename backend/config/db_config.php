<?php
// Файл: prognozai/backend/Config/db_config.php
declare(strict_types=1);

define('DB_HOST', 'localhost'); // или IP вашего сервера MariaDB
define('DB_NAME', 'prognozai');
define('DB_USER', 'root');
define('DB_PASS', 'Vasya2403'); // ЗАМЕНИТЕ НА ВАШ РЕАЛЬНЫЙ СЕКРЕТНЫЙ ПАРОЛЬ
define('DB_CHARSET', 'utf8mb4');

// Проверка, что все ключевые константы определены (опционально, но полезно для отладки)
$required_db_consts = ['DB_HOST', 'DB_NAME', 'DB_USER', 'DB_PASS', 'DB_CHARSET'];
foreach ($required_db_consts as $const) {
    if (!defined($const)) {
        // Логируем ошибку, но не прерываем выполнение здесь,
        // чтобы DatabaseConnection мог выбросить более специфичное исключение.
        error_log("Критическая ошибка конфигурации: константа БД '$const' не определена в " . __FILE__);
    }
}