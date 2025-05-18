<?php
declare(strict_types=1);

/**
 * Скрипт пакетного добавления событий из копипаста Фонбет (футбол, теннис).
 * Вся логика работы с БД реализована через финализированные репозитории.
 * Используются: TextPreprocessor, FonbetLeagueTextParser, DateTimeHelper, odds_market_mapper_config.php.
 * Полностью соответствует финальному DDL и структуре компонентов.
 */

use PrognozAi\Core\DatabaseConnection;
use PrognozAi\Core\Utils\TextPreprocessor;
use PrognozAi\DataIngestion\Parsers\FonbetLeagueTextParser;
use PrognozAi\Core\Utils\DateTimeHelper;
use PrognozAi\DataManagement\Repositories\SportsRepository;
use PrognozAi\DataManagement\Repositories\CountriesRepository;
use PrognozAi\DataManagement\Repositories\DataSourcesRepository;
use PrognozAi\DataManagement\Repositories\LeaguesRepository;
use PrognozAi\DataManagement\Repositories\SeasonsRepository;
use PrognozAi\DataManagement\Repositories\ClubsRepository;
use PrognozAi\DataManagement\Repositories\PlayersRepository;
use PrognozAi\DataManagement\Repositories\MatchesRepository;
use PrognozAi\DataManagement\Repositories\BettingMarketsRepository;
use PrognozAi\DataManagement\Repositories\OddsRepository;
use PrognozAi\DataManagement\Repositories\RawTextInputsRepository;

require_once __DIR__ . '/../Core/DatabaseConnection.php';
require_once __DIR__ . '/../Core/Utils/TextPreprocessor.php';
require_once __DIR__ . '/../DataIngestion/Parsers/FonbetLeagueTextParser.php';
require_once __DIR__ . '/../Core/Utils/DateTimeHelper.php';
require_once __DIR__ . '/../DataManagement/Repositories/SportsRepository.php';
require_once __DIR__ . '/../DataManagement/Repositories/CountriesRepository.php';
require_once __DIR__ . '/../DataManagement/Repositories/DataSourcesRepository.php';
require_once __DIR__ . '/../DataManagement/Repositories/LeaguesRepository.php';
require_once __DIR__ . '/../DataManagement/Repositories/SeasonsRepository.php';
require_once __DIR__ . '/../DataManagement/Repositories/ClubsRepository.php';
require_once __DIR__ . '/../DataManagement/Repositories/PlayersRepository.php';
require_once __DIR__ . '/../DataManagement/Repositories/MatchesRepository.php';
require_once __DIR__ . '/../DataManagement/Repositories/BettingMarketsRepository.php';
require_once __DIR__ . '/../DataManagement/Repositories/OddsRepository.php';
require_once __DIR__ . '/../DataManagement/Repositories/RawTextInputsRepository.php';

$oddsMarketMapperConfig = require __DIR__ . '/../Config/odds_market_mapper_config.php';

$error = null;
$success = null;
$debugMessages = [];
$parsedDataPreview = null;
$matchesFound = 0;
$matchesSaved = 0;
$rawTextInputId = null;

// --- HTML-форма и вывод результатов (функции) ---
function htmlHeader()
{
    echo "<!DOCTYPE html><html lang='ru'><head><meta charset='UTF-8'><title>Пакетный импорт событий Fonbet</title>";
    echo "<style>body{font-family:Arial,sans-serif;margin:20px;}pre{background:#f6f6f6;padding:8px;}input,textarea{width:100%;}label{font-weight:bold;}table{border-collapse:collapse;margin:12px 0;}td,th{border:1px solid #ccc;padding:4px 7px;}</style>";
    echo "</head><body>";
}
function htmlFooter() { echo "</body></html>"; }
function htmlForm($clipboardData = '', $sportKey = 'football')
{
    ?>
    <h2>Пакетный импорт линии Фонбет</h2>
    <form method="post">
        <label for="sport">Вид спорта:</label>
        <select name="sport" id="sport">
            <option value="football" <?= $sportKey === 'football' ? 'selected' : '' ?>>Футбол</option>
            <option value="tennis" <?= $sportKey === 'tennis' ? 'selected' : '' ?>>Теннис</option>
        </select><br><br>
        <label for="clipboard_data">Вставьте данные линии (копипаст Фонбет):</label><br>
        <textarea name="clipboard_data" id="clipboard_data" rows="15"><?= htmlspecialchars($clipboardData) ?></textarea><br><br>
        <button type="submit">Импортировать</button>
    </form>
    <hr>
    <?php
}
function htmlDebug($debugMessages)
{
    if ($debugMessages) {
        echo "<details open><summary>Debug Info</summary><pre>";
        foreach ($debugMessages as $msg) echo htmlspecialchars($msg) . "\n";
        echo "</pre></details>";
    }
}

