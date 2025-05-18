**Файл: prognozai/backend/public/index.php**

```php
<?php
/**
 * Точка входа для PrognozAi.ru
 */

// Подключаем автозагрузчик классов
require_once __DIR__ . '/../core/Autoloader.php';

// Включаем вывод ошибок в режиме разработки
$config = require_once __DIR__ . '/../config/app_config.php';
if ($config['debug']) {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
}

// Инициализируем сессию
session_start();

// Создаем экземпляр маршрутизатора
$router = new Router();

// === Определяем маршруты ===

// Главная страница
$router->get('/', function() {
    $controller = new HomeController();
    $controller->index();
});

// Матчи
$router->get('/matches', function() {
    $controller = new MatchController();
    $controller->index();
});

$router->get('/matches/{id}', function($id) {
    $controller = new MatchController();
    $controller->show($id);
});

$router->post('/matches/favorite', function() {
    $controller = new MatchController();
    $controller->toggleFavorite();
});

// Пользователи
$router->get('/register', function() {
    $controller = new UserController();
    $controller->registerForm();
});

$router->post('/register', function() {
    $controller = new UserController();
    $controller->register();
});

$router->get('/login', function() {
    $controller = new UserController();
    $controller->loginForm();
});

$router->post('/login', function() {
    $controller = new UserController();
    $controller->login();
});

$router->get('/logout', function() {
    $controller = new UserController();
    $controller->logout();
});

$router->get('/profile', function() {
    $controller = new UserController();
    $controller->profile();
});

// Выбор языка
$router->get('/language/{lang}', function($lang) {
    // Проверяем, что язык поддерживается
    $supportedLanguages = $config['supportedLanguages'] ?? ['ru' => 'Русский', 'en' => 'English'];
    if (isset($supportedLanguages[$lang])) {
        $_SESSION['language'] = $lang;
    }
    
    // Перенаправляем обратно на предыдущую страницу
    $referer = $_SERVER['HTTP_REFERER'] ?? '/';
    header("Location: $referer");
    exit;
});

// Страница FAQ
$router->get('/faq', function() {
    // В будущем здесь будет контроллер для FAQ
    // Временно используем базовый контроллер и шаблон
    $controller = new BaseController();
    echo $controller->view->render('faq', [
        'pageTitle' => 'FAQ',
        'pageDescription' => 'Часто задаваемые вопросы о PrognozAi.ru',
        'flashMessages' => $controller->getAllFlashes()
    ]);
});

// Страница сообщества
$router->get('/community', function() {
    // В будущем здесь будет контроллер для сообщества
    // Временно используем базовый контроллер и шаблон
    $controller = new BaseController();
    echo $controller->view->render('community', [
        'pageTitle' => 'Сообщество',
        'pageDescription' => 'Сообщество аналитиков и прогнозистов PrognozAi.ru',
        'flashMessages' => $controller->getAllFlashes()
    ]);
});

// Обработчик для страницы 404 (не найдено)
$router->notFound(function() {
    $controller = new BaseController();
    header('HTTP/1.0 404 Not Found');
    echo $controller->view->render('errors/404', [
        'pageTitle' => 'Страница не найдена',
        'pageDescription' => 'Запрашиваемая страница не найдена',
        'message' => 'Извините, запрашиваемая страница не найдена. Возможно, она была удалена или перемещена.'
    ]);
});

// Запускаем маршрутизатор
$router->resolve();