<?php
declare(strict_types=1);

namespace PrognozAi\DataManagement\Repositories;

use PDO;
use RuntimeException;

/**
 * Репозиторий для клубов (clubs)
 */
class ClubsRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Найти club_id по sport_id, country_id и названию клуба (en/ru), или создать новый клуб
     * @param int $sportId
     * @param string $clubName
     * @param int|null $countryId
     * @return int
     */
    public function findOrCreateByName(int $sportId, string $clubName, ?int $countryId = null): int
    {
        if ($countryId === null) {
            $sql = "SELECT club_id FROM clubs WHERE sport_id = :sport_id AND country_id IS NULL AND (name_en = :name OR name_ru = :name) LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':sport_id' => $sportId,
                ':name'     => $clubName,
            ]);
        } else {
            $sql = "SELECT club_id FROM clubs WHERE sport_id = :sport_id AND country_id = :country_id AND (name_en = :name OR name_ru = :name) LIMIT 1";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':sport_id'   => $sportId,
                ':country_id' => $countryId,
                ':name'       => $clubName,
            ]);
        }
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return (int)$row['club_id'];
        }

        $sql = "INSERT INTO clubs (sport_id, country_id, name_en, name_ru)
                VALUES (:sport_id, :country_id, :name_en, :name_ru)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':sport_id'   => $sportId,
            ':country_id' => $countryId,
            ':name_en'    => $clubName,
            ':name_ru'    => $clubName,
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}