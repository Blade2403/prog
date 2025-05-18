### 1.5. Базовый контроллер

**Файл: prognozai/backend/Controllers/BaseController.php**

<?php
/**
 * Базовый контроллер для наследования всеми контроллерами приложения
 * 
 * Предоставляет общие методы и функциональность для всех контроллеров
 */
class BaseController
{
    /**
     * Компонент для отображения шаблонов
     * 
     * @var View
     */
    protected $view;
    
    /**
     * Конфигурация приложения
     * 
     * @var array
     */
    protected $config;
    
    /**
     * Соединение с базой данных
     * 
     * @var PDO
     */
    protected $db;
    
    /**
     * Конструктор базового контроллера
     */
    public function __construct()
    {
        // Инициализируем сессию, если она еще не запущена
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Загружаем конфигурацию приложения
        $this->config = require dirname(__DIR__) . '/config/app_config.php';
        
        // Получаем соединение с базой данных
        $dbConfig = require dirname(__DIR__) . '/config/db_config.php';
        $this->db = $this->getDatabaseConnection($dbConfig);
        
        // Инициализируем компонент для отображения шаблонов
        $this->view = new View();
        
        // Устанавливаем глобальные данные для шаблонов
        $this->view->setGlobalData([
            'baseUrl' => $this->config['baseUrl'],
            'siteName' => $this->config['siteName'],
            'siteDescription' => $this->config['siteDescription'],
            'language' => $this->getCurrentLanguage(),
            'supportedLanguages' => $this->config['supportedLanguages'],
            'isLoggedIn' => $this->isLoggedIn(),
            'currentUser' => $this->getCurrentUser(),
            'isDebug' => $this->config['debug'],
            'currentUrl' => $this->getCurrentUrl(),
        ]);
    }
    
    /**
     * Получает соединение с базой данных
     * 
     * @param array $config Конфигурация базы данных
     * @return PDO Соединение с базой данных
     */
    protected function getDatabaseConnection($config)
    {
        try {
            // Используем DatabaseConnection, если он существует
            if (class_exists('DatabaseConnection')) {
                $dbConnection = new DatabaseConnection();
                return $dbConnection->getConnection();
            }
            
            // Иначе создаем новое соединение
            $dsn = "mysql:host={$config['host']};dbname={$config['dbname']};charset={$config['charset']}";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            return new PDO($dsn, $config['user'], $config['password'], $options);
        } catch (PDOException $e) {
            // В режиме отладки выводим ошибку
            if ($this->config['debug']) {
                die("Database connection error: " . $e->getMessage());
            }
            
            // В production просто перенаправляем на страницу ошибки
            $this->redirectToError(500);
        }
    }
    
    /**
     * Выполняет перенаправление на указанный URL
     * 
     * @param string $url URL для перенаправления
     * @return void
     */
    protected function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Перенаправляет на страницу ошибки
     * 
     * @param int $code HTTP-код ошибки
     * @return void
     */
    protected function redirectToError($code)
    {
        header("Location: {$this->config['baseUrl']}error/{$code}");
        exit;
    }
    
    /**
     * Проверяет, авторизован ли пользователь
     * 
     * @return bool
     */
    protected function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }
    
    /**
     * Получает данные текущего пользователя
     * 
     * @return array|null Данные пользователя или null, если пользователь не авторизован
     */
    protected function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }
        
        return [
            'id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'] ?? '',
            'role' => $_SESSION['role'] ?? 'user',
        ];
    }
    
    /**
     * Требует авторизации пользователя для доступа
     * 
     * @param string|null $redirectTo URL для перенаправления после авторизации
     * @return void
     */
    protected function requireLogin($redirectTo = null)
    {
        if (!$this->isLoggedIn()) {
            // Сохраняем URL для возврата после авторизации
            if ($redirectTo !== null) {
                $_SESSION['redirect_after_login'] = $redirectTo;
            } else {
                $_SESSION['redirect_after_login'] = $this->getCurrentUrl();
            }
            
            // Устанавливаем флеш-сообщение
            $this->setFlash('error', 'Необходимо войти в систему для доступа к этой странице');
            
            // Перенаправляем на страницу входа
            $this->redirect($this->config['baseUrl'] . 'login');
        }
    }
    
    /**
     * Устанавливает флеш-сообщение
     * 
     * @param string $key Ключ сообщения
     * @param string $message Текст сообщения
     * @return void
     */
    protected function setFlash($key, $message)
    {
        $_SESSION['flash_messages'][$key] = $message;
    }
    
    /**
     * Получает флеш-сообщение и удаляет его из сессии
     * 
     * @param string $key Ключ сообщения
     * @param mixed $default Значение по умолчанию
     * @return mixed Сообщение или значение по умолчанию
     */
    protected function getFlash($key, $default = null)
    {
        if (!isset($_SESSION['flash_messages'][$key])) {
            return $default;
        }
        
        $message = $_SESSION['flash_messages'][$key];
        unset($_SESSION['flash_messages'][$key]);
        
        return $message;
    }
    
    /**
     * Получает все флеш-сообщения и удаляет их из сессии
     * 
     * @return array Массив сообщений
     */
    protected function getAllFlashes()
    {
        if (!isset($_SESSION['flash_messages']) || !is_array($_SESSION['flash_messages'])) {
            return [];
        }
        
        $messages = $_SESSION['flash_messages'];
        $_SESSION['flash_messages'] = [];
        
        return $messages;
    }
    
    /**
     * Получает текущий язык пользователя
     * 
     * @return string Код языка
     */
    protected function getCurrentLanguage()
    {
        // Если язык уже установлен в сессии, используем его
        if (isset($_SESSION['language'])) {
            return $_SESSION['language'];
        }
        
        // Иначе используем язык по умолчанию
        return $this->config['language'];
    }
    
    /**
     * Получает текущий URL
     * 
     * @return string Текущий URL
     */
    protected function getCurrentUrl()
    {
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $uri = $_SERVER['REQUEST_URI'];
        
        return $protocol . $host . $uri;
    }
    
    /**
     * Выводит данные в формате JSON и завершает выполнение скрипта
     * 
     * @param array $data Данные для вывода
     * @param int $statusCode HTTP-код ответа
     * @return void
     */
    protected function jsonResponse($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data);
        exit;
    }
}