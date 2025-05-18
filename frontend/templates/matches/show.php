### 4.4. Шаблон детальной страницы матча

**Файл: prognozai/frontend/templates/matches/show.php**

<section class="match-detail">
    <div class="container">
        <div class="breadcrumbs">
            <a href="<?= $baseUrl ?>">Главная</a> &raquo;
            <a href="<?= $baseUrl ?>matches">Матчи</a> &raquo;
            <span><?= htmlspecialchars($match['home_team_name']) ?> vs <?= htmlspecialchars($match['away_team_name']) ?></span>
        </div>
        
        <div class="match-header">
            <div class="match-title">
                <h1><?= htmlspecialchars($match['home_team_name']) ?> vs <?= htmlspecialchars($match['away_team_name']) ?></h1>
                <div class="match-meta">
                    <div class="match-league"><?= htmlspecialchars($match['league_name']) ?></div>
                    <div class="match-date"><?= date('d.m.Y H:i', strtotime($match['match_datetime'])) ?></div>
                    <div class="match-status <?= $match['status'] ?>">
                        <?php
                        $statusMap = [
                            'scheduled' => 'Предстоящий',
                            'in_progress' => 'В прогрессе',
                            'finished' => 'Завершен',
                            'postponed' => 'Отложен',
                            'cancelled' => 'Отменен'
                        ];
                        echo $statusMap[$match['status']] ?? $match['status'];
                        ?>
                    </div>
                </div>
                
                <?php if ($isLoggedIn): ?>
                    <button class="btn-favorite large <?= isset($match['is_favorite']) && $match['is_favorite'] ? 'active' : '' ?>" 
                            data-match-id="<?= $match['match_id'] ?>"
                            title="<?= isset($match['is_favorite']) && $match['is_favorite'] ? 'Удалить из избранного' : 'Добавить в избранное' ?>">
                        <span class="fav-icon">★</span>
                        <span class="fav-text"><?= isset($match['is_favorite']) && $match['is_favorite'] ? 'В избранном' : 'В избранное' ?></span>
                    </button>
                <?php endif; ?>
            </div>
            
            <div class="teams-wrapper">
                <div class="team home-team">
                    <div class="team-logo">
                        <div class="team-logo-placeholder">
                            <?= strtoupper(substr($match['home_team_name'], 0, 1)) ?>
                        </div>
                    </div>
                    <div class="team-name"><?= htmlspecialchars($match['home_team_name']) ?></div>
                </div>
                
                <div class="match-vs">
                    <span class="vs-text">VS</span>
                </div>
                
                <div class="team away-team">
                    <div class="team-logo">
                        <div class="team-logo-placeholder">
                            <?= strtoupper(substr($match['away_team_name'], 0, 1)) ?>
                        </div>
                    </div>
                    <div class="team-name"><?= htmlspecialchars($match['away_team_name']) ?></div>
                </div>
            </div>
        </div>
        
        <div class="match-info-tabs">
            <div class="tabs">
                <button class="tab-button active" data-tab="odds">Коэффициенты</button>
                <button class="tab-button" data-tab="prediction">AI-прогноз</button>
                <button class="tab-button" data-tab="stats">Статистика</button>
            </div>
            
            <div class="tab-content">
                <!-- Вкладка с коэффициентами -->
                <div class="tab-pane active" id="odds-tab">
                    <div class="odds-section">
                        <h2>Коэффициенты</h2>
                        
                        <?php if (empty($odds)): ?>
                            <div class="no-odds">
                                <p>Коэффициенты для этого матча недоступны.</p>
                            </div>
                        <?php else: ?>
                            <div class="odds-categories">
                                <!-- Основные исходы (1X2) -->
                                <div class="odds-category">
                                    <h3>Основные исходы</h3>
                                    <div class="odds-grid">
                                        <?php
                                        $mainMarketKeys = ['1', 'X', '2'];
                                        $mainMarkets = array_filter($odds, function($odd) use ($mainMarketKeys) {
                                            return in_array($odd['market_key'], $mainMarketKeys);
                                        });
                                        
                                        foreach ($mainMarkets as $odd):
                                        ?>
                                            <div class="odd-item">
                                                <div class="odd-name"><?= htmlspecialchars($odd['market_name']) ?></div>
                                                <div class="odd-value"><?= htmlspecialchars($odd['odd_value']) ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <!-- Двойные шансы -->
                                <div class="odds-category">
                                    <h3>Двойной шанс</h3>
                                    <div class="odds-grid">
                                        <?php
                                        $doubleChanceKeys = ['1X', '12', 'X2'];
                                        $doubleChanceMarkets = array_filter($odds, function($odd) use ($doubleChanceKeys) {
                                            return in_array($odd['market_key'], $doubleChanceKeys);
                                        });
                                        
                                        foreach ($doubleChanceMarkets as $odd):
                                        ?>
                                            <div class="odd-item">
                                                <div class="odd-name"><?= htmlspecialchars($odd['market_name']) ?></div>
                                                <div class="odd-value"><?= htmlspecialchars($odd['odd_value']) ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <!-- Тоталы -->
                                <div class="odds-category">
                                    <h3>Тоталы</h3>
                                    <div class="odds-grid">
                                        <?php
                                        $totalMarkets = array_filter($odds, function($odd) {
                                            return strpos($odd['market_key'], 'TotalOver') !== false || 
                                                   strpos($odd['market_key'], 'TotalUnder') !== false;
                                        });
                                        
                                        foreach ($totalMarkets as $odd):
                                        ?>
                                            <div class="odd-item">
                                                <div class="odd-name"><?= htmlspecialchars($odd['market_name']) ?></div>
                                                <div class="odd-value"><?= htmlspecialchars($odd['odd_value']) ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                
                                <!-- Форы -->
                                <div class="odds-category">
                                    <h3>Форы</h3>
                                    <div class="odds-grid">
                                        <?php
                                        $handicapMarkets = array_filter($odds, function($odd) {
                                            return strpos($odd['market_key'], 'Handicap') !== false;
                                        });
                                        
                                        foreach ($handicapMarkets as $odd):
                                        ?>
                                            <div class="odd-item">
                                                <div class="odd-name"><?= htmlspecialchars($odd['market_name']) ?></div>
                                                <div class="odd-value"><?= htmlspecialchars($odd['odd_value']) ?></div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Вкладка с AI-прогнозом -->
                <div class="tab-pane" id="prediction-tab">
                    <div class="ai-prediction">
                        <h2>AI-прогноз</h2>
                        
                        <div class="ai-prediction-container">
                            <div class="ai-avatar">
                                <div class="ai-avatar-icon">🤖</div>
                                <div class="ai-avatar-name">Палыч</div>
                            </div>
                            
                            <div class="ai-prediction-content">
                                <div class="ai-prediction-time">
                                    <?= date('d.m.Y H:i', strtotime($aiPrediction['created_at'])) ?>
                                </div>
                                
                                <div class="ai-prediction-text">
                                    <?= nl2br(htmlspecialchars($aiPrediction['explanation_text'])) ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Вкладка со статистикой -->
                <div class="tab-pane" id="stats-tab">
                    <div class="stats-section">
                        <h2>Статистика</h2>
                        
                        <div class="stats-placeholder">
                            <p>Статистика по данному матчу будет доступна позже.</p>
                            <p>В полной версии здесь будет представлена детальная статистика команд, история личных встреч и другие аналитические данные.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// Скрипт для обработки вкладок
document.addEventListener('DOMContentLoaded', function() {
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Убираем активное состояние со всех кнопок и вкладок
            tabButtons.forEach(btn => btn.classList.remove('active'));
            tabPanes.forEach(pane => pane.classList.remove('active'));
            
            // Добавляем активное состояние текущей кнопке
            button.classList.add('active');
            
            // Показываем соответствующую вкладку
            const tabId = button.getAttribute('data-tab') + '-tab';
            document.getElementById(tabId).classList.add('active');
        });
    });
    
    // Обработка добавления в избранное
    const favoriteButton = document.querySelector('.btn-favorite');
    
    if (favoriteButton) {
        favoriteButton.addEventListener('click', function() {
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
                        this.querySelector('.fav-text').textContent = 'В избранном';
                    } else {
                        this.classList.remove('active');
                        this.title = 'Добавить в избранное';
                        this.querySelector('.fav-text').textContent = 'В избранное';
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
    }
});
</script>