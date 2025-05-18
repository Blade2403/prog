**Файл: prognozai/frontend/templates/user/profile.php**

```php
<section class="profile-section">
    <div class="container">
        <div class="section-header">
            <h1>Профиль</h1>
        </div>
        
        <div class="profile-container">
            <div class="profile-sidebar">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <div class="profile-avatar-placeholder">
                            <?= strtoupper(substr($user['username'], 0, 1)) ?>
                        </div>
                    </div>
                    
                    <div class="profile-info">
                        <h2 class="profile-name"><?= htmlspecialchars($user['username']) ?></h2>
                        <div class="profile-meta">
                            <div class="profile-meta-item">
                                <span class="meta-label">Email:</span>
                                <span class="meta-value"><?= htmlspecialchars($user['email']) ?></span>
                            </div>
                            <div class="profile-meta-item">
                                <span class="meta-label">Дата регистрации:</span>
                                <span class="meta-value"><?= date('d.m.Y', strtotime($user['created_at'])) ?></span>
                            </div>
                            <div class="profile-meta-item">
                                <span class="meta-label">Роль:</span>
                                <span class="meta-value"><?= htmlspecialchars($user['role_key'] ?? 'Пользователь') ?></span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-actions">
                        <a href="<?= $baseUrl ?>logout" class="btn btn-secondary">Выйти</a>
                    </div>
                </div>
            </div>
            
            <div class="profile-content">
                <div class="profile-section-title">
                    <h2>Избранные матчи</h2>
                </div>
                
                <?php if (empty($favoriteMatches)): ?>
                    <div class="no-favorites">
                        <p>У вас пока нет избранных матчей.</p>
                        <a href="<?= $baseUrl ?>matches" class="btn btn-primary">Перейти к матчам</a>
                    </div>
                <?php else: ?>
                    <div class="favorites-list">
                        <?php foreach ($favoriteMatches as $match): ?>
                            <div class="favorite-match">
                                <div class="favorite-match-info">
                                    <div class="favorite-match-league">
                                        <?= htmlspecialchars($match['league_name']) ?>
                                    </div>
                                    
                                    <div class="favorite-match-teams">
                                        <span class="home-team"><?= htmlspecialchars($match['home_team_name']) ?></span>
                                        <span class="vs">vs</span>
                                        <span class="away-team"><?= htmlspecialchars($match['away_team_name']) ?></span>
                                    </div>
                                    
                                    <div class="favorite-match-date">
                                        <?= date('d.m.Y H:i', strtotime($match['match_datetime'])) ?>
                                    </div>
                                </div>
                                
                                <div class="favorite-match-actions">
                                    <a href="<?= $baseUrl ?>matches/<?= $match['match_id'] ?>" class="btn btn-secondary btn-sm">Подробнее</a>
                                    <button class="btn-remove-favorite" data-match-id="<?= $match['match_id'] ?>" title="Удалить из избранного">✕</button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
// Обработка удаления из избранного
document.addEventListener('DOMContentLoaded', function() {
    const removeButtons = document.querySelectorAll('.btn-remove-favorite');
    
    removeButtons.forEach(button => {
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
                    // Удаляем элемент из DOM
                    const favoriteMatch = this.closest('.favorite-match');
                    favoriteMatch.remove();
                    
                    // Если больше нет избранных матчей, показываем сообщение
                    if (document.querySelectorAll('.favorite-match').length === 0) {
                        const favoritesList = document.querySelector('.favorites-list');
                        favoritesList.innerHTML = `
                            <div class="no-favorites">
                                <p>У вас пока нет избранных матчей.</p>
                                <a href="<?= $baseUrl ?>matches" class="btn btn-primary">Перейти к матчам</a>
                            </div>
                        `;
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
                } else {
                    alert(data.message || 'Произошла ошибка');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Произошла ошибка при удалении из избранного');
            });
        });
    });
});
</script>