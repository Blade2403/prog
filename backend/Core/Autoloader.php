## 1. Архитектурные компоненты ядра

### 1.1. Автозагрузчик классов

**Файл: prognozai/backend/core/Autoloader.php**

<?php
/**
 * Автозагрузчик классов для PrognozAi.ru
 * 
 * Реализация PSR-4 совместимого автозагрузчика для неймспейсов PrognozAi\*
 */
class Autoloader
{
    /**
     * Регистрирует автозагрузчик
     */
    public static function register()
    {
        spl_autoload_register([self::class, 'loadClass']);
    }

    /**
     * Загружает класс по его имени
     * 
     * @param string $className Полное имя класса (с неймспейсом)
     */
    public static function loadClass($className)
    {
        // Базовый путь проекта
        $basePath = dirname(__DIR__, 2);
        
        // Маппинг пространств имён (неймспейсов) на директории
        $namespaceMap = [
            'PrognozAi\\Core' => $basePath . '/backend/core/',
            'PrognozAi\\Controllers' => $basePath . '/backend/Controllers/',
            'PrognozAi\\DataManagement\\Repositories' => $basePath . '/backend/DataManagement/Repositories/',
            'PrognozAi\\DataManagement\\Entities' => $basePath . '/backend/DataManagement/Entities/',
            'PrognozAi\\Services' => $basePath . '/backend/Services/',
        ];
        
        // Проверяем, содержит ли класс пространство имён
        if (strpos($className, '\\') !== false) {
            // Для классов с неймспейсом
            foreach ($namespaceMap as $namespace => $dir) {
                // Если класс начинается с этого неймспейса
                if (strpos($className, $namespace) === 0) {
                    // Удаляем неймспейс из имени класса
                    $relativeClass = substr($className, strlen($namespace) + 1);
                    // Заменяем разделители неймспейса на разделители директорий
                    $filePath = $dir . str_replace('\\', '/', $relativeClass) . '.php';
                    
                    if (file_exists($filePath)) {
                        require $filePath;
                        return;
                    }
                }
            }
        } else {
            // Для классов без неймспейса (легаси или утилиты)
            // Проверяем наиболее вероятные расположения
            
            // Контроллеры
            $file = $basePath . '/backend/Controllers/' . $className . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
            
            // Ядро системы
            $file = $basePath . '/backend/core/' . $className . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
            
            // Репозитории
            $file = $basePath . '/backend/DataManagement/Repositories/' . $className . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
            
            // Сущности
            $file = $basePath . '/backend/DataManagement/Entities/' . $className . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
            
            // Сервисы
            $file = $basePath . '/backend/Services/' . $className . '.php';
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
}

// Регистрируем автозагрузчик
Autoloader::register();