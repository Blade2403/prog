<?php
declare(strict_types=1);

namespace PrognozAi\DataManagement\Repositories;

use PDO;
use RuntimeException;

/**
 * Репозиторий для справочника видов спорта (sports)
 */
class SportsRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Найти sport_id по ключу или создать новую запись
     * @param string $sportKey
     * @param string $nameEn
     * @param string $nameRu
     * @param string|null $logoUrl
     * @return int
     */
    public function findOrCreateByKey(string $sportKey, string $nameEn, string $nameRu, ?string $logoUrl = null): int
    {
        // Сначала ищем по sport_key
        $sql = "SELECT sport_id FROM sports WHERE sport_key = :sport_key";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':sport_key' => $sportKey]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return (int)$row['sport_id'];
        }
        // Вставляем
        $sql = "INSERT INTO sports (name_en, name_ru, sport_key, logo_url) VALUES (:name_en, :name_ru, :sport_key, :logo_url)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':name_en' => $nameEn,
            ':name_ru' => $nameRu,
            ':sport_key' => $sportKey,
            ':logo_url' => $logoUrl,
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}