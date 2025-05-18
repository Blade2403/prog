<?php
/**
 * Скрипт для наполнения базы данных PrognozAi.ru демо-данными
 * 
 * Версия: 1.0 (2025-05-18)
 * 
 * Использование:
 * php seed_demo_data.php
 */

// Подключаем автозагрузчик
require_once __DIR__ . '/../core/Autoloader.php';

// Включаем вывод ошибок для отладки
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "Скрипт наполнения базы данных демо-данными для PrognozAi.ru\n";
echo "=========================================================\n\n";

// Получаем соединение с БД через наш класс DatabaseConnection
try {
    if (class_exists('DatabaseConnection')) {
        $dbConnection = new DatabaseConnection();
        $db = $dbConnection->getConnection();
    } else {
        // Резервный способ подключения, если класс не найден
        $dbConfig = require __DIR__ . '/../config/db_config.php';
        $dsn = "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']};charset={$dbConfig['charset']}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $db = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], $options);
    }
    echo "✓ Соединение с базой данных установлено\n";
} catch (PDOException $e) {
    die("✗ Ошибка подключения к базе данных: " . $e->getMessage() . "\n");
}

// Функция для добавления записи и возврата ID
function insertAndGetId($db, $table, $data) {
    // Формируем строку с полями
    $fields = implode(', ', array_keys($data));
    
    // Формируем строку с плейсхолдерами
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    
    // Формируем SQL-запрос
    $sql = "INSERT INTO {$table} ({$fields}) VALUES ({$placeholders})";
    
    // Готовим и выполняем запрос
    $stmt = $db->prepare($sql);
    $stmt->execute(array_values($data));
    
    // Возвращаем ID добавленной записи
    return $db->lastInsertId();
}

// Функция для добавления записи с игнорированием дубликатов
function insertIgnore($db, $table, $data) {
    // Формируем строку с полями
    $fields = implode(', ', array_keys($data));
    
    // Формируем строку с плейсхолдерами
    $placeholders = implode(', ', array_fill(0, count($data), '?'));
    
    // Формируем SQL-запрос с INSERT IGNORE
    $sql = "INSERT IGNORE INTO {$table} ({$fields}) VALUES ({$placeholders})";
    
    // Готовим и выполняем запрос
    $stmt = $db->prepare($sql);
    return $stmt->execute(array_values($data));
}

// Функция для проверки существования записи
function recordExists($db, $table, $condition, $value) {
    $sql = "SELECT 1 FROM {$table} WHERE {$condition} = ? LIMIT 1";
    $stmt = $db->prepare($sql);
    $stmt->execute([$value]);
    return $stmt->fetchColumn() ? true : false;
}

// Начинаем транзакцию
$db->beginTransaction();

