### 4.2. Шаблон главной страницы

**Файл: prognozai/frontend/templates/home.php**

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <h1>PrognozAi.ru</h1>
            <h2>Интеллектуальная платформа для анализа спортивных событий</h2>
            <p>Получайте прогнозы на основе искусственного интеллекта, анализируйте статистику и делайте обоснованные ставки</p>
            <div class="hero-buttons">
                <a href="<?= $baseUrl ?>matches" class="btn btn-primary">Смотреть матчи</a>
                <?php if (!$isLoggedIn): ?>
                    <a href="<?= $baseUrl ?>register" class="btn btn-secondary">Зарегистрироваться</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<section class="upcoming-matches">
    <div class="container">
        <div class="section-header">
            <h2>Ближайшие матчи</h2>
            <a href="<?= $baseUrl ?>matches" class="view-all">Посмотреть все</a>
        </div>
        
        <div class="matches-grid">
            <?php if (empty($upcomingMatches)): ?>
                <div class="no-matches">
                    <p>Нет предстоящих матчей.</p>
                </div>
            <?php else: ?>
                <?php foreach ($upcomingMatches as $match): ?>
                    <div class="match-card">
                        <div class="match-header">
                            <div class="match-league"><?= htmlspecialchars($match['league_name']) ?></div>
                            <div class="match-date"><?= date('d.m.Y H:i', strtotime($match['match_datetime'])) ?></div>
                        </div>
                        
                        <div class="match-teams">
                            <div class="team home-team">
                                <div class="team-name"><?= htmlspecialchars($match['home_team_name']) ?></div>
                            </div>
                            
                            <div class="match-vs">vs</div>
                            
                            <div class="team away-team">
                                <div class="team-name"><?= htmlspecialchars($match['away_team_name']) ?></div>
                            </div>
                        </div>
                        
                        <div class="match-odds">
                            <?php if (!empty($match['odds'])): ?>
                                <div class="odds-row">
                                    <?php foreach ($match['odds'] as $odd): ?>
                                        <div class="odd-item">
                                            <div class="odd-name"><?= htmlspecialchars($odd['market_name']) ?></div>
                                            <div class="odd-value"><?= htmlspecialchars($odd['odd_value']) ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="no-odds">Коэффициенты недоступны</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="match-actions">
                            <a href="<?= $baseUrl ?>matches/<?= $match['match_id'] ?>" class="btn btn-secondary btn-sm">Подробнее</a>
                            
                            <?php if ($isLoggedIn): ?>
                                <button class="btn-favorite <?= isset($match['is_favorite']) && $match['is_favorite'] ? 'active' : '' ?>" 
                                        data-match-id="<?= $match['match_id'] ?>"
                                        title="<?= isset($match['is_favorite']) && $match['is_favorite'] ? 'Удалить из избранного' : 'Добавить в избранное' ?>">
                                    <span class="fav-icon">★</span>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="features">
    <div class="container">
        <h2>Наши преимущества</h2>
        
        <div class="features-grid">
            <div class="feature-card">
                <div class="feature-icon">🤖</div>
                <h3>AI-прогнозы</h3>
                <p>Используем искусственный интеллект для анализа данных и создания точных прогнозов на спортивные события</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">📊</div>
                <h3>Детальная статистика</h3>
                <p>Предоставляем подробную статистику матчей, команд и игроков для глубокого анализа</p>
            </div>
            
            <div class="feature-card">
                <div class="feature-icon">🏆</div>
                <h3>Сообщество экспертов</h3>
                <p>Объединяем опытных аналитиков и бетторов для обмена знаниями и стратегиями</p>
            </div>
        </div>
    </div>
</section>

<section class="cta">
    <div class="container">
        <div class="cta-content">
            <h2>Готовы начать?</h2>
            <p>Присоединяйтесь к нашему сообществу и получайте доступ к аналитике, прогнозам и эксклюзивным материалам</p>
            <?php if (!$isLoggedIn): ?>
                <a href="<?= $baseUrl ?>register" class="btn btn-primary">Зарегистрироваться</a>
            <?php else: ?>
                <a href="<?= $baseUrl ?>matches" class="btn btn-primary">Смотреть матчи</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<script>
// Скрипт для обработки добавления в избранное
document.addEventListener('DOMContentLoaded', function() {
    const favoriteButtons = document.querySelectorAll('.btn-favorite');
    
    favoriteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const matchId = this.getAttribute('data-match-id');
            
            fetch('<?= $baseUrl ?>matches/favorite', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'match_id=' + matchId
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    if (data.action === 'added') {
                        this.classList.add('active');
                        this.title = 'Удалить из избранного';
                    } else {
                        this.classList.remove('active');
                        this.title = 'Добавить в избранное';
                    }
                    
                    // Показываем всплывающее сообщение
                    const flashContainer = document.querySelector('.flash-messages');
                    if (flashContainer) {
                        const alert = document.createElement('div');
                        alert.className = 'alert alert-success';
                        alert.textContent = data.message;
                        flashContainer.appendChild(alert);
                        
                        // Удаляем сообщение через 3 секунды
                        setTimeout(() => {
                            alert.remove();
                        }, 3000);
                    }
                } else if (data.redirect) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при обновлении избранного');
            });
        });
    });
});
</script>