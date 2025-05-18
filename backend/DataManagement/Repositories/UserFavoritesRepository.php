/** 
*3. Репозитории

 *3.1. Репозиторий для избранных матчей пользователя

*Файл: prognozai/backend/DataManagement/Repositories/UserFavoritesRepository.php*/

<?php
/**
 * Репозиторий для работы с избранными матчами пользователя
 */
class UserFavoritesRepository
{
    /**
     * Соединение с базой данных
     * 
     * @var PDO
     */
    protected $db;
    
    /**
     * Конструктор
     * 
     * @param PDO $db Соединение с базой данных
     */
    public function __construct($db)
    {
        $this->db = $db;
    }
    
    /**
     * Проверяет, добавлен ли матч в избранное пользователя
     * 
     * @param int $userId ID пользователя
     * @param int $matchId ID матча
     * @return bool true, если матч в избранном
     */
    public function isFavorite($userId, $matchId)
    {
        $stmt = $this->db->prepare("
            SELECT 1 FROM user_favorite_matches 
            WHERE user_id = ? AND match_id = ?
        ");
        
        $stmt->execute([$userId, $matchId]);
        
        return $stmt->fetchColumn() ? true : false;
    }
    
    /**
     * Добавляет или удаляет матч из избранного
     * 
     * @param int $userId ID пользователя
     * @param int $matchId ID матча
     * @return array Результат операции
     */
    public function toggleFavorite($userId, $matchId)
    {
        // Проверяем, есть ли уже этот матч в избранном
        if ($this->isFavorite($userId, $matchId)) {
            // Удаляем из избранного
            $stmt = $this->db->prepare("
                DELETE FROM user_favorite_matches 
                WHERE user_id = ? AND match_id = ?
            ");
            
            $stmt->execute([$userId, $matchId]);
            
            return [
                'success' => true,
                'action' => 'removed',
                'message' => 'Матч удален из избранного'
            ];
        } else {
            // Добавляем в избранное
            $stmt = $this->db->prepare("
                INSERT INTO user_favorite_matches (user_id, match_id, created_at) 
                VALUES (?, ?, NOW())
            ");
            
            $stmt->execute([$userId, $matchId]);
            
            return [
                'success' => true,
                'action' => 'added',
                'message' => 'Матч добавлен в избранное'
            ];
        }
    }
    
    /**
     * Получает список избранных матчей пользователя
     * 
     * @param int $userId ID пользователя
     * @return array Массив матчей
     */
    public function getFavoriteMatches($userId)
    {
        $stmt = $this->db->prepare("
            SELECT m.*, 
                   ht.name_ru AS home_team_name, 
                   at.name_ru AS away_team_name,
                   l.name_ru AS league_name,
                   c.name_ru AS country_name,
                   s.name_ru AS sport_name
            FROM user_favorite_matches ufm
            JOIN matches m ON ufm.match_id = m.match_id
            JOIN clubs ht ON m.home_team_id = ht.club_id
            JOIN clubs at ON m.away_team_id = at.club_id
            JOIN leagues l ON m.league_id = l.league_id
            JOIN countries c ON l.country_id = c.country_id
            JOIN sports s ON m.sport_id = s.sport_id
            WHERE ufm.user_id = ?
            ORDER BY m.match_datetime ASC
        ");
        
        $stmt->execute([$userId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Удаляет все избранные матчи пользователя
     * 
     * @param int $userId ID пользователя
     * @return bool Результат операции
     */
    public function removeAllFavorites($userId)
    {
        $stmt = $this->db->prepare("
            DELETE FROM user_favorite_matches 
            WHERE user_id = ?
        ");
        
        return $stmt->execute([$userId]);
    }
}