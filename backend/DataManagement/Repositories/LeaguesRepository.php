<?php
declare(strict_types=1);

namespace PrognozAi\DataManagement\Repositories;

use PDO;
use RuntimeException;

/**
 * Репозиторий для лиг (leagues)
 */
class LeaguesRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Найти league_id по sport_id, country_id и названию лиги (en/ru), или создать новую лигу
     * @param int $sportId
     * @param int|null $countryId
     * @param string $leagueName
     * @return int
     */
    public function findOrCreateByName(int $sportId, ?int $countryId, string $leagueName): int
    {
        // Поиск: если countryId null, ищем по IS NULL
        if ($countryId === null) {
            $sql = "SELECT league_id FROM leagues WHERE sport_id = :sport_id AND country_id IS NULL AND (name_en = :name OR name_ru = :name) LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':sport_id' => $sportId,
                ':name'     => $leagueName,
            ]);
        } else {
            $sql = "SELECT league_id FROM leagues WHERE sport_id = :sport_id AND country_id = :country_id AND (name_en = :name OR name_ru = :name) LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':sport_id'  => $sportId,
                ':country_id'=> $countryId,
                ':name'      => $leagueName,
            ]);
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return (int)$row['league_id'];
        }

        // Вставка
        $sql = "INSERT INTO leagues (sport_id, country_id, name_en, name_ru)
                VALUES (:sport_id, :country_id, :name_en, :name_ru)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':sport_id'   => $sportId,
            ':country_id' => $countryId,
            ':name_en'    => $leagueName,
            ':name_ru'    => $leagueName,
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}