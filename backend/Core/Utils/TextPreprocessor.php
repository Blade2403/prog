<?php
// Файл: /var/www/palychai/prognozai/backend/core/UtilsTextPreprocessor.php
declare(strict_types=1);

namespace PrognozAi\core\Utils;

// ... (use statements как были) ...
use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Exception;


class TextPreprocessor
{
    public static function preprocessLeagueClipboardText(
        array $rawLines,
        string $outputDateFormat = 'd.m.Y',
        string $defaultTimeZone = 'Europe/Moscow'
    ): array {
        // ... (инициализация $now, $currentYear, $monthsMap - как было) ...
        try {
            $now = new DateTimeImmutable('now', new DateTimeZone($defaultTimeZone));
        } catch (Exception $e) { /* ... */ return []; }
        $currentYear = $now->format('Y');
        $monthsMap = [ /* ... */ 
            'янв' => '01', 'января' => '01', 'фев' => '02', 'февраля' => '02', 'мар' => '03', 'марта' => '03', 
            'апр' => '04', 'апреля' => '04', 'мая' => '05', 'июн' => '06', 'июня' => '06', 
            'июл' => '07', 'июля' => '07', 'авг' => '08', 'августа' => '08', 'сен' => '09', 'сентября' => '09', 
            'окт' => '10', 'октября' => '10', 'ноя' => '11', 'ноября' => '11', 'дек' => '12', 'декабря' => '12'
        ];

        $processedLines = [];
        $patternsToRemoveLineCompletely = [
            '/^\d+\s+матч(ей|а|е|и|ов)?$/ui',      
            '/^Матч дня$/ui',                      
            '/^Заверш[её]н$/ui',                   
            '/^Показатели команд$/ui',             
            '/^Судья$/ui',                         
            // Общие заголовки рынков и спорта
            '/^(Футбол|Теннис|Исходы|Двойные шансы|Форы|Тоталы|Обе забьют|Фора по сетам|Тотал сетов)$/ui',
            // Заголовки столбцов (включая варианты с пробелами, которые могут остаться)
            '/^(1|Х|2|1X|12|X2)\s*$/ui', // Добавил \s* для возможных пробелов в конце
            '/^ФОРА 1\s*$/ui', '/^ФОРА 2\s*$/ui', 
            '/^Тотал\s*$/ui', '/^Б\s*$/ui', '/^М\s*$/ui', 
            '/^Да\s*$/ui', '/^Нет\s*$/ui',
            '/^Сеты\s*$/ui', // Для тенниса
            '/^Ф1\s*$/ui', '/^Ф2\s*$/ui' // Для тенниса
        ];
        // ... (остальной код метода TextPreprocessor::preprocessLeagueClipboardText 
        //      остается ТОЧНО ТАКИМ ЖЕ, как в моем предыдущем ответе 
        //      "Коллега, я понимаю ваше разочарование...")
        //      Важно, что он теперь будет удалять больше заголовков благодаря $patternsToRemoveLineCompletely.
        // ... (включая нормализацию чисел, параметров и дат) ...
        foreach ($rawLines as $line) {
            $trimmedLine = trim($line);
            if (empty($trimmedLine)) continue;

            $skipLine = false;
            foreach ($patternsToRemoveLineCompletely as $pattern) {
                if (preg_match($pattern, $trimmedLine)) { $skipLine = true; break; }
            }
            if ($skipLine) { error_log("TP INFO: Removing line by pattern: '$trimmedLine'"); continue; }

            if (preg_match('/^\+\d{3,}$/u', $trimmedLine)) { // Маркер конца +NNN (3+ цифры)
                error_log("TP INFO: Keeping delimiter line: '$trimmedLine'");
                $processedLines[] = $trimmedLine;
                continue;
            }
            
            $originalLineForLog = $trimmedLine;
            $lineForNumericCheck = preg_replace('/[\x{2007}\x{2008}\x{202F}]+/u', '', $trimmedLine);
            $lineForNumericCheck = trim(preg_replace('/\s+/', ' ', $lineForNumericCheck));
            $lineForNumericCheck = preg_replace('/^([+-])\s+(\d)/u', '$1$2', $lineForNumericCheck);
            
            if (preg_match('/^([+-]?\d+([.,]\d+)?)$/u', $lineForNumericCheck) || $lineForNumericCheck === '-') {
                $finalValue = ($lineForNumericCheck === "+0" || $lineForNumericCheck === "-0") ? "0" : $lineForNumericCheck;
                if ($finalValue !== $trimmedLine) {
                     error_log("TP INFO: Normalized odd/param: '$trimmedLine' -> '$finalValue'");
                }
                $trimmedLine = $finalValue;
            } else {
                 error_log("TP DEBUG: Line '$trimmedLine' not numeric/param after clean ('$lineForNumericCheck'). Check date.");
            }

            if (preg_match('/^(Сегодня|Завтра|(?:(\d{1,2})\s+)?([а-яёА-ЯЁ]+))\s+в\s+(\d{1,2}:\d{2})$/ui', $trimmedLine, $dateMatches)) {
                $dateKeywordOrFullDayMonth = $dateMatches[1]; $dayIfPresent = $dateMatches[2] ?? null; $monthNameIfPresent = $dateMatches[3] ?? null; $timePart = $dateMatches[4]; $parsedDate = null;
                try {
                    if (mb_strtolower($dateKeywordOrFullDayMonth) === 'сегодня') { $parsedDate = $now; }
                    elseif (mb_strtolower($dateKeywordOrFullDayMonth) === 'завтра') { $parsedDate = $now->modify('+1 day'); }
                    elseif ($dayIfPresent && $monthNameIfPresent) {
                        $day = $dayIfPresent; $monthNameInput = mb_strtolower($monthNameIfPresent); $monthNumber = null;
                        foreach ($monthsMap as $name => $num) { if (mb_stripos($monthNameInput, $name) === 0) { $monthNumber = $num; break; } }
                        if ($monthNumber) {
                            $dateStrToParse = $currentYear . '-' . $monthNumber . '-' . str_pad($day, 2, '0', STR_PAD_LEFT);
                            $parsedDate = new DateTime($dateStrToParse, new DateTimeZone($defaultTimeZone));
                        } else { error_log("TP WARNING [Clean Date]: Could not parse month: '$monthNameInput' from '$originalLineForLog'");}
                    }
                    if ($parsedDate) {
                        if (preg_match('/^(\d{1,2}):(\d{2})$/', $timePart, $timeComponents)) {
                            $parsedDate->setTime((int)$timeComponents[1], (int)$timeComponents[2]);
                        }
                        $formattedDate = $parsedDate->format($outputDateFormat);
                        $processedLines[] = $formattedDate . ' в ' . $timePart;
                        error_log("TP DEBUG [Clean Date]: Date processed: '$originalLineForLog' -> '{$processedLines[count($processedLines)-1]}'");
                        continue; 
                    } else { error_log("TP WARNING [Clean Date]: Date part not parsed for: '$originalLineForLog'."); }
                } catch (Exception $e) { error_log("TP ERROR [Clean Date]: Date Exception for '{$originalLineForLog}': " . $e->getMessage()); }
            }
            
            $processedLines[] = $trimmedLine;
        }
        return $processedLines;
    }
}
