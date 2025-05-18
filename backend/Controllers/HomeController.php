## 2. Контроллеры

### 2.1. Контроллер для главной страницы

**Файл: prognozai/backend/Controllers/HomeController.php**

<?php
/**
 * Контроллер для главной страницы
 */
class HomeController extends BaseController
{
    /**
     * Репозиторий для работы с матчами
     * 
     * @var MatchesRepository
     */
    protected $matchesRepository;
    
    /**
     * Репозиторий для работы с коэффициентами
     * 
     * @var OddsRepository
     */
    protected $oddsRepository;
    
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        
        // Инициализируем репозитории
        $this->matchesRepository = new MatchesRepository($this->db);
        $this->oddsRepository = new OddsRepository($this->db);
    }
    
    /**
     * Отображает главную страницу
     */
    public function index()
    {
        // Получаем предстоящие матчи (лимит 6 для главной страницы)
        $upcomingMatches = $this->matchesRepository->getUpcomingMatches(6);
        
        // Дополняем матчи информацией о коэффициентах
        foreach ($upcomingMatches as &$match) {
            $match['odds'] = $this->oddsRepository->getBasicOddsByMatchId($match['match_id']);
            
            // Проверяем, является ли матч избранным для текущего пользователя
            if ($this->isLoggedIn()) {
                // Получаем репозиторий избранных матчей
                $userFavoritesRepository = new UserFavoritesRepository($this->db);
                $match['is_favorite'] = $userFavoritesRepository->isFavorite($_SESSION['user_id'], $match['match_id']);
            }
        }
        
        // Отображаем шаблон
        echo $this->view->render('home', [
            'pageTitle' => 'Главная',
            'pageDescription' => $this->config['siteDescription'],
            'upcomingMatches' => $upcomingMatches,
            'flashMessages' => $this->getAllFlashes()
        ]);
    }
}