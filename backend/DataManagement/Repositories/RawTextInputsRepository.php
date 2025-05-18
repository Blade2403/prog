<?php
// Файл: backend/DataManagement/Repositories/RawTextInputsRepository.php
// Назначение: Репозиторий для работы с таблицей raw_text_inputs
declare(strict_types=1);

namespace PrognozAi\DataManagement\Repositories;

use PDO;

class RawTextInputsRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Вставить новую запись с исходным текстом парсера.
     * Возвращает ID новой записи.
     */
    public function insert(?int $userId, int $sportId, int $sourceId, string $rawText): int
    {
        $sql = "INSERT INTO raw_text_inputs 
            (user_id, sport_id, source_id, raw_text, processing_status_key, error_message, parsed_league_name, matches_found_count, matches_saved_count, created_at, processed_at)
            VALUES
            (:user_id, :sport_id, :source_id, :raw_text, 'pending', NULL, NULL, 0, 0, NOW(), NULL)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':user_id' => $userId,
            ':sport_id' => $sportId,
            ':source_id' => $sourceId,
            ':raw_text' => $rawText
        ]);
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Обновить статус обработки записи.
     */
    public function updateStatus(
        int $inputId,
        string $statusKey,
        ?string $errorMessage = null,
        ?string $parsedLeagueName = null,
        ?int $matchesFound = null,
        ?int $matchesSaved = null
    ): void {
        $sql = "UPDATE raw_text_inputs SET 
                    processing_status_key = :status_key,
                    error_message = :error_message,
                    parsed_league_name = :parsed_league_name,
                    matches_found_count = :matches_found,
                    matches_saved_count = :matches_saved,
                    processed_at = NOW()
                WHERE input_id = :input_id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':status_key' => $statusKey,
            ':error_message' => $errorMessage,
            ':parsed_league_name' => $parsedLeagueName,
            ':matches_found' => $matchesFound,
            ':matches_saved' => $matchesSaved,
            ':input_id' => $inputId
        ]);
    }
}