// --- Основная обработка POST-запроса ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // --- 1. Валидация формы ---
        $sportKey = $_POST['sport'] ?? 'football';
        $clipboardData = trim((string)($_POST['clipboard_data'] ?? ''));
        if (!$clipboardData) throw new RuntimeException("Пустое поле с копипастом!");

        // --- 2. Инициализация PDO и всех репозиториев ---
        $pdo = DatabaseConnection::getPDO();
        $pdo->beginTransaction();

        $sportsRepo = new SportsRepository($pdo);
        $countriesRepo = new CountriesRepository($pdo);
        $dataSourcesRepo = new DataSourcesRepository($pdo);
        $leaguesRepo = new LeaguesRepository($pdo);
        $seasonsRepo = new SeasonsRepository($pdo);
        $clubsRepo = new ClubsRepository($pdo);
        $playersRepo = new PlayersRepository($pdo);
        $matchesRepo = new MatchesRepository($pdo);
        $bettingMarketsRepo = new BettingMarketsRepository($pdo);
        $oddsRepo = new OddsRepository($pdo);
        $rawInputsRepo = new RawTextInputsRepository($pdo);

        // --- 3. Найти/создать sport_id (использует заранее предзаполненные значения) ---
        $sportNameRu = $sportKey === 'football' ? 'Футбол' : 'Теннис';
        $sportNameEn = $sportKey;
        $sportId = $sportsRepo->findOrCreateByKey($sportKey, $sportNameEn, $sportNameRu);

        // --- 4. Найти/создать источник данных ("Fonbet_CopyPaste_Parser") ---
        $fonbetSourceId = $dataSourcesRepo->findOrCreateByName(
            'Fonbet_CopyPaste_Parser', 'parser', 'Импорт через копипаст с Фонбет', 'Fonbet CopyPaste Parser', null, true
        );

        // --- 5. Сохранить исходный текст в raw_text_inputs ---
        $userId = null; // Если есть авторизация - сюда подставить user_id.
        $rawTextInputId = $rawInputsRepo->insert($userId, $sportId, $fonbetSourceId, $clipboardData);

        // --- 6. Предварительная очистка, парсинг ---
        $preprocessedLines = (new TextPreprocessor())->preprocess($clipboardData);
        $parser = new FonbetLeagueTextParser();
        $parsed = $parser->parseLeagueData($preprocessedLines, $sportKey);

        // --- 7. Разобрать ключевые поля для справочников ---
        $leagueName = $parsed['league_name'] ?? '';
        $countryHint = $parsed['country_name_hint'] ?? null;
        $seasonHint = $parsed['season_name_hint'] ?? null;
        $parsedMatches = $parsed['matches'] ?? [];

        $parsedDataPreview = $parsed;
        $matchesFound = count($parsedMatches);

        // --- 8. Найти/создать страну ---
        $countryId = null;
        if ($countryHint) {
            $countryId = $countriesRepo->findOrCreateByName($countryHint, $countryHint);
        }

        // --- 9. Найти/создать лигу ---
        $leagueId = $leaguesRepo->findOrCreateByName($sportId, $countryId, $leagueName);

        // --- 10. Найти/создать сезон ---
        $seasonId = null;
        if ($seasonHint && $seasonHint !== '') {
            $seasonId = $seasonsRepo->findOrCreateByName($leagueId, $seasonHint);
        }

        // --- 11. Обработка матчей ---
        $matchesSaved = 0;
        foreach ($parsedMatches as $match) {
            // --- 11.1. Клубы/игроки (футбол/теннис) ---
            if ($sportKey === 'football') {
                $homeClubId = $clubsRepo->findOrCreateByName($sportId, $match['home_team_name'], $countryId);
                $awayClubId = $clubsRepo->findOrCreateByName($sportId, $match['away_team_name'], $countryId);
                $homePlayerId = $awayPlayerId = null;
            } elseif ($sportKey === 'tennis') {
                $homePlayerId = $playersRepo->findOrCreateByFullName($sportId, $match['home_team_name'], $countryId);
                $awayPlayerId = $playersRepo->findOrCreateByFullName($sportId, $match['away_team_name'], $countryId);
                $homeClubId = $awayClubId = null; // Или служебный клуб (см. обсуждение выше)
            } else {
                throw new RuntimeException("Неизвестный вид спорта: $sportKey");
            }

            // --- 11.2. Парсинг даты/времени в UTC ---
            $datetimeUtc = DateTimeHelper::parseDateTimeToUtc($match['raw_datetime_str'], 'Europe/Moscow');
            if (!$datetimeUtc) {
                $debugMessages[] = "Не удалось распарсить дату/время: " . $match['raw_datetime_str'];
                continue;
            }

            // --- 11.3. Проверка дубликата ---
            $existingMatch = $matchesRepo->findExisting(
                $leagueId, $homeClubId, $awayClubId, $datetimeUtc, $homePlayerId, $awayPlayerId
            );
            if ($existingMatch) {
                $debugMessages[] = "Пропущен дубликат матча: " . $match['home_team_name'] . " - " . $match['away_team_name'] . " ($datetimeUtc)";
                continue;
            }

            // --- 11.4. Вставка матча ---
            $matchId = $matchesRepo->insertMatch(
                $sportId, $leagueId, $seasonId, $datetimeUtc, $homeClubId, $awayClubId, $fonbetSourceId,
                $homePlayerId, $awayPlayerId, $rawTextInputId
            );

            // --- 11.5. Коэффициенты (odds, odds_history) ---
            $odds = $match['odds'] ?? [];
            foreach ($odds as $oddsKey => $oddsValue) {
                if ($oddsValue === null || !isset($oddsMarketMapperConfig[$sportKey][$oddsKey])) {
                    continue; // Пропустить пустые коэффициенты или неописанные в маппере ключи
                }
                $mapper = $oddsMarketMapperConfig[$sportKey][$oddsKey];

                if (!empty($mapper['is_parameter_only'])) {
                    continue; // Не коэффициент, а параметр для рынка (например, total_value)
                }

                // Определение значения параметра рынка (например, форы, тоталы)
                $marketParameterValue = null;
                if (!empty($mapper['parameter_source_key'])) {
                    $paramKey = $mapper['parameter_source_key'];
                    $marketParameterValue = $odds[$paramKey] ?? null;
                }

                $marketKey = $mapper['market_key_template'];
                $marketNameEn = $mapper['market_name_template_en'];
                $marketNameRu = $mapper['market_name_template_ru'];
                $marketCategoryEn = $mapper['market_category_name_en'] ?? null;
                $marketCategoryRu = $mapper['market_category_name_ru'] ?? null;

                // --- 11.6. Найти/создать betting_market ---
                $marketId = $bettingMarketsRepo->findOrCreateByKey(
                    $sportId, $marketKey, $marketNameEn, $marketNameRu, $marketCategoryEn, $marketCategoryRu
                );

                // --- 11.7. Сохранить коэффициент и историю ---
                $outcomeKey = $mapper['outcome_key'];
                $oddsRepo->saveOddAndHistory(
                    $matchId,
                    $marketId,
                    $fonbetSourceId,
                    $outcomeKey,
                    $marketParameterValue,
                    (float)$oddsValue
                );
            }

            $matchesSaved++;
        }

        // --- 12. Коммит ---
        $pdo->commit();

        $success = "Импорт успешно завершен! Матчей найдено: $matchesFound, сохранено: $matchesSaved.";
        $rawInputsRepo->updateStatus($rawTextInputId, 'success', null, $leagueName, $matchesFound, $matchesSaved);
    } catch (Throwable $ex) {
        if (isset($pdo) && $pdo->inTransaction()) $pdo->rollBack();
        $error = "Ошибка импорта: " . $ex->getMessage();
        if ($rawTextInputId) {
            try {
                $rawInputsRepo->updateStatus($rawTextInputId, 'error', $error, $leagueName ?? null, $matchesFound ?? 0, $matchesSaved ?? 0);
            } catch (\Throwable $e) {
                // fail silently
            }
        }
    }
}

// --- ВЫВОД: HTML ---
htmlHeader();

if ($error) {
    echo "<div style='color:red;font-weight:bold'>Ошибка: ".htmlspecialchars($error)."</div>";
}
if ($success) {
    echo "<div style='color:green;font-weight:bold'>".htmlspecialchars($success)."</div>";
}
htmlForm($_POST['clipboard_data'] ?? '', $_POST['sport'] ?? 'football');

// --- Предпросмотр распарсенных данных (после парсинга, до записи) ---
if ($parsedDataPreview) {
    echo "<h3>Предпросмотр распарсенных данных:</h3>";
    echo "<pre>" . htmlspecialchars(print_r($parsedDataPreview, true)) . "</pre>";
}

htmlDebug($debugMessages);

htmlFooter();
