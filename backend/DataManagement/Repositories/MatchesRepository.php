<?php
declare(strict_types=1);

namespace PrognozAi\DataManagement\Repositories;

use PDO;
use RuntimeException;

/**
 * Репозиторий для работы с матчами (таблица matches).
 */
class MatchesRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Ищет существующий матч по уникальной комбинации ключей:
     * Для тенниса: league_id, home_player_id, away_player_id, match_datetime_utc
     * Для футбола (и др.): league_id, home_club_id, away_club_id, match_datetime_utc
     * Возвращает ассоциативный массив данных или null, если не найден.
     */
    public function findExisting(
        int $leagueId,
        ?int $homeClubId,
        ?int $awayClubId,
        string $datetimeUtc,
        ?int $homePlayerId = null,
        ?int $awayPlayerId = null
    ): ?array {
        if ($homePlayerId !== null && $awayPlayerId !== null) {
            // Теннис: ищем по игрокам
            $sql = "SELECT * FROM matches
                WHERE league_id = :league_id
                  AND home_player_id = :home_player_id
                  AND away_player_id = :away_player_id
                  AND match_datetime_utc = :match_datetime_utc
                LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':league_id', $leagueId, PDO::PARAM_INT);
            $stmt->bindValue(':home_player_id', $homePlayerId, PDO::PARAM_INT);
            $stmt->bindValue(':away_player_id', $awayPlayerId, PDO::PARAM_INT);
            $stmt->bindValue(':match_datetime_utc', $datetimeUtc, PDO::PARAM_STR);
        } else {
            // Футбол и др.: ищем по клубам, учитываем возможные NULL
            $sql = "SELECT * FROM matches
                WHERE league_id = :league_id
                  AND " . ($homeClubId === null ? "home_club_id IS NULL" : "home_club_id = :home_club_id") . "
                  AND " . ($awayClubId === null ? "away_club_id IS NULL" : "away_club_id = :away_club_id") . "
                  AND match_datetime_utc = :match_datetime_utc
                LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':league_id', $leagueId, PDO::PARAM_INT);
            if ($homeClubId !== null) {
                $stmt->bindValue(':home_club_id', $homeClubId, PDO::PARAM_INT);
            }
            if ($awayClubId !== null) {
                $stmt->bindValue(':away_club_id', $awayClubId, PDO::PARAM_INT);
            }
            $stmt->bindValue(':match_datetime_utc', $datetimeUtc, PDO::PARAM_STR);
        }
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row === false ? null : $row;
    }

    /**
     * Вставляет новый матч в таблицу matches.
     * Возвращает ID созданного матча.
     * @throws RuntimeException при ошибке вставки.
     */
    public function insertMatch(
        int $sportId,
        int $leagueId,
        ?int $seasonId,
        string $datetimeUtc,
        ?int $homeClubId,
        ?int $awayClubId,
        int $primarySourceId,
        ?int $homePlayerId = null,
        ?int $awayPlayerId = null,
        ?int $parserInputId = null
    ): int {
        $sql = "INSERT INTO matches (
            sport_id, league_id, season_id, match_datetime_utc, home_club_id, away_club_id,
            home_player_id, away_player_id, venue_id, referee_id, status_key,
            primary_source_id, parser_input_id, created_at, updated_at
        ) VALUES (
            :sport_id, :league_id, :season_id, :match_datetime_utc, :home_club_id, :away_club_id,
            :home_player_id, :away_player_id, NULL, NULL, :status_key,
            :primary_source_id, :parser_input_id, NOW(), NOW()
        )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':sport_id', $sportId, PDO::PARAM_INT);
        $stmt->bindValue(':league_id', $leagueId, PDO::PARAM_INT);
        $stmt->bindValue(':season_id', $seasonId, $seasonId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':match_datetime_utc', $datetimeUtc, PDO::PARAM_STR);
        $stmt->bindValue(':home_club_id', $homeClubId, $homeClubId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':away_club_id', $awayClubId, $awayClubId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':home_player_id', $homePlayerId, $homePlayerId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':away_player_id', $awayPlayerId, $awayPlayerId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
        $stmt->bindValue(':status_key', 'scheduled', PDO::PARAM_STR);
        $stmt->bindValue(':primary_source_id', $primarySourceId, PDO::PARAM_INT);
        $stmt->bindValue(':parser_input_id', $parserInputId, $parserInputId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);

        if (!$stmt->execute()) {
            throw new RuntimeException("Ошибка при вставке матча: " . implode(' | ', $stmt->errorInfo()));
        }
        return (int)$this->pdo->lastInsertId();
    }
}
