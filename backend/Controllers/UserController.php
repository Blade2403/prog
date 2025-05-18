### 2.3. Контроллер для пользователей

**Файл: prognozai/backend/Controllers/UserController.php**

<?php
/**
 * Контроллер для работы с пользователями
 */
class UserController extends BaseController
{
    /**
     * Репозиторий для работы с пользователями
     * 
     * @var UsersRepository
     */
    protected $usersRepository;
    
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
        
        // Инициализируем репозиторий пользователей
        $this->usersRepository = new UsersRepository($this->db);
        
        // Инициализируем репозиторий избранных матчей, если пользователь авторизован
        if ($this->isLoggedIn()) {
            $this->userFavoritesRepository = new UserFavoritesRepository($this->db);
        }
    }
    
    /**
     * Отображает форму регистрации
     */
    public function registerForm()
    {
        // Если пользователь уже авторизован, перенаправляем на главную
        if ($this->isLoggedIn()) {
            $this->redirect($this->config['baseUrl']);
        }
        
        // Отображаем форму регистрации
        echo $this->view->render('user/register', [
            'pageTitle' => 'Регистрация',
            'pageDescription' => 'Регистрация нового пользователя на PrognozAi.ru',
            'errors' => $this->getFlash('errors', []),
            'oldInput' => $this->getFlash('oldInput', []),
            'flashMessages' => $this->getAllFlashes()
        ]);
    }
    
    /**
     * Обрабатывает форму регистрации
     */
    public function register()
    {
        // Если пользователь уже авторизован, перенаправляем на главную
        if ($this->isLoggedIn()) {
            $this->redirect($this->config['baseUrl']);
        }
        
        // Получаем данные из формы
        $username = isset($_POST['username']) ? trim($_POST['username']) : '';
        $email = isset($_POST['email']) ? trim($_POST['email']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $confirmPassword = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
        
        // Сохраняем введенные данные для отображения в форме при ошибках
        $oldInput = [
            'username' => $username,
            'email' => $email
        ];
        
        // Валидация данных
        $errors = [];
        
        // Проверяем имя пользователя
        if (empty($username)) {
            $errors['username'] = 'Имя пользователя обязательно';
        } elseif (strlen($username) < 3) {
            $errors['username'] = 'Имя пользователя должно быть не менее 3 символов';
        } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
            $errors['username'] = 'Имя пользователя может содержать только латинские буквы, цифры и символ подчеркивания';
        }
        
        // Проверяем email
        if (empty($email)) {
            $errors['email'] = 'Email обязателен';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Указан некорректный email';
        }
        
        // Проверяем пароль
        if (empty($password)) {
            $errors['password'] = 'Пароль обязателен';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Пароль должен быть не менее 6 символов';
        }
        
        // Проверяем подтверждение пароля
        if ($password !== $confirmPassword) {
            $errors['confirm_password'] = 'Пароли не совпадают';
        }
        
        // Проверяем, не занято ли имя пользователя или email
        if (empty($errors['username']) && $this->usersRepository->findByUsername($username)) {
            $errors['username'] = 'Пользователь с таким именем уже существует';
        }
        
        if (empty($errors['email']) && $this->usersRepository->findByEmail($email)) {
            $errors['email'] = 'Пользователь с таким email уже существует';
        }
        
        // Если есть ошибки, возвращаемся к форме
        if (!empty($errors)) {
            $this->setFlash('errors', $errors);
            $this->setFlash('oldInput', $oldInput);
            $this->redirect($this->config['baseUrl'] . 'register');
            return;
        }
        
        // Регистрируем пользователя
        $userId = $this->usersRepository->createUser($username, $email, $password);
        
        if ($userId) {
            // Успешная регистрация
            $this->setFlash('success', 'Регистрация успешно завершена. Теперь вы можете войти в систему.');
            $this->redirect($this->config['baseUrl'] . 'login');
        } else {
            // Ошибка при регистрации
            $this->setFlash('errors', ['general' => 'Произошла ошибка при регистрации. Пожалуйста, попробуйте позже.']);
            $this->setFlash('oldInput', $oldInput);
            $this->redirect($this->config['baseUrl'] . 'register');
        }
    }
    
    /**
     * Отображает форму входа
     */
    public function loginForm()
    {
        // Если пользователь уже авторизован, перенаправляем на главную
        if ($this->isLoggedIn()) {
            $this->redirect($this->config['baseUrl']);
        }
        
        // Отображаем форму входа
        echo $this->view->render('user/login', [
            'pageTitle' => 'Вход',
            'pageDescription' => 'Вход в личный кабинет на PrognozAi.ru',
            'oldInput' => $this->getFlash('oldInput', []),
            'flashMessages' => $this->getAllFlashes()
        ]);
    }
    
    /**
     * Обрабатывает форму входа
     */
    public function login()
    {
        // Если пользователь уже авторизован, перенаправляем на главную
        if ($this->isLoggedIn()) {
            $this->redirect($this->config['baseUrl']);
        }
        
        // Получаем данные из формы
        $login = isset($_POST['login']) ? trim($_POST['login']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        
        // Сохраняем введенные данные для отображения в форме при ошибках
        $oldInput = ['login' => $login];
        
        // Валидация данных
        if (empty($login) || empty($password)) {
            $this->setFlash('error', 'Необходимо указать логин и пароль');
            $this->setFlash('oldInput', $oldInput);
            $this->redirect($this->config['baseUrl'] . 'login');
            return;
        }
        
        // Аутентификация пользователя
        $user = $this->usersRepository->authenticateUser($login, $password);
        
        if ($user) {
            // Успешная аутентификация
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role_key'] ?? 'user';
            
            // Если было указано, куда перенаправить после входа
            $redirectTo = $_SESSION['redirect_after_login'] ?? $this->config['baseUrl'];
            unset($_SESSION['redirect_after_login']);
            
            // Устанавливаем флеш-сообщение об успешном входе
            $this->setFlash('success', 'Вы успешно вошли в систему');
            
            // Перенаправляем пользователя
            $this->redirect($redirectTo);
        } else {
            // Неудачная аутентификация
            $this->setFlash('error', 'Неверный логин или пароль');
            $this->setFlash('oldInput', $oldInput);
            $this->redirect($this->config['baseUrl'] . 'login');
        }
    }
    
    /**
     * Обрабатывает выход пользователя из системы
     */
    public function logout()
    {
        // Очищаем данные сессии
        $_SESSION = [];
        
        // Если используется cookie для сессии, удаляем его
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        
        // Уничтожаем сессию
        session_destroy();
        
        // Устанавливаем флеш-сообщение об успешном выходе
        session_start();
        $this->setFlash('success', 'Вы успешно вышли из системы');
        
        // Перенаправляем на главную
        $this->redirect($this->config['baseUrl']);
    }
    
    /**
     * Отображает профиль пользователя
     */
    public function profile()
    {
        // Проверяем, авторизован ли пользователь
        $this->requireLogin();
        
        // Получаем данные пользователя
        $user = $this->usersRepository->getUserById($_SESSION['user_id']);
        
        if (!$user) {
            // Если пользователь не найден (например, был удален), выходим из системы
            $this->logout();
            return;
        }
        
        // Получаем избранные матчи пользователя
        $favoriteMatches = $this->userFavoritesRepository->getFavoriteMatches($_SESSION['user_id']);
        
        // Отображаем профиль
        echo $this->view->render('user/profile', [
            'pageTitle' => 'Профиль',
            'pageDescription' => 'Личный кабинет пользователя на PrognozAi.ru',
            'user' => $user,
            'favoriteMatches' => $favoriteMatches,
            'flashMessages' => $this->getAllFlashes()
        ]);
    }
}