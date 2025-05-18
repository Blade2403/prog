### 1.3. Компонент для отображения шаблонов

**Файл: prognozai/backend/core/View.php**

```php
<?php
/**
 * Компонент для отображения шаблонов
 * 
 * Поддерживает PHP-шаблоны, макеты, передачу данных в шаблоны
 * и частичное отображение (без макета)
 */
class View
{
    /**
     * Базовая директория шаблонов
     * 
     * @var string
     */
    private $templatesPath;
    
    /**
     * Имя файла макета
     * 
     * @var string|false
     */
    private $layout = 'main';
    
    /**
     * Глобальные данные, доступные во всех шаблонах
     * 
     * @var array
     */
    private $globalData = [];
    
    /**
     * Конструктор
     * 
     * @param string|null $templatesPath Путь к директории с шаблонами
     * @param array $globalData Глобальные данные, доступные всем шаблонам
     */
    public function __construct($templatesPath = null, $globalData = [])
    {
        // Если путь к шаблонам не указан, используем путь по умолчанию
        $this->templatesPath = $templatesPath ?? dirname(__DIR__, 2) . '/frontend/templates/';
        $this->globalData = $globalData;
    }
    
    /**
     * Устанавливает глобальные данные, доступные всем шаблонам
     * 
     * @param array $data Ассоциативный массив данных
     * @return View
     */
    public function setGlobalData($data)
    {
        $this->globalData = array_merge($this->globalData, $data);
        return $this;
    }
    
    /**
     * Устанавливает или отключает макет
     * 
     * @param string|false $layout Имя файла макета или false для отключения
     * @return View
     */
    public function setLayout($layout)
    {
        $this->layout = $layout;
        return $this;
    }
    
    /**
     * Отображает шаблон и возвращает результат
     * 
     * @param string $template Путь к шаблону относительно директории шаблонов
     * @param array $data Ассоциативный массив данных, передаваемых в шаблон
     * @return string HTML-код страницы
     */
    public function render($template, $data = [])
    {
        // Объединяем глобальные данные и данные, переданные в метод
        $data = array_merge($this->globalData, $data);
        
        // Проверяем существование файла шаблона
        $templateFile = $this->templatesPath . $template . '.php';
        if (!file_exists($templateFile)) {
            throw new Exception("Template file not found: {$templateFile}");
        }
        
        // Извлекаем переменные из массива данных
        extract($data);
        
        // Начинаем буферизацию вывода
        ob_start();
        
        // Подключаем файл шаблона
        include $templateFile;
        
        // Получаем содержимое буфера
        $content = ob_get_clean();
        
        // Если макет отключен, возвращаем содержимое шаблона
        if ($this->layout === false) {
            return $content;
        }
        
        // Отображаем содержимое в макете
        return $this->renderLayout($content, $data);
    }
    
    /**
     * Отображает частичный шаблон (без макета)
     * 
     * @param string $template Путь к шаблону относительно директории шаблонов
     * @param array $data Ассоциативный массив данных, передаваемых в шаблон
     * @return string HTML-код шаблона
     */
    public function renderPartial($template, $data = [])
    {
        $oldLayout = $this->layout;
        $this->layout = false;
        $content = $this->render($template, $data);
        $this->layout = $oldLayout;
        
        return $content;
    }
    
    /**
     * Отображает макет с содержимым
     * 
     * @param string $content Содержимое, которое будет вставлено в макет
     * @param array $data Ассоциативный массив данных, передаваемых в макет
     * @return string HTML-код страницы
     */
    private function renderLayout($content, $data)
    {
        // Проверяем существование файла макета
        $layoutFile = $this->templatesPath . 'layouts/' . $this->layout . '.php';
        if (!file_exists($layoutFile)) {
            throw new Exception("Layout file not found: {$layoutFile}");
        }
        
        // Извлекаем переменные из массива данных
        extract($data);
        
        // Начинаем буферизацию вывода
        ob_start();
        
        // Подключаем файл макета
        include $layoutFile;
        
        // Возвращаем содержимое буфера
        return ob_get_clean();
    }
}