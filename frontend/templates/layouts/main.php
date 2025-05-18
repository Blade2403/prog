## 4. Шаблоны для фронтенда

### 4.1. Базовый макет

**Файл: prognozai/frontend/templates/layouts/main.php**

<!DOCTYPE html>
<html lang="<?= $_SESSION['language'] ?? 'ru' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - ' . htmlspecialchars($siteName) : htmlspecialchars($siteName) ?></title>
    <meta name="description" content="<?= htmlspecialchars($pageDescription ?? $siteDescription) ?>">
    <link rel="stylesheet" href="<?= $baseUrl ?>static/css/main.css">
    <?php if (isset($extraCss) && is_array($extraCss)): ?>
        <?php foreach ($extraCss as $css): ?>
            <link rel="stylesheet" href="<?= $baseUrl . $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-wrapper">
                <div class="logo">
                    <a href="<?= $baseUrl ?>">
                        <span class="logo-text">PrognozAi.ru</span>
                    </a>
                </div>
                
                <nav class="main-nav">
                    <ul class="nav-list">
                        <li class="nav-item"><a href="<?= $baseUrl ?>" class="nav-link">Главная</a></li>
                        <li class="nav-item"><a href="<?= $baseUrl ?>matches" class="nav-link">Матчи</a></li>
                        <li class="nav-item"><a href="<?= $baseUrl ?>faq" class="nav-link">FAQ</a></li>
                        <li class="nav-item"><a href="<?= $baseUrl ?>community" class="nav-link">Сообщество</a></li>
                    </ul>
                </nav>
                
                <div class="user-menu">
                    <?php if ($isLoggedIn): ?>
                        <div class="dropdown">
                            <button class="dropdown-toggle">
                                <?= htmlspecialchars($currentUser['username']) ?>
                                <span class="dropdown-icon">▼</span>
                            </button>
                            <div class="dropdown-menu">
                                <a href="<?= $baseUrl ?>profile" class="dropdown-item">Профиль</a>
                                <a href="<?= $baseUrl ?>logout" class="dropdown-item">Выйти</a>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="auth-buttons">
                            <a href="<?= $baseUrl ?>login" class="btn btn-secondary">Войти</a>
                            <a href="<?= $baseUrl ?>register" class="btn btn-primary">Регистрация</a>
                        </div>
                    <?php endif; ?>
                    
                    <div class="language-selector">
                        <?php foreach ($supportedLanguages as $code => $name): ?>
                            <a href="<?= $baseUrl ?>language/<?= $code ?>" class="lang-link <?= $language === $code ? 'active' : '' ?>"><?= strtoupper($code) ?></a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <main class="site-content">
        <?php if (!empty($flashMessages)): ?>
            <div class="flash-messages container">
                <?php foreach ($flashMessages as $type => $message): ?>
                    <div class="alert alert-<?= $type ?>">
                        <?= htmlspecialchars($message) ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?= $content ?>
    </main>
    
    <footer class="site-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-info">
                    <div class="footer-logo">
                        <span class="logo-text">PrognozAi.ru</span>
                    </div>
                    <p class="footer-copyright">&copy; <?= date('Y') ?> PrognozAi.ru - Интеллектуальная онлайн-платформа для анализа спортивных событий</p>
                </div>
                
                <div class="footer-links">
                    <div class="footer-nav">
                        <h4>Навигация</h4>
                        <ul>
                            <li><a href="<?= $baseUrl ?>">Главная</a></li>
                            <li><a href="<?= $baseUrl ?>matches">Матчи</a></li>
                            <li><a href="<?= $baseUrl ?>faq">FAQ</a></li>
                            <li><a href="<?= $baseUrl ?>community">Сообщество</a></li>
                        </ul>
                    </div>
                    
                    <div class="footer-nav">
                        <h4>Аккаунт</h4>
                        <ul>
                            <?php if ($isLoggedIn): ?>
                                <li><a href="<?= $baseUrl ?>profile">Профиль</a></li>
                                <li><a href="<?= $baseUrl ?>logout">Выйти</a></li>
                            <?php else: ?>
                                <li><a href="<?= $baseUrl ?>login">Войти</a></li>
                                <li><a href="<?= $baseUrl ?>register">Регистрация</a></li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </footer>
    
    <script src="<?= $baseUrl ?>static/js/main.js"></script>
    <?php if (isset($extraJs) && is_array($extraJs)): ?>
        <?php foreach ($extraJs as $js): ?>
            <script src="<?= $baseUrl . $js ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>