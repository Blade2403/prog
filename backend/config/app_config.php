### 1.4. Конфигурационный файл приложения

**Файл: prognozai/backend/config/app_config.php**

<?php
/**
 * Конфигурационный файл приложения PrognozAi.ru
 * 
 * Содержит основные настройки приложения
 */
return [
    // Режим отладки
    'debug' => true,
    
    // Язык приложения по умолчанию
    'language' => 'ru',
    
    // Базовый URL приложения
    'baseUrl' => '/',
    
    // Информация о сайте
    'siteName' => 'PrognozAi.ru',
    'siteDescription' => 'Интеллектуальная онлайн-платформа для анализа футбольных и теннисных событий',
    'siteEmail' => 'info@prognozai.ru',
    
    // Настройки пагинации
    'perPage' => 10,
    
    // Поддерживаемые языки
    'supportedLanguages' => [
        'ru' => 'Русский',
        'en' => 'English',
    ],
    
    // Настройки сессии
    'session' => [
        'name' => 'prognozai_session',
        'lifetime' => 3600, // 1 час
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
    ],
    
    // Настройки для автоматического импорта данных
    'dataImport' => [
        'defaultSource' => 1, // ID источника по умолчанию (Фонбет)
        'importInterval' => 3600, // Периодичность импорта в секундах (1 час)
    ],
    
    // Настройки AI
    'ai' => [
        'predictionUpdateInterval' => 86400, // 24 часа
        'predictionConfidenceThreshold' => 0.6, // Порог уверенности для отображения прогноза
    ],
];