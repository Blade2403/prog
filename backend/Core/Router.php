### 1.2. Маршрутизатор

**Файл: prognozai/backend/core/Router.php**

```php
<?php
/**
 * Маршрутизатор для обработки URL и вызова контроллеров
 * 
 * Поддерживает маршруты с параметрами, переопределение HTTP-методов и обработку 404-ошибок
 */
class Router
{
    /**
     * Массив зарегистрированных маршрутов
     * [HTTP-метод => [маршрут => обработчик]]
     * 
     * @var array
     */
    private $routes = [];
    
    /**
     * Обработчик 404-ошибки (страница не найдена)
     * 
     * @var callable
     */
    private $notFoundHandler;
    
    /**
     * Регистрирует маршрут для GET-запроса
     * 
     * @param string $path Путь URL (может содержать параметры в формате {param})
     * @param callable $handler Функция-обработчик или массив [класс, метод]
     * @return Router
     */
    public function get($path, $handler)
    {
        $this->addRoute('GET', $path, $handler);
        return $this;
    }
    
    /**
     * Регистрирует маршрут для POST-запроса
     * 
     * @param string $path Путь URL (может содержать параметры в формате {param})
     * @param callable $handler Функция-обработчик или массив [класс, метод]
     * @return Router
     */
    public function post($path, $handler)
    {
        $this->addRoute('POST', $path, $handler);
        return $this;
    }
    
    /**
     * Регистрирует маршрут для любого HTTP-метода
     * 
     * @param string $method HTTP-метод (GET, POST, PUT, DELETE и т.д.)
     * @param string $path Путь URL (может содержать параметры в формате {param})
     * @param callable $handler Функция-обработчик или массив [класс, метод]
     * @return Router
     */
    public function addRoute($method, $path, $handler)
    {
        $this->routes[strtoupper($method)][$path] = $handler;
        return $this;
    }
    
    /**
     * Устанавливает обработчик для 404-ошибки (страница не найдена)
     * 
     * @param callable $handler Функция-обработчик
     * @return Router
     */
    public function notFound($handler)
    {
        $this->notFoundHandler = $handler;
        return $this;
    }
    
    /**
     * Обрабатывает текущий HTTP-запрос
     * 
     * @return void
     */
    public function resolve()
    {
        // Получаем HTTP-метод и URL текущего запроса
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Удаляем trailing slash, если это не корневой URL
        if ($uri !== '/' && substr($uri, -1) === '/') {
            $uri = rtrim($uri, '/');
        }
        
        // Проверяем, существует ли прямое совпадение маршрута
        if (isset($this->routes[$method][$uri])) {
            call_user_func($this->routes[$method][$uri]);
            return;
        }
        
        // Проверяем маршруты с параметрами
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            // Преобразуем шаблон маршрута в регулярное выражение
            $pattern = $this->convertRouteToRegex($route);
            
            // Если URL соответствует шаблону маршрута
            if (preg_match($pattern, $uri, $matches)) {
                // Удаляем первый элемент (полное совпадение)
                array_shift($matches);
                
                // Вызываем обработчик с извлеченными параметрами
                call_user_func_array($handler, $matches);
                return;
            }
        }
        
        // Если маршрут не найден, вызываем обработчик 404-ошибки
        if ($this->notFoundHandler) {
            call_user_func($this->notFoundHandler);
        } else {
            // Если обработчик 404-ошибки не установлен, выводим стандартное сообщение
            header('HTTP/1.0 404 Not Found');
            echo '<h1>404 Not Found</h1>';
            echo '<p>The requested URL was not found on this server.</p>';
        }
    }
    
    /**
     * Преобразует шаблон маршрута в регулярное выражение
     * 
     * @param string $route Шаблон маршрута
     * @return string Регулярное выражение
     */
    private function convertRouteToRegex($route)
    {
        // Заменяем {param} на ([^/]+)
        $pattern = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $route);
        // Экранируем символы пути
        $pattern = str_replace('/', '\/', $pattern);
        // Добавляем начало и конец строки
        $pattern = '/^' . $pattern . '$/';
        
        return $pattern;
    }
}