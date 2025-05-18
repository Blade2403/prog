<?php

namespace PrognozAi\DataIngestion\Parsers;

class FonbetLeagueTextParser
{
    /**
     * Parse an array of preprocessed text lines from a Fonbet league clipboard copy.
     *
     * @param string[] $lines    The lines of text (already exploded by "\n" and preprocessed).
     * @param string   $sportKey The sport key ('football' or 'tennis').
     * @return array   Structured data as described (league info and matches with odds).
     */
    public function parseLeagueData(array $lines, string $sportKey): array
    {
        $result = [
            'league_name'       => '',
            'country_name_hint' => null,
            'season_name_hint'  => null,
            'sport_key'         => $sportKey,
            'matches'           => []
        ];

        // Skip any leading empty lines
        $totalLines = count($lines);
        $index = 0;
        while ($index < $totalLines && trim($lines[$index]) === '') {
            $index++;
        }
        if ($index >= $totalLines) {
            return $result; // no content
        }

        // Process league line (first non-empty line)
        $leagueLine = trim($lines[$index]);
        $index++;
        $result['league_name'] = $leagueLine;
        // Attempt to split country and season from league line
        if (($dotPos = strpos($leagueLine, '.')) !== false) {
            // Format like "Country. League Name ... [Season]"
            $countryCandidate = trim(substr($leagueLine, 0, $dotPos));
            $leagueRest = trim(substr($leagueLine, $dotPos + 1));
            if ($countryCandidate !== '' && mb_strlen($countryCandidate) > 1) {
                $result['country_name_hint'] = $countryCandidate;
            }
            // Check if last token in leagueRest is a season (year or year range)
            $parts = preg_split('/\s+/', $leagueRest);
            if (!empty($parts)) {
                $lastToken = end($parts);
                if (preg_match('/^\d{4}(?:\/\d{2,4})?$/', $lastToken)) {
                    // e.g. "2023" or "2023/24" or "2023/2024"
                    $result['season_name_hint'] = $lastToken;
                    array_pop($parts);
                    $leagueRest = trim(implode(' ', $parts));
                }
            }
            if ($leagueRest !== '') {
                $result['league_name'] = $leagueRest;
            }
        } elseif (strpos($leagueLine, '(') !== false && strpos($leagueLine, ')') !== false) {
            // Format like "League Name (Country)" or "League Name (Season)"
            $openPos = strpos($leagueLine, '(');
            $closePos = strrpos($leagueLine, ')');
            if ($closePos !== false && $closePos > $openPos) {
                $insideParen = trim(substr($leagueLine, $openPos + 1, $closePos - $openPos - 1));
                $beforeParen = trim(substr($leagueLine, 0, $openPos));
                if ($insideParen !== '') {
                    if (preg_match('/\d{4}/', $insideParen)) {
                        // If it contains a number, treat as season
                        $result['season_name_hint'] = $insideParen;
                    } else {
                        // Otherwise treat as country
                        $result['country_name_hint'] = $insideParen;
                    }
                }
                if ($beforeParen !== '') {
                    $result['league_name'] = $beforeParen;
                }
            }
        }

        // Prepare regex patterns (with Unicode support)
        $dateWithTeamPattern = '/^(.+?)\s+((?:Сегодня|Завтра|\d{1,2}\s+\p{L}+(?:\s+\d{4})?)\s+в\s+\d{1,2}:\d{2})$/u';
        $dateOnlyPattern     = '/^(?:Сегодня|Завтра|\d{1,2}\s+\p{L}+(?:\s+\d{4})?)\s+в\s+\d{1,2}:\d{2}$/u';

        // Patterns for odds:
        $moneylineFootballPattern = '/(?:^|\s)(?:П1|1)\s+([0-9]+(?:\.[0-9]+)?|–)\s+(?:Х|X)\s+([0-9]+(?:\.[0-9]+)?|–)\s+(?:П2|2)\s+([0-9]+(?:\.[0-9]+)?|–)/u';
        $moneylineTennisPattern   = '/(?:^|\s)(?:П1|1)\s+([0-9]+(?:\.[0-9]+)?|–)\s+(?:П2|2)\s+([0-9]+(?:\.[0-9]+)?|–)/u';
        $handicapPattern          = '/(?:Фора1|Ф1|Handicap1)\s*\(?([+\-−]?[0-9]+(?:\.[0-9]+)?)\)?\s+([0-9]+(?:\.[0-9]+)?|–)\s*(?:Фора2|Ф2|Handicap2)\s*\(?([+\-−]?[0-9]+(?:\.[0-9]+)?)\)?\s+([0-9]+(?:\.[0-9]+)?|–)/u';
        $totalPattern             = '/(?:Тотал(?!\s*сет)|Total(?!\s*Sets))\s*\(?([0-9]+(?:\.[0-9]+)?)\)?\s*(?:Б|Over)\s+([0-9]+(?:\.[0-9]+)?|–)\s*(?:М|Under)\s+([0-9]+(?:\.[0-9]+)?|–)/u';
        $totalSetsPattern         = '/(?:Тотал\s*сетов|Total\s*Sets)\s*\(?([0-9]+(?:\.[0-9]+)?)\)?\s*(?:Б|Over)\s+([0-9]+(?:\.[0-9]+)?|–)\s*(?:М|Under)\s+([0-9]+(?:\.[0-9]+)?|–)/u';
        $bothTeamsPattern         = '/(?:Обе\s*забьют|Both\s*teams\s*to\s*score).*?(?:Да|Yes)\s+([0-9]+(?:\.[0-9]+)?|–)\s*(?:Нет|No)\s+([0-9]+(?:\.[0-9]+)?|–)/ui';
        $doubleChancePattern      = '/(?:Двойной\s*шанс|Double\s*chance).*?1X\s+([0-9]+(?:\.[0-9]+)?|–)\s*12\s+([0-9]+(?:\.[0-9]+)?|–)\s*X2\s+([0-9]+(?:\.[0-9]+)?|–)/ui';

        // Helper to convert a matched value string to float or null
        $toFloatOrNull = function ($valueStr) {
            if ($valueStr === null) {
                return null;
            }
            $val = trim($valueStr);
            if ($val === '' || $val === '–' || $val === '—' || $val === '-') {
                return null;
            }
            // Use locale-independent conversion (ensure decimal point is dot)
            $floatVal = floatval(str_replace(',', '.', $val));
            return $floatVal;
        };

        // Iterate through lines to build matches
        $currentMatch = null;
        $currentRawLines = [];
        for (; $index < $totalLines; $index++) {
            $line = trim($lines[$index]);
            if ($line === '') {
                continue; // skip empty lines between or after matches
            }

            // Check if this line indicates a new match (contains " – " or " - " between team names)
            if ((mb_strpos($line, ' – ') !== false) || (mb_strpos($line, ' - ') !== false)) {
                // If we were collecting a match, finalize it
                if ($currentMatch !== null) {
                    $currentMatch['raw_match_lines'] = $currentRawLines;
                    $result['matches'][] = $currentMatch;
                }
                $currentMatch = null;
                $currentRawLines = [];

                // Split home and away by the first occurrence of " – " or " - "
                $separator = (mb_strpos($line, ' – ') !== false) ? ' – ' : ' - ';
                $parts = explode($separator, $line, 2);
                $homeTeam = trim($parts[0]);
                $awayAndMaybeDate = isset($parts[1]) ? trim($parts[1]) : '';

                $awayTeam = $awayAndMaybeDate;
                $rawDate  = null;
                // Try to extract date from the same line
                if (preg_match($dateWithTeamPattern, $awayAndMaybeDate, $m)) {
                    // Matches: [1] away team, [2] date string
                    $awayTeam = trim($m[1]);
                    $rawDate  = trim($m[2]);
                } else {
                    // If no date on the same line, check next line for a standalone date/time
                    if ($index + 1 < $totalLines) {
                        $nextLine = trim($lines[$index + 1]);
                        if ($nextLine !== '' && preg_match($dateOnlyPattern, $nextLine)) {
                            $rawDate = $nextLine;
                            // Use entire awayAndMaybeDate as the away team name
                            $awayTeam = $awayAndMaybeDate;
                            // Consume the next line as part of current match
                            $currentRawLines = [$line, $nextLine];
                            $index++; // skip the date line in the main loop
                        }
                    }
                }

                // Initialize a new match entry
                $currentMatch = [
                    'home_team_name' => $homeTeam,
                    'away_team_name' => $awayTeam,
                    'raw_datetime_str' => $rawDate,
                    'odds' => [
                        'p1' => null, 'x' => null, 'p2' => null,
                        'handicap1_value' => null, 'handicap1_coeff' => null,
                        'handicap2_value' => null, 'handicap2_coeff' => null,
                        'total_value' => null, 'total_over' => null, 'total_under' => null,
                        'both_yes' => null, 'both_no' => null,
                        'double_chance_1x' => null, 'double_chance_12' => null, 'double_chance_x2' => null
                    ],
                    'raw_match_lines' => []  // to be filled when finalized
                ];
                if ($sportKey === 'tennis') {
                    // Include Total Sets keys for tennis matches
                    $currentMatch['odds']['total_sets_value'] = null;
                    $currentMatch['odds']['total_sets_over']  = null;
                    $currentMatch['odds']['total_sets_under'] = null;
                }
                // If we didn't already add the lines (in case of separate date line, we did above),
                // add the current line as part of match lines.
                if (empty($currentRawLines)) {
                    $currentRawLines[] = $line;
                }
                // Continue to next line (odds lines will be processed in following iterations)
                continue;
            }

            // If this line is not a team separator line, it must belong to the current match as an odds line.
            if ($currentMatch === null) {
                // (Safety check: if we encounter odds lines without a current match, skip them)
                continue;
            }
            $currentRawLines[] = $line;

            // Try each odds pattern on this line and fill the values if matched
            if ($sportKey === 'football' && preg_match($moneylineFootballPattern, $line, $m)) {
                // $m[1] = P1 odds, $m[2] = X odds, $m[3] = P2 odds
                $currentMatch['odds']['p1'] = $toFloatOrNull($m[1]);
                $currentMatch['odds']['x']  = $toFloatOrNull($m[2]);
                $currentMatch['odds']['p2'] = $toFloatOrNull($m[3]);
            } elseif ($sportKey === 'tennis' && preg_match($moneylineTennisPattern, $line, $m)) {
                // $m[1] = P1 odds, $m[2] = P2 odds
                $currentMatch['odds']['p1'] = $toFloatOrNull($m[1]);
                $currentMatch['odds']['p2'] = $toFloatOrNull($m[2]);
                // 'x' (draw) remains null for tennis
            }

            if (preg_match($handicapPattern, $line, $m)) {
                // $m[1] = handicap1 value, $m[2] = handicap1 coeff, $m[3] = handicap2 value, $m[4] = handicap2 coeff
                $currentMatch['odds']['handicap1_value'] = $toFloatOrNull($m[1]);
                $currentMatch['odds']['handicap1_coeff'] = $toFloatOrNull($m[2]);
                $currentMatch['odds']['handicap2_value'] = $toFloatOrNull($m[3]);
                $currentMatch['odds']['handicap2_coeff'] = $toFloatOrNull($m[4]);
            }

            if (preg_match($totalPattern, $line, $m)) {
                // $m[1] = total value, $m[2] = over, $m[3] = under
                $currentMatch['odds']['total_value'] = $toFloatOrNull($m[1]);
                $currentMatch['odds']['total_over']  = $toFloatOrNull($m[2]);
                $currentMatch['odds']['total_under'] = $toFloatOrNull($m[3]);
            }

            if ($sportKey === 'tennis' && preg_match($totalSetsPattern, $line, $m)) {
                // $m[1] = total sets value, $m[2] = over, $m[3] = under
                $currentMatch['odds']['total_sets_value'] = $toFloatOrNull($m[1]);
                $currentMatch['odds']['total_sets_over']  = $toFloatOrNull($m[2]);
                $currentMatch['odds']['total_sets_under'] = $toFloatOrNull($m[3]);
            }

            if (preg_match($bothTeamsPattern, $line, $m)) {
                // $m[1] = Yes odds, $m[2] = No odds
                $currentMatch['odds']['both_yes'] = $toFloatOrNull($m[1]);
                $currentMatch['odds']['both_no']  = $toFloatOrNull($m[2]);
            }

            if (preg_match($doubleChancePattern, $line, $m)) {
                // $m[1] = 1X odds, $m[2] = 12 odds, $m[3] = X2 odds
                $currentMatch['odds']['double_chance_1x'] = $toFloatOrNull($m[1]);
                $currentMatch['odds']['double_chance_12'] = $toFloatOrNull($m[2]);
                $currentMatch['odds']['double_chance_x2'] = $toFloatOrNull($m[3]);
            }
        }

        // Append the last match if still open
        if ($currentMatch !== null) {
            $currentMatch['raw_match_lines'] = $currentRawLines;
            $result['matches'][] = $currentMatch;
        }

        return $result;
    }
}
