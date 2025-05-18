<?php
declare(strict_types=1);

namespace PrognozAi\DataManagement\Repositories;

use PDO;
use RuntimeException;

/**
 * Репозиторий для справочника источников данных (data_sources)
 */
class DataSourcesRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Найти source_id по имени или создать новый источник
     * @param string $sourceName
     * @param string $sourceType
     * @param string|null $descriptionRu
     * @param string|null $descriptionEn
     * @param string|null $baseUrl
     * @param bool $isActive
     * @return int
     */
    public function findOrCreateByName(
        string $sourceName,
        string $sourceType,
        ?string $descriptionRu = null,
        ?string $descriptionEn = null,
        ?string $baseUrl = null,
        bool $isActive = true
    ): int
    {
        // Поиск по source_name
        $sql = "SELECT source_id FROM data_sources WHERE source_name = :source_name LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':source_name' => $sourceName]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return (int)$row['source_id'];
        }
        // Заполняем description_en/ru если только одна есть
        if (!$descriptionEn && $descriptionRu) $descriptionEn = $descriptionRu;
        if (!$descriptionRu && $descriptionEn) $descriptionRu = $descriptionEn;

        $sql = "INSERT INTO data_sources 
                (source_name, source_type, description_ru, description_en, base_url, is_active)
                VALUES (:name, :type, :desc_ru, :desc_en, :url, :active)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':name'     => $sourceName,
            ':type'     => $sourceType,
            ':desc_ru'  => $descriptionRu,
            ':desc_en'  => $descriptionEn,
            ':url'      => $baseUrl,
            ':active'   => $isActive ? 1 : 0,
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}