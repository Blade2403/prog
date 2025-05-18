### 2.2. Контроллер для страниц матчей

**Файл: prognozai/backend/Controllers/MatchController.php**

<?php
/**
 * Контроллер для работы с матчами
 */
class MatchController extends BaseController
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
     * Репозиторий для работы с лигами
     * 
     * @var LeaguesRepository
     */
    protected $leaguesRepository;
    
    /**
     * Репозиторий для работы с избранными матчами
     * 
     * @var UserFavoritesRepository
     */
    protected $userFavoritesRepository;
    
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        
        // Инициализируем репозитории
        $this->matchesRepository = new MatchesRepository($this->db);
        $this->oddsRepository = new OddsRepository($this->db);
        $this->leaguesRepository = new LeaguesRepository($this->db);
        
        // Инициализируем репозиторий избранных матчей только если пользователь авторизован
        if ($this->isLoggedIn()) {
            $this->userFavoritesRepository = new UserFavoritesRepository($this->db);
        }
    }
    
    /**
     * Отображает список матчей с фильтрацией и пагинацией
     */
    public function index()
    {
        // Получаем параметры фильтрации и пагинации
        $leagueId = isset($_GET['league']) ? (int)$_GET['league'] : null;
        $date = isset($_GET['date']) ? $_GET['date'] : null;
        $sportId = isset($_GET['sport']) ? (int)$_GET['sport'] : 1; // По умолчанию - футбол
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $perPage = $this->config['perPage'];
        
        // Проверяем корректность параметров
        if ($page < 1) $page = 1;
        
        // Получаем матчи с учетом фильтров и пагинации
        $matches = $this->matchesRepository->getFilteredMatches($sportId, $leagueId, $date, $page, $perPage);
        
        // Получаем общее количество матчей для пагинации
        $totalMatches = $this->matchesRepository->countFilteredMatches($sportId, $leagueId, $date);
        $totalPages = ceil($totalMatches / $perPage);
        
        // Получаем список лиг для фильтра
        $leagues = $this->leaguesRepository->getAllLeagues();
        
        // Дополняем матчи информацией о коэффициентах и избранных
        foreach ($matches as &$match) {
            $match['odds'] = $this->oddsRepository->getBasicOddsByMatchId($match['match_id']);
            
            // Проверяем, является ли матч избранным для текущего пользователя
            if ($this->isLoggedIn()) {
                $match['is_favorite'] = $this->userFavoritesRepository->isFavorite($_SESSION['user_id'], $match['match_id']);
            }
        }
        
        // Отображаем шаблон
        echo $this->view->render('matches/index', [
            'pageTitle' => 'Матчи',
            'pageDescription' => 'Список предстоящих матчей с коэффициентами',
            'matches' => $matches,
            'leagues' => $leagues,
            'filters' => [
                'leagueId' => $leagueId,
                'date' => $date,
                'sportId' => $sportId
            ],
            'pagination' => [
                'page' => $page,
                'perPage' => $perPage,
                'totalPages' => $totalPages,
                'totalItems' => $totalMatches
            ],
            'flashMessages' => $this->getAllFlashes()
        ]);
    }
    
    /**
     * Отображает детальную страницу матча
     * 
     * @param int $id ID матча
     */
    public function show($id)
    {
        // Преобразуем ID в число
        $id = (int)$id;
        
        // Получаем данные матча
        $match = $this->matchesRepository->getMatchById($id);
        
        if (!$match) {
            // Если матч не найден, показываем 404 ошибку
            $this->view->setLayout('error');
            echo $this->view->render('errors/404', [
                'pageTitle' => 'Матч не найден',
                'message' => 'Запрашиваемый матч не найден.'
            ]);
            return;
        }
        
        // Получаем коэффициенты для матча
        $odds = $this->oddsRepository->getOddsByMatchId($id);
        
        // Получаем AI-прогноз (если есть)
        $aiPrediction = null;
        try {
            // Пытаемся инициализировать репозиторий для AI-прогнозов
            $extAiExplanationsRepository = new ExtAiExplanationsRepository($this->db);
            $aiPrediction = $extAiExplanationsRepository->getExplanationByMatchId($id);
        } catch (Exception $e) {
            // Если таблица не существует или произошла ошибка, используем заглушку
            $aiPrediction = [
                'explanation_text' => 'AI-ассистент "Палыч" анализирует этот матч. Прогноз будет доступен позже.',
                'created_at' => date('Y-m-d H:i:s')
            ];
        }
        
        // Проверяем, является ли матч избранным для текущего пользователя
        if ($this->isLoggedIn()) {
            $match['is_favorite'] = $this->userFavoritesRepository->isFavorite($_SESSION['user_id'], $id);
        }
        
        // Отображаем шаблон
        echo $this->view->render('matches/show', [
            'pageTitle' => $match['home_team_name'] . ' - ' . $match['away_team_name'],
            'pageDescription' => 'Информация о матче ' . $match['home_team_name'] . ' - ' . $match['away_team_name'] . ', коэффициенты и прогнозы',
            'match' => $match,
            'odds' => $odds,
            'aiPrediction' => $aiPrediction,
            'flashMessages' => $this->getAllFlashes()
        ]);
    }
    
    /**
     * Обрабатывает AJAX-запрос на добавление/удаление матча из избранного
     */
    public function toggleFavorite()
    {
        // Проверяем, авторизован ли пользователь
        if (!$this->isLoggedIn()) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Необходимо войти в систему',
                'redirect' => $this->config['baseUrl'] . 'login'
            ], 401);
        }
        
        // Проверяем наличие ID матча
        if (!isset($_POST['match_id']) || !is_numeric($_POST['match_id'])) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Некорректный ID матча'
            ], 400);
        }
        
        $matchId = (int)$_POST['match_id'];
        $userId = $_SESSION['user_id'];
        
        // Проверяем существование матча
        $match = $this->matchesRepository->getMatchById($matchId);
        if (!$match) {
            $this->jsonResponse([
                'success' => false,
                'message' => 'Матч не найден'
            ], 404);
        }
        
        // Добавляем/удаляем из избранного
        $result = $this->userFavoritesRepository->toggleFavorite($userId, $matchId);
        
        // Возвращаем результат
        $this->jsonResponse([
            'success' => true,
            'action' => $result['action'],
            'message' => $result['message']
        ]);
    }
}