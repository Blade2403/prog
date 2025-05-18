<?php
declare(strict_types=1);

namespace PrognozAi\DataManagement\Repositories;

use PDO;
use RuntimeException;

/**
 * Репозиторий для справочника стран (countries)
 */
class CountriesRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Найти country_id по имени (en/ru) или создать новую страну.
     * Специальная логика для "International".
     * @param string $nameEn
     * @param string $nameRu
     * @param string|null $isoAlpha2
     * @param string|null $isoAlpha3
     * @param string|null $numericCode
     * @param string|null $flagUrl
     * @return int
     */
    public function findOrCreateByName(
        string $nameEn,
        string $nameRu = '',
        ?string $isoAlpha2 = null,
        ?string $isoAlpha3 = null,
        ?string $numericCode = null,
        ?string $flagUrl = null
    ): int
    {
        // Сначала ищем по английскому или русскому названию
        $sql = "SELECT country_id FROM countries WHERE country_name_en = :name_en OR country_name_ru = :name_ru LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':name_en' => $nameEn, ':name_ru' => $nameRu]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return (int)$row['country_id'];
        }

        // Обработка "International"
        $isInternational = (mb_strtolower($nameEn) === 'international' || mb_strtolower($nameRu) === 'интернациональный');
        $isoAlpha2 = $isoAlpha2 ?? 'XX';
        $isoAlpha3 = $isoAlpha3 ?? 'XXX';
        $numericCode = $numericCode ?? null;
        $flagUrl = $flagUrl ?? null;

        if ($isInternational) {
            $isoAlpha2 = 'XX';
            $isoAlpha3 = 'XXX';
            $numericCode = null;
            $flagUrl = null;
        }

        $sql = "INSERT INTO countries 
                (country_name_en, country_name_ru, iso_alpha2, iso_alpha3, numeric_code, flag_url)
                VALUES (:name_en, :name_ru, :iso2, :iso3, :numcode, :flag)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':name_en' => $nameEn,
            ':name_ru' => $nameRu,
            ':iso2'    => $isoAlpha2,
            ':iso3'    => $isoAlpha3,
            ':numcode' => $numericCode,
            ':flag'    => $flagUrl,
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}