try {
    // Текущее время для полей created_at и updated_at
    $now = date('Y-m-d H:i:s');
    
    echo "\n[1/15] Добавление видов спорта (sports)...\n";
    
    // Проверяем наличие записей в таблице sports
    $stmt = $db->query("SELECT COUNT(*) FROM sports");
    if ($stmt->fetchColumn() == 0) {
        // Добавляем футбол
        $footballId = insertAndGetId($db, 'sports', [
            'name_ru' => 'Футбол',
            'name_en' => 'Football',
            'slug_ru' => 'football',
            'slug_en' => 'football',
            'description_ru' => 'Футбол - командная игра с мячом',
            'description_en' => 'Football - a team game with a ball',
            'logo_url' => null,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now
        ]);
        
        // Добавляем теннис
        $tennisId = insertAndGetId($db, 'sports', [
            'name_ru' => 'Теннис',
            'name_en' => 'Tennis',
            'slug_ru' => 'tennis',
            'slug_en' => 'tennis',
            'description_ru' => 'Теннис - индивидуальный или парный вид спорта',
            'description_en' => 'Tennis - an individual or doubles sport',
            'logo_url' => null,
            'is_active' => 1,
            'created_at' => $now,
            'updated_at' => $now
        ]);
        
        echo "  ✓ Добавлены виды спорта: Футбол (ID: {$footballId}), Теннис (ID: {$tennisId})\n";
    } else {
        // Получаем ID существующих видов спорта
        $stmt = $db->query("SELECT sport_id FROM sports WHERE slug_en = 'football' LIMIT 1");
        $footballId = $stmt->fetchColumn();
        
        $stmt = $db->query("SELECT sport_id FROM sports WHERE slug_en = 'tennis' LIMIT 1");
        $tennisId = $stmt->fetchColumn();
        
        echo "  ℹ Виды спорта уже существуют: Футбол (ID: {$footballId}), Теннис (ID: {$tennisId})\n";
    }
    
    echo "\n[2/15] Добавление стран (countries)...\n";
    
    // Проверяем наличие записей в таблице countries
    $stmt = $db->query("SELECT COUNT(*) FROM countries");
    if ($stmt->fetchColumn() < 5) {
        // Добавляем страны
        $countriesData = [
            [
                'name_ru' => 'Россия', 
                'name_en' => 'Russia', 
                'code' => 'RU',
                'iso_alpha2' => 'RU',
                'iso_alpha3' => 'RUS',
                'numeric_code' => '643',
                'flag_url' => '/static/img/flags/ru.png'
            ],
            [
                'name_ru' => 'Англия', 
                'name_en' => 'England', 
                'code' => 'GB',
                'iso_alpha2' => 'GB',
                'iso_alpha3' => 'GBR',
                'numeric_code' => '826',
                'flag_url' => '/static/img/flags/gb.png'
            ],
            [
                'name_ru' => 'Испания', 
                'name_en' => 'Spain', 
                'code' => 'ES',
                'iso_alpha2' => 'ES',
                'iso_alpha3' => 'ESP',
                'numeric_code' => '724',
                'flag_url' => '/static/img/flags/es.png'
            ],
            [
                'name_ru' => 'Италия', 
                'name_en' => 'Italy', 
                'code' => 'IT',
                'iso_alpha2' => 'IT',
                'iso_alpha3' => 'ITA',
                'numeric_code' => '380',
                'flag_url' => '/static/img/flags/it.png'
            ],
            [
                'name_ru' => 'Международный', 
                'name_en' => 'International', 
                'code' => 'INT',
                'iso_alpha2' => 'XX',
                'iso_alpha3' => 'XXX',
                'numeric_code' => '999',
                'flag_url' => '/static/img/flags/int.png'
            ]
        ];
        
        $countryIds = [];
        
        foreach ($countriesData as $countryData) {
            $code = $countryData['code'];
            unset($countryData['code']); // Удаляем, так как нет в таблице
            
            // Добавляем общие поля
            $countryData['is_active'] = 1;
            $countryData['created_at'] = $now;
            $countryData['updated_at'] = $now;
            
            if (!recordExists($db, 'countries', 'iso_alpha2', $countryData['iso_alpha2'])) {
                $id = insertAndGetId($db, 'countries', $countryData);
                $countryIds[$code] = $id;
                echo "  ✓ Добавлена страна: {$countryData['name_ru']} (ID: {$id})\n";
            } else {
                $stmt = $db->prepare("SELECT country_id FROM countries WHERE iso_alpha2 = ? LIMIT 1");
                $stmt->execute([$countryData['iso_alpha2']]);
                $id = $stmt->fetchColumn();
                $countryIds[$code] = $id;
                echo "  ℹ Страна уже существует: {$countryData['name_ru']} (ID: {$id})\n";
            }
        }
    } else {
        // Получаем ID существующих стран
        $countryIds = [];
        
        $stmt = $db->query("SELECT country_id, iso_alpha2 FROM countries");
        $countries = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($countries as $country) {
            $code = $country['iso_alpha2'];
            $countryIds[$code] = $country['country_id'];
        }
        
        echo "  ℹ Страны уже существуют в базе данных\n";
    }
    
    echo "\n[3/15] Добавление источников данных (data_sources)...\n";
    
    // Проверяем наличие записей в таблице data_sources
    $stmt = $db->query("SELECT COUNT(*) FROM data_sources");
    if ($stmt->fetchColumn() == 0) {
        // Добавляем источники данных
        $sourceTypes = [
            'web_scraper_custom' => 'Парсер веб-сайта (кастомный)',
            'web_scraper_api' => 'Парсер веб-сайта через API',
            'manual_input_admin' => 'Ручной ввод администратором',
            'system_generated' => 'Сгенерировано системой',
            'ai_llm_external' => 'Генерация внешним LLM',
            'ai_llm_internal' => 'Генерация внутренним LLM'
        ];
        
        $sourcesData = [
            [
                'source_name' => 'Фонбет (парсинг)',
                'source_type' => 'web_scraper_custom',
                'website_url' => 'https://www.fonbet.ru',
                'description_ru' => 'Парсинг данных с сайта Фонбет',
                'description_en' => 'Data parsing from Fonbet website',
                'base_url' => 'https://www.fonbet.ru',
                'is_active' => 1
            ],
            [
                'source_name' => 'Администратор PrognozAi',
                'source_type' => 'manual_input_admin',
                'website_url' => null,
                'description_ru' => 'Ручной ввод данных администратором',
                'description_en' => 'Manual data input by administrator',
                'base_url' => null,
                'is_active' => 1
            ],
            [
                'source_name' => 'Система PrognozAi',
                'source_type' => 'system_generated',
                'website_url' => null,
                'description_ru' => 'Системная генерация данных',
                'description_en' => 'System generated data',
                'base_url' => null,
                'is_active' => 1
            ],
            [
                'source_name' => 'OpenAI GPT-4',
                'source_type' => 'ai_llm_external',
                'website_url' => 'https://openai.com',
                'description_ru' => 'Генерация контента с помощью GPT-4',
                'description_en' => 'Content generation using GPT-4',
                'base_url' => 'https://api.openai.com',
                'is_active' => 1
            ]
        ];
        
        $sourceIds = [];
        
        foreach ($sourcesData as $sourceData) {
            // Добавляем общие поля
            $sourceData['created_at'] = $now;
            $sourceData['updated_at'] = $now;
            
            $id = insertAndGetId($db, 'data_sources', $sourceData);
            $sourceIds[$sourceData['source_name']] = $id;
            echo "  ✓ Добавлен источник данных: {$sourceData['source_name']} (ID: {$id})\n";
        }
        
        // Сохраняем ID для использования далее
        $fonbetSourceId = $sourceIds['Фонбет (парсинг)'];
        $adminSourceId = $sourceIds['Администратор PrognozAi'];
        $systemSourceId = $sourceIds['Система PrognozAi'];
        $openaiSourceId = $sourceIds['OpenAI GPT-4'];
    } else {
        // Получаем ID существующих источников данных
        $sourceIds = [];
        
        $stmt = $db->query("SELECT source_id, source_name FROM data_sources");
        $sources = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($sources as $source) {
            $sourceIds[$source['source_name']] = $source['source_id'];
        }
        
        // Пытаемся получить ID по известным именам или используем первые 4 ID
        $fonbetSourceId = $sourceIds['Фонбет (парсинг)'] ?? $sources[0]['source_id'] ?? 1;
        $adminSourceId = $sourceIds['Администратор PrognozAi'] ?? $sources[1]['source_id'] ?? 2;
        $systemSourceId = $sourceIds['Система PrognozAi'] ?? $sources[2]['source_id'] ?? 3;
        $openaiSourceId = $sourceIds['OpenAI GPT-4'] ?? $sources[3]['source_id'] ?? 4;
        
        echo "  ℹ Источники данных уже существуют в базе данных\n";
    }
    
    echo "\n[4/15] Добавление ролей пользователей (user_roles)...\n";
    
    // Проверяем наличие записей в таблице user_roles
    $stmt = $db->query("SELECT COUNT(*) FROM user_roles");
    if ($stmt->fetchColumn() == 0) {
        // Добавляем роли пользователей
        $rolesData = [
            [
                'role_key' => 'user',
                'name_ru' => 'Пользователь',
                'name_en' => 'User'
            ],
            [
                'role_key' => 'admin',
                'name_ru' => 'Администратор',
                'name_en' => 'Administrator'
            ],
            [
                'role_key' => 'moderator',
                'name_ru' => 'Модератор',
                'name_en' => 'Moderator'
            ],
            [
                'role_key' => 'expert',
                'name_ru' => 'Эксперт',
                'name_en' => 'Expert'
            ],
            [
                'role_key' => 'subscriber',
                'name_ru' => 'Подписчик',
                'name_en' => 'Subscriber'
            ]
        ];
        
        foreach ($rolesData as $roleData) {
            // Добавляем общие поля
            $roleData['created_at'] = $now;
            $roleData['updated_at'] = $now;
            
            $id = insertAndGetId($db, 'user_roles', $roleData);
            echo "  ✓ Добавлена роль пользователя: {$roleData['name_ru']} (ID: {$id})\n";
        }
    } else {
        echo "  ℹ Роли пользователей уже существуют в базе данных\n";
    }
    
    echo "\n[5/15] Добавление рынков ставок (betting_markets)...\n";
    
    // Проверяем наличие записей в таблице betting_markets
    $stmt = $db->query("SELECT COUNT(*) FROM betting_markets");
    if ($stmt->fetchColumn() == 0) {
        // Добавляем рынки ставок для футбола
        $footballMarketsData = [
            // Основные исходы
            [
                'sport_id' => $footballId,
                'market_key' => '1',
                'name_ru' => 'П1',
                'name_en' => 'Home Win',
                'market_category_name_ru' => 'Основные исходы',
                'market_category_name_en' => 'Main Outcomes',
                'description_ru' => 'Победа хозяев',
                'description_en' => 'Home team win'
            ],
            [
                'sport_id' => $footballId,
                'market_key' => 'X',
                'name_ru' => 'Ничья',
                'name_en' => 'Draw',
                'market_category_name_ru' => 'Основные исходы',
                'market_category_name_en' => 'Main Outcomes',
                'description_ru' => 'Ничья',
                'description_en' => 'Draw'
            ],
            [
                'sport_id' => $footballId,
                'market_key' => '2',
                'name_ru' => 'П2',
                'name_en' => 'Away Win',
                'market_category_name_ru' => 'Основные исходы',
                'market_category_name_en' => 'Main Outcomes',
                'description_ru' => 'Победа гостей',
                'description_en' => 'Away team win'
            ],
            
            // Двойной шанс
            [
                'sport_id' => $footballId,
                'market_key' => '1X',
                'name_ru' => '1X',
                'name_en' => '1X',
                'market_category_name_ru' => 'Двойной шанс',
                'market_category_name_en' => 'Double Chance',
                'description_ru' => 'Победа хозяев или ничья',
                'description_en' => 'Home win or draw'
            ],
            [
                'sport_id' => $footballId,
                'market_key' => '12',
                'name_ru' => '12',
                'name_en' => '12',
                'market_category_name_ru' => 'Двойной шанс',
                'market_category_name_en' => 'Double Chance',
                'description_ru' => 'Победа хозяев или гостей',
                'description_en' => 'Home win or away win'
            ],
            [
                'sport_id' => $footballId,
                'market_key' => 'X2',
                'name_ru' => 'X2',
                'name_en' => 'X2',
                'market_category_name_ru' => 'Двойной шанс',
                'market_category_name_en' => 'Double Chance',
                'description_ru' => 'Ничья или победа гостей',
                'description_en' => 'Draw or away win'
            ],
            
            // Тоталы
            [
                'sport_id' => $footballId,
                'market_key' => 'TotalOver2.5',
                'name_ru' => 'ТБ 2.5',
                'name_en' => 'Total Over 2.5',
                'market_category_name_ru' => 'Тоталы',
                'market_category_name_en' => 'Totals',
                'description_ru' => 'Тотал больше 2.5',
                'description_en' => 'Total over 2.5 goals'
            ],
            [
                'sport_id' => $footballId,
                'market_key' => 'TotalUnder2.5',
                'name_ru' => 'ТМ 2.5',
                'name_en' => 'Total Under 2.5',
                'market_category_name_ru' => 'Тоталы',
                'market_category_name_en' => 'Totals',
                'description_ru' => 'Тотал меньше 2.5',
                'description_en' => 'Total under 2.5 goals'
            ],
            
            // Форы
            [
                'sport_id' => $footballId,
                'market_key' => 'Handicap1(-1.5)',
                'name_ru' => 'Ф1(-1.5)',
                'name_en' => 'H1(-1.5)',
                'market_category_name_ru' => 'Форы',
                'market_category_name_en' => 'Handicaps',
                'description_ru' => 'Фора хозяев -1.5',
                'description_en' => 'Home team handicap -1.5'
            ],
            [
                'sport_id' => $footballId,
                'market_key' => 'Handicap2(+1.5)',
                'name_ru' => 'Ф2(+1.5)',
                'name_en' => 'H2(+1.5)',
                'market_category_name_ru' => 'Форы',
                'market_category_name_en' => 'Handicaps',
                'description_ru' => 'Фора гостей +1.5',
                'description_en' => 'Away team handicap +1.5'
            ]
        ];
        
        // Добавляем рынки ставок для тенниса
        $tennisMarketsData = [
            // Основные исходы
            [
                'sport_id' => $tennisId,
                'market_key' => '1',
                'name_ru' => 'П1',
                'name_en' => 'Player 1 Win',
                'market_category_name_ru' => 'Основные исходы',
                'market_category_name_en' => 'Main Outcomes',
                'description_ru' => 'Победа первого игрока',
                'description_en' => 'First player win'
            ],
            [
                'sport_id' => $tennisId,
                'market_key' => '2',
                'name_ru' => 'П2',
                'name_en' => 'Player 2 Win',
                'market_category_name_ru' => 'Основные исходы',
                'market_category_name_en' => 'Main Outcomes',
                'description_ru' => 'Победа второго игрока',
                'description_en' => 'Second player win'
            ],
            
            // Тоталы
            [
                'sport_id' => $tennisId,
                'market_key' => 'TotalOver21.5',
                'name_ru' => 'ТБ 21.5',
                'name_en' => 'Total Over 21.5',
                'market_category_name_ru' => 'Тоталы',
                'market_category_name_en' => 'Totals',
                'description_ru' => 'Тотал больше 21.5',
                'description_en' => 'Total over 21.5 games'
            ],
            [
                'sport_id' => $tennisId,
                'market_key' => 'TotalUnder21.5',
                'name_ru' => 'ТМ 21.5',
                'name_en' => 'Total Under 21.5',
                'market_category_name_ru' => 'Тоталы',
                'market_category_name_en' => 'Totals',
                'description_ru' => 'Тотал меньше 21.5',
                'description_en' => 'Total under 21.5 games'
            ],
            
            // Форы
            [
                'sport_id' => $tennisId,
                'market_key' => 'Handicap1(-3.5)',
                'name_ru' => 'Ф1(-3.5)',
                'name_en' => 'H1(-3.5)',
                'market_category_name_ru' => 'Форы',
                'market_category_name_en' => 'Handicaps',
                'description_ru' => 'Фора первого игрока -3.5',
                'description_en' => 'First player handicap -3.5'
            ],
            [
                'sport_id' => $tennisId,
                'market_key' => 'Handicap2(+3.5)',
                'name_ru' => 'Ф2(+3.5)',
                'name_en' => 'H2(+3.5)',
                'market_category_name_ru' => 'Форы',
                'market_category_name_en' => 'Handicaps',
                'description_ru' => 'Фора второго игрока +3.5',
                'description_en' => 'Second player handicap +3.5'
            ]
        ];
        
        // Объединяем все рынки
        $marketsData = array_merge($footballMarketsData, $tennisMarketsData);
        
        $marketIds = [];
        
        foreach ($marketsData as $marketData) {
            // Добавляем общие поля
            $marketData['created_at'] = $now;
            $marketData['updated_at'] = $now;
            
            $id = insertAndGetId($db, 'betting_markets', $marketData);
            $marketIds[$marketData['market_key']] = $id;
            echo "  ✓ Добавлен рынок ставок: {$marketData['name_ru']} (ID: {$id})\n";
        }
    } else {
        // Получаем ID существующих рынков ставок
        $marketIds = [];
        
        $stmt = $db->query("SELECT market_id, market_key FROM betting_markets");
        $markets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($markets as $market) {
            $marketIds[$market['market_key']] = $market['market_id'];
        }
        
        echo "  ℹ Рынки ставок уже существуют в базе данных\n";
    }
    
    echo "\n[6/15] Добавление типов статистики (stat_types)...\n";
    
    // Проверяем наличие записей в таблице stat_types
    $stmt = $db->query("SELECT COUNT(*) FROM stat_types");
    if ($stmt->fetchColumn() == 0) {
        // Добавляем типы статистики для футбола
        $footballStatTypesData = [
            [
                'sport_id' => $footballId,
                'stat_key' => 'goals',
                'name_ru' => 'Голы',
                'name_en' => 'Goals',
                'description_ru' => 'Количество забитых голов',
                'description_en' => 'Number of goals scored'
            ],
            [
                'sport_id' => $footballId,
                'stat_key' => 'shots',
                'name_ru' => 'Удары',
                'name_en' => 'Shots',
                'description_ru' => 'Количество ударов',
                'description_en' => 'Number of shots'
            ],
            [
                'sport_id' => $footballId,
                'stat_key' => 'shots_on_target',
                'name_ru' => 'Удары в створ',
                'name_en' => 'Shots on Target',
                'description_ru' => 'Количество ударов в створ ворот',
                'description_en' => 'Number of shots on target'
            ],
            [
                'sport_id' => $footballId,
                'stat_key' => 'possession',
                'name_ru' => 'Владение',
                'name_en' => 'Possession',
                'description_ru' => 'Процент владения мячом',
                'description_en' => 'Ball possession percentage'
            ],
            [
                'sport_id' => $footballId,
                'stat_key' => 'corners',
                'name_ru' => 'Угловые',
                'name_en' => 'Corners',
                'description_ru' => 'Количество угловых',
                'description_en' => 'Number of corner kicks'
            ]
        ];
        
        // Добавляем типы статистики для тенниса
        $tennisStatTypesData = [
            [
                'sport_id' => $tennisId,
                'stat_key' => 'aces',
                'name_ru' => 'Эйсы',
                'name_en' => 'Aces',
                'description_ru' => 'Количество эйсов',
                'description_en' => 'Number of aces'
            ],
            [
                'sport_id' => $tennisId,
                'stat_key' => 'double_faults',
                'name_ru' => 'Двойные ошибки',
                'name_en' => 'Double Faults',
                'description_ru' => 'Количество двойных ошибок',
                'description_en' => 'Number of double faults'
            ],
            [
                'sport_id' => $tennisId,
                'stat_key' => 'first_serve_percentage',
                'name_ru' => 'Процент первой подачи',
                'name_en' => 'First Serve Percentage',
                'description_ru' => 'Процент попадания первой подачи',
                'description_en' => 'First serve percentage'
            ],
            [
                'sport_id' => $tennisId,
                'stat_key' => 'break_points_converted',
                'name_ru' => 'Реализация брейк-поинтов',
                'name_en' => 'Break Points Converted',
                'description_ru' => 'Количество реализованных брейк-поинтов',
                'description_en' => 'Number of break points converted'
            ]
        ];
        
        // Объединяем все типы статистики
        $statTypesData = array_merge($footballStatTypesData, $tennisStatTypesData);
        
        foreach ($statTypesData as $statTypeData) {
            // Добавляем общие поля
            $statTypeData['created_at'] = $now;
            $statTypeData['updated_at'] = $now;
            
            $id = insertAndGetId($db, 'stat_types', $statTypeData);
            echo "  ✓ Добавлен тип статистики: {$statTypeData['name_ru']} (ID: {$id})\n";
        }
    } else {
        echo "  ℹ Типы статистики уже существуют в базе данных\n";
    }
    
    echo "\n[7/15] Добавление типов событий (event_types)...\n";
    
    // Проверяем наличие записей в таблице event_types
    $stmt = $db->query("SELECT COUNT(*) FROM event_types");
    if ($stmt->fetchColumn() == 0) {
        // Добавляем типы событий
        $eventTypesData = [
            [
                'event_type_key' => 'user_registration',
                'name_ru' => 'Регистрация пользователя',
                'name_en' => 'User Registration',
                'description_ru' => 'Регистрация нового пользователя',
                'description_en' => 'New user registration'
            ],
            [
                'event_type_key' => 'user_login',
                'name_ru' => 'Вход пользователя',
                'name_en' => 'User Login',
                'description_ru' => 'Вход пользователя в систему',
                'description_en' => 'User login to the system'
            ],
            [
                'event_type_key' => 'match_add_favorite',
                'name_ru' => 'Добавление матча в избранное',
                'name_en' => 'Match Added to Favorites',
                'description_ru' => 'Пользователь добавил матч в избранное',
                'description_en' => 'User added a match to favorites'
            ],
            [
                'event_type_key' => 'subscription_purchase',
                'name_ru' => 'Покупка подписки',
                'name_en' => 'Subscription Purchase',
                'description_ru' => 'Пользователь приобрел подписку',
                'description_en' => 'User purchased a subscription'
            ],
            [
                'event_type_key' => 'referral_signup',
                'name_ru' => 'Регистрация по реферальной ссылке',
                'name_en' => 'Referral Signup',
                'description_ru' => 'Регистрация пользователя по реферальной ссылке',
                'description_en' => 'User registration via referral link'
            ]
        ];
        
        foreach ($eventTypesData as $eventTypeData) {
            // Добавляем общие поля
            $eventTypeData['created_at'] = $now;
            $eventTypeData['updated_at'] = $now;
            
            $id = insertAndGetId($db, 'event_types', $eventTypeData);
            echo "  ✓ Добавлен тип события: {$eventTypeData['name_ru']} (ID: {$id})\n";
        }
    } else {
        echo "  ℹ Типы событий уже существуют в базе данных\n";
    }
    
    echo "\n[8/15] Добавление типов бонусов (bonus_types)...\n";
    
    // Проверяем наличие записей в таблице bonus_types
    $stmt = $db->query("SELECT COUNT(*) FROM bonus_types");
    if ($stmt->fetchColumn() == 0) {
        // Добавляем типы бонусов
        $bonusTypesData = [
            [
                'bonus_type_key' => 'registration',
                'name_ru' => 'Бонус за регистрацию',
                'name_en' => 'Registration Bonus',
                'description_ru' => 'Бонус за регистрацию нового аккаунта',
                'description_en' => 'Bonus for registering a new account'
            ],
            [
                'bonus_type_key' => 'referral',
                'name_ru' => 'Реферальный бонус',
                'name_en' => 'Referral Bonus',
                'description_ru' => 'Бонус за приглашение нового пользователя',
                'description_en' => 'Bonus for referring a new user'
            ],
            [
                'bonus_type_key' => 'subscription',
                'name_ru' => 'Бонус за подписку',
                'name_en' => 'Subscription Bonus',
                'description_ru' => 'Бонус за оформление подписки',
                'description_en' => 'Bonus for purchasing a subscription'
            ],
            [
                'bonus_type_key' => 'daily_login',
                'name_ru' => 'Ежедневный бонус',
                'name_en' => 'Daily Login Bonus',
                'description_ru' => 'Бонус за ежедневный вход',
                'description_en' => 'Bonus for daily login'
            ]
        ];
        
        foreach ($bonusTypesData as $bonusTypeData) {
            // Добавляем общие поля
            $bonusTypeData['created_at'] = $now;
            $bonusTypeData['updated_at'] = $now;
            
            $id = insertAndGetId($db, 'bonus_types', $bonusTypeData);
            echo "  ✓ Добавлен тип бонуса: {$bonusTypeData['name_ru']} (ID: {$id})\n";
        }
    } else {
        echo "  ℹ Типы бонусов уже существуют в базе данных\n";
    }
    
    echo "\n[9/15] Добавление типов достижений (achievement_types)...\n";
    
    // Проверяем наличие записей в таблице achievement_types
    $stmt = $db->query("SELECT COUNT(*) FROM achievement_types");
    if ($stmt->fetchColumn() == 0) {
        // Добавляем типы достижений
        $achievementTypesData = [
            [
                'achievement_type_key' => 'registration',
                'name_ru' => 'Новичок',
                'name_en' => 'Newcomer',
                'description_ru' => 'Регистрация на платформе',
                'description_en' => 'Registered on the platform'
            ],
            [
                'achievement_type_key' => 'favorites_10',
                'name_ru' => 'Коллекционер',
                'name_en' => 'Collector',
                'description_ru' => 'Добавлено 10 матчей в избранное',
                'description_en' => 'Added 10 matches to favorites'
            ],
            [
                'achievement_type_key' => 'login_streak_7',
                'name_ru' => 'Постоянный клиент',
                'name_en' => 'Regular Customer',
                'description_ru' => '7 дней подряд на платформе',
                'description_en' => '7-day login streak'
            ],
            [
                'achievement_type_key' => 'first_subscription',
                'name_ru' => 'Подписчик',
                'name_en' => 'Subscriber',
                'description_ru' => 'Первая подписка оформлена',
                'description_en' => 'First subscription purchased'
            ]
        ];
        
        foreach ($achievementTypesData as $achievementTypeData) {
            // Добавляем общие поля
            $achievementTypeData['created_at'] = $now;
            $achievementTypeData['updated_at'] = $now;
            
            $id = insertAndGetId($db, 'achievement_types', $achievementTypeData);
            echo "  ✓ Добавлен тип достижения: {$achievementTypeData['name_ru']} (ID: {$id})\n";
        }
    } else {
        echo "  ℹ Типы достижений уже существуют в базе данных\n";
    }
    
    echo "\n[10/15] Добавление тестовых пользователей (users)...\n";
    
    // Проверяем наличие тестовых пользователей
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
    $stmt->execute(['test_user']);
    
    if ($stmt->fetchColumn() == 0) {
        // Добавляем тестовых пользователей
        $usersData = [
            [
                'username' => 'test_user',
                'email' => 'test@example.com',
                'password_hash' => password_hash('test123', PASSWORD_DEFAULT),
                'role_key' => 'user'
            ],
            [
                'username' => 'admin_user',
                'email' => 'admin@example.com',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'role_key' => 'admin'
            ],
            [
                'username' => 'expert_user',
                'email' => 'expert@example.com',
                'password_hash' => password_hash('expert123', PASSWORD_DEFAULT),
                'role_key' => 'expert'
            ]
        ];
        
        foreach ($usersData as $userData) {
            // Добавляем общие поля
            $userData['created_at'] = $now;
            $userData['updated_at'] = $now;
            
            $id = insertAndGetId($db, 'users', $userData);
            echo "  ✓ Добавлен пользователь: {$userData['username']} (ID: {$id})\n";
            echo "    Логин: {$userData['username']}, Пароль: " . substr($userData['username'], 0, strpos($userData['username'], '_')) . "123\n";
        }
    } else {
        echo "  ℹ Тестовые пользователи уже существуют в базе данных\n";
    }
    
    echo "\n[11/15] Добавление сезонов (seasons)...\n";
    
    // Проверяем наличие записей в таблице seasons
    $stmt = $db->query("SELECT COUNT(*) FROM seasons");
    if ($stmt->fetchColumn() == 0) {
        // Добавляем сезоны
        $seasonsData = [
            [
                'name_ru' => 'Сезон 2023-2024',
                'name_en' => 'Season 2023-2024',
                'start_date' => '2023-08-01',
                'end_date' => '2024-05-31',
                'is_active' => 1
            ],
            [
                'name_ru' => 'Сезон 2024-2025',
                'name_en' => 'Season 2024-2025',
                'start_date' => '2024-08-01',
                'end_date' => '2025-05-31',
                'is_active' => 1
            ]
        ];
        
        $seasonIds = [];
        
        foreach ($seasonsData as $seasonData) {
            // Добавляем общие поля
            $seasonData['created_at'] = $now;
            $seasonData['updated_at'] = $now;
            
            $id = insertAndGetId($db, 'seasons', $seasonData);
            $seasonIds[$seasonData['name_en']] = $id;
            echo "  ✓ Добавлен сезон: {$seasonData['name_ru']} (ID: {$id})\n";
        }
        
        $currentSeasonId = $seasonIds['Season 2024-2025'];
    } else {
        // Получаем ID существующих сезонов
        $stmt = $db->query("SELECT season_id, name_en FROM seasons WHERE is_active = 1 ORDER BY start_date DESC LIMIT 1");
        $season = $stmt->fetch(PDO::FETCH_ASSOC);
        $currentSeasonId = $season['season_id'];
        
        echo "  ℹ Сезоны уже существуют в базе данных, текущий сезон ID: {$currentSeasonId}\n";
    }
    
    echo "\n[12/15] Добавление футбольных клубов (clubs)...\n";
    
    // Проверяем наличие футбольных клубов
    $stmt = $db->prepare("SELECT COUNT(*) FROM clubs WHERE sport_id = ?");
    $stmt->execute([$footballId]);
    
    if ($stmt->fetchColumn() < 6) {
        // Добавляем российские клубы
        $russianClubs = [
            [
                'country_id' => $countryIds['RU'],
                'name_ru' => 'Спартак',
                'name_en' => 'Spartak Moscow',
                'short_name_ru' => 'Спартак',
                'short_name_en' => 'Spartak'
            ],
            [
                'country_id' => $countryIds['RU'],
                'name_ru' => 'ЦСКА',
                'name_en' => 'CSKA Moscow',
                'short_name_ru' => 'ЦСКА',
                'short_name_en' => 'CSKA'
            ],
            [
                'country_id' => $countryIds['RU'],
                'name_ru' => 'Зенит',
                'name_en' => 'Zenit St. Petersburg',
                'short_name_ru' => 'Зенит',
                'short_name_en' => 'Zenit'
            ]
        ];
        
        // Добавляем английские клубы
        $englishClubs = [
            [
                'country_id' => $countryIds['GB'],
                'name_ru' => 'Манчестер Сити',
                'name_en' => 'Manchester City',
                'short_name_ru' => 'Ман Сити',
                'short_name_en' => 'Man City'
            ],
            [
                'country_id' => $countryIds['GB'],
                'name_ru' => 'Ливерпуль',
                'name_en' => 'Liverpool',
                'short_name_ru' => 'Ливерпуль',
                'short_name_en' => 'Liverpool'
            ],
            [
                'country_id' => $countryIds['GB'],
                'name_ru' => 'Арсенал',
                'name_en' => 'Arsenal',
                'short_name_ru' => 'Арсенал',
                'short_name_en' => 'Arsenal'
            ]
        ];
        
        $clubsData = array_merge($russianClubs, $englishClubs);
        $clubIds = [];
        
        foreach ($clubsData as $clubData) {
            // Добавляем общие поля
            $clubData['sport_id'] = $footballId;
            $clubData['is_active'] = 1;
            $clubData['created_at'] = $now;
            $clubData['updated_at'] = $now;
            
            $id = insertAndGetId($db, 'clubs', $clubData);
            $clubIds[$clubData['name_en']] = $id;
            echo "  ✓ Добавлен клуб: {$clubData['name_ru']} (ID: {$id})\n";
        }
    } else {
        // Получаем ID существующих клубов
        $clubIds = [];
        
        $stmt = $db->query("SELECT club_id, name_en FROM clubs WHERE sport_id = {$footballId}");
        $clubs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($clubs as $club) {
            $clubIds[$club['name_en']] = $club['club_id'];
        }
        
        echo "  ℹ Футбольные клубы уже существуют в базе данных\n";
    }
    
    echo "\n[13/15] Добавление футбольных лиг (leagues)...\n";
    
    // Проверяем наличие футбольных лиг
    $stmt = $db->prepare("SELECT COUNT(*) FROM leagues WHERE sport_id = ?");
    $stmt->execute([$footballId]);
    
    if ($stmt->fetchColumn() < 2) {
        // Добавляем лиги
        $leaguesData = [
            [
                'sport_id' => $footballId,
                'country_id' => $countryIds['RU'],
                'name_ru' => 'Российская Премьер-Лига',
                'name_en' => 'Russian Premier League',
                'slug_ru' => 'rpl',
                'slug_en' => 'russian-premier-league',
                'level' => 1
            ],
            [
                'sport_id' => $footballId,
                'country_id' => $countryIds['GB'],
                'name_ru' => 'Английская Премьер-Лига',
                'name_en' => 'English Premier League',
                'slug_ru' => 'apl',
                'slug_en' => 'english-premier-league',
                'level' => 1
            ]
        ];
        
        $leagueIds = [];
        
        foreach ($leaguesData as $leagueData) {
            // Добавляем общие поля
            $leagueData['is_active'] = 1;
            $leagueData['created_at'] = $now;
            $leagueData['updated_at'] = $now;
            
            $id = insertAndGetId($db, 'leagues', $leagueData);
            $leagueIds[$leagueData['name_en']] = $id;
            echo "  ✓ Добавлена лига: {$leagueData['name_ru']} (ID: {$id})\n";
        }
    } else {
        // Получаем ID существующих лиг
        $leagueIds = [];
        
        $stmt = $db->query("SELECT league_id, name_en FROM leagues WHERE sport_id = {$footballId}");
        $leagues = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($leagues as $league) {
            $leagueIds[$league['name_en']] = $league['league_id'];
        }
        
        echo "  ℹ Футбольные лиги уже существуют в базе данных\n";
    }
    
    echo "\n[14/15] Добавление демо-матчей (matches)...\n";
    
    // Проверяем наличие матчей
    $stmt = $db->query("SELECT COUNT(*) FROM matches");
    if ($stmt->fetchColumn() < 3) {
        // Подготавливаем данные для матчей
        $spartakId = $clubIds['Spartak Moscow'] ?? 1;
        $cskaId = $clubIds['CSKA Moscow'] ?? 2;
        $zenitId = $clubIds['Zenit St. Petersburg'] ?? 3;
        $mancityId = $clubIds['Manchester City'] ?? 4;
        $liverpoolId = $clubIds['Liverpool'] ?? 5;
        $arsenalId = $clubIds['Arsenal'] ?? 6;
        
        $rplId = $leagueIds['Russian Premier League'] ?? 1;
        $eplId = $leagueIds['English Premier League'] ?? 2;
        
        // Добавляем матчи
        $matchesData = [
            [
                'sport_id' => $footballId,
                'league_id' => $rplId,
                'season_id' => $currentSeasonId,
                'match_datetime' => date('Y-m-d H:i:s', strtotime('+2 days')),
                'home_team_id' => $spartakId,
                'away_team_id' => $cskaId,
                'status' => 'scheduled'
            ],
            [
                'sport_id' => $footballId,
                'league_id' => $rplId,
                'season_id' => $currentSeasonId,
                'match_datetime' => date('Y-m-d H:i:s', strtotime('+5 days')),
                'home_team_id' => $zenitId,
                'away_team_id' => $spartakId,
                'status' => 'scheduled'
            ],
            [
                'sport_id' => $footballId,
                'league_id' => $eplId,
                'season_id' => $currentSeasonId,
                'match_datetime' => date('Y-m-d H:i:s', strtotime('+3 days')),
                'home_team_id' => $mancityId,
                'away_team_id' => $liverpoolId,
                'status' => 'scheduled'
            ],
            [
                'sport_id' => $footballId,
                'league_id' => $eplId,
                'season_id' => $currentSeasonId,
                'match_datetime' => date('Y-m-d H:i:s', strtotime('+7 days')),
                'home_team_id' => $arsenalId,
                'away_team_id' => $mancityId,
                'status' => 'scheduled'
            ],
            [
                'sport_id' => $footballId,
                'league_id' => $eplId,
                'season_id' => $currentSeasonId,
                'match_datetime' => date('Y-m-d H:i:s', strtotime('-1 days')),
                'home_team_id' => $liverpoolId,
                'away_team_id' => $arsenalId,
                'status' => 'finished'
            ]
        ];
        
        $matchIds = [];
        
        foreach ($matchesData as $matchData) {
            // Добавляем общие поля
            $matchData['created_at'] = $now;
            $matchData['updated_at'] = $now;
            
            $id = insertAndGetId($db, 'matches', $matchData);
            $matchIds[] = $id;
            
            // Получаем имена команд для лога
            $stmt = $db->prepare("
                SELECT 
                    h.name_ru AS home_team,
                    a.name_ru AS away_team
                FROM 
                    clubs h
                    JOIN clubs a ON a.club_id = ?
                WHERE 
                    h.club_id = ?
            ");
            $stmt->execute([$matchData['away_team_id'], $matchData['home_team_id']]);
            $teams = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo "  ✓ Добавлен матч: {$teams['home_team']} - {$teams['away_team']} (ID: {$id})\n";
        }
        
        echo "\n  ✓ Добавляем коэффициенты для матчей...\n";
        
        // Добавляем коэффициенты для каждого матча
        foreach ($matchIds as $matchId) {
            // Коэффициенты на победу хозяев (П1)
            insertAndGetId($db, 'odds', [
                'match_id' => $matchId,
                'bookmaker_id' => $fonbetSourceId,
                'market_id' => $marketIds['1'],
                'odd_value' => rand(150, 350) / 100,
                'created_at' => $now,
                'updated_at' => $now
            ]);
            
            // Коэффициенты на ничью (X)
            insertAndGetId($db, 'odds', [
                'match_id' => $matchId,
                'bookmaker_id' => $fonbetSourceId,
                'market_id' => $marketIds['X'],
                'odd_value' => rand(300, 450) / 100,
                'created_at' => $now,
                'updated_at' => $now
            ]);
            
            // Коэффициенты на победу гостей (П2)
            insertAndGetId($db, 'odds', [
                'match_id' => $matchId,
                'bookmaker_id' => $fonbetSourceId,
                'market_id' => $marketIds['2'],
                'odd_value' => rand(200, 500) / 100,
                'created_at' => $now,
                'updated_at' => $now
            ]);
            
            // Коэффициенты на тотал больше 2.5
            insertAndGetId($db, 'odds', [
                'match_id' => $matchId,
                'bookmaker_id' => $fonbetSourceId,
                'market_id' => $marketIds['TotalOver2.5'],
                'odd_value' => rand(170, 220) / 100,
                'created_at' => $now,
                'updated_at' => $now
            ]);
            
            // Коэффициенты на тотал меньше 2.5
            insertAndGetId($db, 'odds', [
                'match_id' => $matchId,
                'bookmaker_id' => $fonbetSourceId,
                'market_id' => $marketIds['TotalUnder2.5'],
                'odd_value' => rand(170, 220) / 100,
                'created_at' => $now,
                'updated_at' => $now
            ]);
        }
        
        // Сохраняем ID матчей для AI-прогнозов
        $demoMatch1Id = $matchIds[0] ?? null;
        $demoMatch2Id = $matchIds[2] ?? null;
    } else {
        // Получаем ID существующих матчей для AI-прогнозов
        $stmt = $db->query("SELECT match_id FROM matches ORDER BY match_datetime LIMIT 2");
        $demoMatches = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        $demoMatch1Id = $demoMatches[0] ?? null;
        $demoMatch2Id = $demoMatches[1] ?? null;
        
        echo "  ℹ Матчи и коэффициенты уже существуют в базе данных\n";
    }
    
    echo "\n[15/15] Добавление AI-прогнозов (ext_ai_explanations)...\n";
    
    // Проверяем наличие таблицы ext_ai_explanations
    $stmt = $db->query("SHOW TABLES LIKE 'ext_ai_explanations'");
    if ($stmt->rowCount() > 0) {
        // Проверяем наличие прогнозов
        $stmt = $db->query("SELECT COUNT(*) FROM ext_ai_explanations");
        if ($stmt->fetchColumn() == 0 && $demoMatch1Id && $demoMatch2Id) {
            // Добавляем AI-прогнозы
            $aiPredictionsData = [
                [
                    'match_id' => $demoMatch1Id,
                    'explanation_text' => "Анализ матча:\n\nДобро пожаловать на комплексный разбор предстоящего матча от AI-аналитика 'Палыч'!\n\nИзучив последние 10 встреч команд, могу отметить следующие ключевые тенденции:\n- Хозяева поля выиграли 6 из 10 последних домашних матчей\n- Гости имеют серию из 3 игр без поражений\n- В 7 из 10 последних очных встреч команд было забито больше 2.5 голов\n\nСтатистика ключевых игроков показывает высокую форму нападающих хозяев, что может стать решающим фактором. При этом, гости имеют проблемы в обороне (3 пропущенных гола в последнем матче).\n\nРекомендация: Рассмотрите ставку на победу хозяев (П1) с коэффициентом выше 1.90 или на тотал больше 2.5 с коэффициентом от 1.75.\n\nУверенность прогноза: 72%"
                ],
                [
                    'match_id' => $demoMatch2Id,
                    'explanation_text' => "Анализ матча:\n\nПриветствую вас, я AI-аналитик 'Палыч'! Представляю мой анализ предстоящего матча.\n\nОбе команды находятся в хорошей форме, что обещает зрелищную игру. Анализ последних выступлений выявил следующие тенденции:\n- Гости не проигрывают в 5 последних выездных матчах\n- Хозяева забивают в среднем 2.3 гола за матч на своем поле\n- В 8 из 10 последних личных встреч команд забивали обе команды\n\nВажный фактор: у хозяев травмированы два ключевых защитника, что может серьезно ослабить оборонительную линию.\n\nМой прогноз на этот матч: наиболее вероятен исход с голами обеих команд, рекомендую рассмотреть ставку 'Обе забьют - Да' с коэффициентом выше 1.65 или двойной шанс X2 с коэффициентом от 1.50.\n\nУверенность прогноза: 68%"
                ]
            ];
            
            foreach ($aiPredictionsData as $aiPredictionData) {
                // Добавляем общие поля
                $aiPredictionData['created_at'] = $now;
                $aiPredictionData['updated_at'] = $now;
                
                $id = insertAndGetId($db, 'ext_ai_explanations', $aiPredictionData);
                echo "  ✓ Добавлен AI-прогноз для матча ID: {$aiPredictionData['match_id']} (ID прогноза: {$id})\n";
            }
        } else {
            echo "  ℹ AI-прогнозы уже существуют в базе данных или не указаны ID матчей\n";
        }
    } else {
        echo "  ⚠ Таблица ext_ai_explanations не найдена, AI-прогнозы не добавлены\n";
    }
    
    // Фиксируем транзакцию
    $db->commit();
    
    echo "\n✅ Наполнение базы данных демо-данными успешно завершено!\n";
    echo "  - Созданы справочники (спорты, страны, лиги, клубы, рынки ставок и т.д.)\n";
    echo "  - Добавлены тестовые пользователи (логин/пароль: test_user/test123, admin_user/admin123, expert_user/expert123)\n";
    echo "  - Созданы демо-матчи с коэффициентами и AI-прогнозами\n\n";
    echo "Теперь вы можете запустить приложение и использовать демо-данные для тестирования.\n";
    
} catch (PDOException $e) {
    // Откатываем транзакцию в случае ошибки
    $db->rollBack();
    echo "\n❌ Ошибка: " . $e->getMessage() . "\n";
    echo "Транзакция отменена. Пожалуйста, устраните ошибку и попробуйте снова.\n";
    exit(1);
}