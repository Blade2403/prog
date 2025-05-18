<?php
declare(strict_types=1);

namespace PrognozAi\DataManagement\Repositories;

use PDO;
use RuntimeException;

/**
 * Репозиторий для сезонов (seasons)
 */
class SeasonsRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Найти или создать сезон по имени (например "2023/2024"), league_id, с вычислением дат если не заданы
     * @param int $leagueId
     * @param string $seasonName
     * @param string|null $startDate
     * @param string|null $endDate
     * @param bool $isCurrentSeason
     * @return int
     */
    public function findOrCreateByName(
        int $leagueId,
        string $seasonName,
        ?string $startDate = null,
        ?string $endDate = null,
        bool $isCurrentSeason = false
    ): int
    {
        // Поиск
        $sql = "SELECT season_id FROM seasons WHERE league_id = :league_id AND season_name = :season_name LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':league_id'   => $leagueId,
            ':season_name' => $seasonName,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return (int)$row['season_id'];
        }

        // Эвристика для дат если не заданы
        if ($startDate === null || $endDate === null) {
            if (preg_match('/^(\d{4})\/(\d{4})$/', $seasonName, $m)) {
                // "2023/2024"
                $startDate = "{$m[1]}-07-01";
                $endDate = "{$m[2]}-06-30";
            } elseif (preg_match('/^(\d{4})$/', $seasonName, $m)) {
                // "2024"
                $startDate = "{$m[1]}-01-01";
                $endDate = "{$m[1]}-12-31";
            }
        }

        $sql = "INSERT INTO seasons (league_id, season_name, start_date, end_date, is_current_season)
                VALUES (:league_id, :season_name, :start_date, :end_date, :is_current_season)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':league_id'         => $leagueId,
            ':season_name'       => $seasonName,
            ':start_date'        => $startDate,
            ':end_date'          => $endDate,
            ':is_current_season' => $isCurrentSeason ? 1 : 0,
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}