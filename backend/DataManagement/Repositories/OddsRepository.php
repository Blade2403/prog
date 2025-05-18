<?php
declare(strict_types=1);

namespace PrognozAi\DataManagement\Repositories;

use PDO;
use RuntimeException;

/**
 * Репозиторий для работы с таблицами odds и odds_history.
 */
class OddsRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Вставляет новую запись в таблицу odds.
     * @return int ID добавленной записи odds
     */
    public function insertOdds(
        int $matchId,
        int $marketId,
        int $bookmakerId,
        string $outcomeKey,
        ?string $marketParameterValue,
        float $oddsValue,
        string $lastUpdatedAt
    ): int {
        $sql = "INSERT INTO odds (
            match_id, market_id, bookmaker_id, outcome_key, market_parameter_value, odds_value, last_updated_at
        ) VALUES (
            :match_id, :market_id, :bookmaker_id, :outcome_key, :market_parameter_value, :odds_value, :last_updated_at
        )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':match_id', $matchId, PDO::PARAM_INT);
        $stmt->bindValue(':market_id', $marketId, PDO::PARAM_INT);
        $stmt->bindValue(':bookmaker_id', $bookmakerId, PDO::PARAM_INT);
        $stmt->bindValue(':outcome_key', $outcomeKey, PDO::PARAM_STR);
        $stmt->bindValue(':market_parameter_value', $marketParameterValue, $marketParameterValue === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':odds_value', $oddsValue);
        $stmt->bindValue(':last_updated_at', $lastUpdatedAt, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            throw new RuntimeException("Ошибка при вставке odds: " . implode(' | ', $stmt->errorInfo()));
        }
        return (int)$this->pdo->lastInsertId();
    }

    /**
     * Вставляет новую запись в таблицу odds_history.
     */
    public function insertOddsHistory(
        int $matchId,
        int $marketId,
        int $bookmakerId,
        string $outcomeKey,
        ?string $marketParameterValue,
        float $oddsValue,
        string $changeTimestamp,
        ?float $oddsValueOld = null
    ): void {
        $sql = "INSERT INTO odds_history (
            match_id, market_id, bookmaker_id, outcome_key, market_parameter_value, odds_value, odds_value_old, change_timestamp
        ) VALUES (
            :match_id, :market_id, :bookmaker_id, :outcome_key, :market_parameter_value, :odds_value, :odds_value_old, :change_timestamp
        )";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':match_id', $matchId, PDO::PARAM_INT);
        $stmt->bindValue(':market_id', $marketId, PDO::PARAM_INT);
        $stmt->bindValue(':bookmaker_id', $bookmakerId, PDO::PARAM_INT);
        $stmt->bindValue(':outcome_key', $outcomeKey, PDO::PARAM_STR);
        $stmt->bindValue(':market_parameter_value', $marketParameterValue, $marketParameterValue === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(':odds_value', $oddsValue);
        $stmt->bindValue(':odds_value_old', $oddsValueOld === null ? null : $oddsValueOld);
        $stmt->bindValue(':change_timestamp', $changeTimestamp, PDO::PARAM_STR);

        if (!$stmt->execute()) {
            throw new RuntimeException("Ошибка при вставке odds_history: " . implode(' | ', $stmt->errorInfo()));
        }
    }

    /**
     * Вставляет запись в odds и в odds_history.
     * Возвращает ID odds.
     */
    public function saveOddAndHistory(
        int $matchId,
        int $marketId,
        int $bookmakerId,
        string $outcomeKey,
        ?string $marketParameterValue,
        float $oddsValue
    ): int {
        // Время фиксации коэффициента (UTC)
        $now = (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format('Y-m-d H:i:s');
        // Сохраняем odds
        $oddsId = $this->insertOdds($matchId, $marketId, $bookmakerId, $outcomeKey, $marketParameterValue, $oddsValue, $now);
        // Сохраняем историю (первое сохранение — old null)
        $this->insertOddsHistory($matchId, $marketId, $bookmakerId, $outcomeKey, $marketParameterValue, $oddsValue, $now, null);
        return $oddsId;
    }
}
