### 4.3. Шаблон списка матчей

**Файл: prognozai/frontend/templates/matches/index.php**

<section class="matches-section">
    <div class="container">
        <div class="section-header">
            <h1>Матчи</h1>
        </div>
        
        <div class="matches-filters">
            <form action="<?= $baseUrl ?>matches" method="GET" class="filter-form">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="league">Лига:</label>
                        <select name="league" id="league" class="form-control">
                            <option value="">Все лиги</option>
                            <?php foreach ($leagues as $league): ?>
                                <option value="<?= $league['league_id'] ?>" <?= $filters['leagueId'] == $league['league_id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($league['name_ru']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label for="date">Дата:</label>
                        <input type="date" name="date" id="date" class="form-control" value="<?= $filters['date'] ?>">
                    </div>
                    
                    <div class="filter-group">
                        <label for="sport">Вид спорта:</label>
                        <select name="sport" id="sport" class="form-control">
                            <option value="1" <?= $filters['sportId'] == 1 ? 'selected' : '' ?>>Футбол</option>
                            <option value="2" <?= $filters['sportId'] == 2 ? 'selected' : '' ?>>Теннис</option>
                        </select>
                    </div>
                    
                    <div class="filter-actions">
                        <button type="submit" class="btn btn-primary">Применить</button>
                        <a href="<?= $baseUrl ?>matches" class="btn btn-secondary">Сбросить</a>
                    </div>
                </div>
            </form>
        </div>
        
        <div class="matches-list">
            <?php if (empty($matches)): ?>
                <div class="no-matches">
                    <p>Матчи не найдены. Попробуйте изменить параметры фильтрации.</p>
                </div>
            <?php else: ?>
                <?php foreach ($matches as $match): ?>
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
                            <a href="<?= $baseUrl ?>matches/<?= $match['match_id'] ?>" class="btn btn-secondary">Подробнее</a>
                            
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
                
                <?php if ($pagination['totalPages'] > 1): ?>
                    <div class="pagination">
                        <?php if ($pagination['page'] > 1): ?>
                            <a href="<?= $baseUrl ?>matches?page=<?= $pagination['page'] - 1 ?>&league=<?= $filters['leagueId'] ?>&date=<?= $filters['date'] ?>&sport=<?= $filters['sportId'] ?>" class="pagination-link pagination-prev">
                                &laquo; Предыдущая
                            </a>
                        <?php endif; ?>
                        
                        <?php 
                        // Определяем диапазон отображаемых страниц
                        $start = max(1, $pagination['page'] - 2);
                        $end = min($pagination['totalPages'], $pagination['page'] + 2);
                        
                        // Отображаем страницы
                        for ($i = $start; $i <= $end; $i++): 
                        ?>
                            <a href="<?= $baseUrl ?>matches?page=<?= $i ?>&league=<?= $filters['leagueId'] ?>&date=<?= $filters['date'] ?>&sport=<?= $filters['sportId'] ?>" 
                               class="pagination-link <?= $i == $pagination['page'] ? 'active' : '' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                        
                        <?php if ($pagination['page'] < $pagination['totalPages']): ?>
                            <a href="<?= $baseUrl ?>matches?page=<?= $pagination['page'] + 1 ?>&league=<?= $filters['leagueId'] ?>&date=<?= $filters['date'] ?>&sport=<?= $filters['sportId'] ?>" class="pagination-link pagination-next">
                                Следующая &raquo;
                            </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
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