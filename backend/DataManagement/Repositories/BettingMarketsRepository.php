<?php
// Файл: backend/DataManagement/Repositories/BettingMarketsRepository.php
// Назначение: Репозиторий для работы с таблицей betting_markets
declare(strict_types=1);

namespace PrognozAi\DataManagement\Repositories;

use PDO;
use RuntimeException;

class BettingMarketsRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Поиск или создание рынка по market_key.
     * Возвращает market_id.
     */
    public function findOrCreateByKey(
        int $sportId,
        string $marketKey,
        string $nameEn,
        string $nameRu,
        ?string $marketCategoryNameEn = null,
        ?string $marketCategoryNameRu = null
    ): int {
        $sqlSelect = "SELECT market_id FROM betting_markets WHERE sport_id = :sport_id AND market_key = :market_key";
        $stmt = $this->pdo->prepare($sqlSelect);
        $stmt->execute([
            ':sport_id' => $sportId,
            ':market_key' => $marketKey
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return (int)$row['market_id'];
        }

        $sqlInsert = "INSERT INTO betting_markets 
            (sport_id, market_key, name_en, name_ru, market_category_name_en, market_category_name_ru, created_at, updated_at)
            VALUES
            (:sport_id, :market_key, :name_en, :name_ru, :market_category_name_en, :market_category_name_ru, NOW(), NOW())";
        $stmt = $this->pdo->prepare($sqlInsert);
        $stmt->execute([
            ':sport_id' => $sportId,
            ':market_key' => $marketKey,
            ':name_en' => $nameEn,
            ':name_ru' => $nameRu,
            ':market_category_name_en' => $marketCategoryNameEn,
            ':market_category_name_ru' => $marketCategoryNameRu
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}
