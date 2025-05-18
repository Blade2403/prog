<?php
declare(strict_types=1);

namespace PrognozAi\DataManagement\Repositories;

use PDO;
use RuntimeException;

/**
 * Репозиторий для игроков (players)
 */
class PlayersRepository
{
    private PDO $pdo;
    private CountriesRepository $countriesRepo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
        $this->countriesRepo = new CountriesRepository($pdo);
    }

    /**
     * Найти player_id по sport_id, ФИО (en/ru) и национальности, или создать нового игрока.
     * Если nationalityCountryId не указан, будет использоваться страна "International".
     * @param int $sportId
     * @param string $fullName
     * @param int|null $nationalityCountryId
     * @return int
     */
    public function findOrCreateByFullName(int $sportId, string $fullName, ?int $nationalityCountryId = null): int
    {
        // Попытка найти по sport_id, имени (en/ru)
        $sql = "SELECT player_id FROM players WHERE sport_id = :sport_id AND (full_name_en = :full_name OR full_name_ru = :full_name) LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':sport_id'  => $sportId,
            ':full_name' => $fullName,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            return (int)$row['player_id'];
        }

        // Национальность — если не указана, берем "International"
        if ($nationalityCountryId === null) {
            $nationalityCountryId = $this->countriesRepo->findOrCreateByName('International', 'Интернациональный');
        }

        // Простое разделение на имя/фамилию
        $nameParts = explode(' ', trim($fullName));
        $firstName = $nameParts[0] ?? $fullName;
        $lastName = $nameParts[1] ?? '';

        $sql = "INSERT INTO players 
            (sport_id, nationality_country_id, full_name_en, full_name_ru, first_name_en, first_name_ru, last_name_en, last_name_ru)
            VALUES
            (:sport_id, :nationality_country_id, :full_name_en, :full_name_ru, :first_name_en, :first_name_ru, :last_name_en, :last_name_ru)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':sport_id'              => $sportId,
            ':nationality_country_id'=> $nationalityCountryId,
            ':full_name_en'          => $fullName,
            ':full_name_ru'          => $fullName,
            ':first_name_en'         => $firstName,
            ':first_name_ru'         => $firstName,
            ':last_name_en'          => $lastName,
            ':last_name_ru'          => $lastName,
        ]);
        return (int)$this->pdo->lastInsertId();
    }
}