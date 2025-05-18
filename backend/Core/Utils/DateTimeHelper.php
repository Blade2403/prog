<?php
declare(strict_types=1);

namespace PrognozAi\Core\Utils;

use DateTime;
use DateTimeZone;
use Exception;

/**
 * Вспомогательный класс для разбора и преобразования строк даты/времени (Фонбет/русск.форматы) в UTC (Y-m-d H:i:s)
 */
class DateTimeHelper
{
    /**
     * Преобразует строку вида "Сегодня в 18:00", "Завтра в 19:00", "25.05.2025 в 21:00", "15 мая в 19:00" и т.д.
     * к строке 'Y-m-d H:i:s' в UTC.
     * @param string $inputDateTimeString
     * @param string $sourceTimeZone
     * @return string
     * @throws Exception
     */
    public static function parseDateTimeToUtc(string $inputDateTimeString, string $sourceTimeZone = 'Europe/Moscow'): string
    {
        $input = trim($inputDateTimeString);
        $tz = new DateTimeZone($sourceTimeZone);
        $now = new DateTime('now', $tz);

        // Маппинг месяцев на русском
        $ruMonths = [
            'января' => '01', 'февраля' => '02', 'марта' => '03', 'апреля' => '04',
            'мая' => '05', 'июня' => '06', 'июля' => '07', 'августа' => '08',
            'сентября' => '09', 'октября' => '10', 'ноября' => '11', 'декабря' => '12',
            // поддержка коротких форм (может быть полезна для ленивых копипастов)
            'янв' => '01', 'фев' => '02', 'мар' => '03', 'апр' => '04',
            'май' => '05', 'июн' => '06', 'июл' => '07', 'авг' => '08',
            'сен' => '09', 'oct' => '10', 'ноя' => '11', 'дек' => '12',
        ];

        // Сегодня/Завтра
        if (mb_stripos($input, 'сегодня') !== false) {
            if (!preg_match('/в\s+(\d{1,2}:\d{2})/ui', $input, $timeMatch)) {
                throw new Exception("Не найдено время в строке: $input");
            }
            $date = $now->format('Y-m-d');
            $time = $timeMatch[1];
        } elseif (mb_stripos($input, 'завтра') !== false) {
            if (!preg_match('/в\s+(\d{1,2}:\d{2})/ui', $input, $timeMatch)) {
                throw new Exception("Не найдено время в строке: $input");
            }
            $tomorrow = (clone $now)->modify('+1 day');
            $date = $tomorrow->format('Y-m-d');
            $time = $timeMatch[1];
        }
        // 25.05.2025 в 18:00 или 25.05 в 18:00
        elseif (preg_match('/(\d{1,2})\.(\d{1,2})(?:\.(\d{4}))?\s*в\s*(\d{1,2}:\d{2})/', $input, $m)) {
            $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $month = str_pad($m[2], 2, '0', STR_PAD_LEFT);
            $year = $m[3] ?? $now->format('Y');
            $time = $m[4];

            // Если год не указан и дата уже прошла - берем следующий год
            $dateCheck = DateTime::createFromFormat('d.m.Y H:i', "$day.$month.$year $time", $tz);
            if ($m[3] === null && $dateCheck && $dateCheck < $now) {
                $year = (string)((int)$now->format('Y') + 1);
            }
            $date = "$year-$month-$day";
        }
        // 15 мая в 19:00
        elseif (preg_match('/(\d{1,2})\s+([а-яё]+)\s+в\s+(\d{1,2}:\d{2})/ui', $input, $m)) {
            $day = str_pad($m[1], 2, '0', STR_PAD_LEFT);
            $ruMonth = mb_strtolower($m[2]);
            $month = $ruMonths[$ruMonth] ?? null;
            if ($month === null) {
                throw new Exception("Не удалось определить месяц: $ruMonth");
            }
            $year = $now->format('Y');
            $time = $m[3];
            // Если дата уже прошла, берем следующий год
            $dateCheck = DateTime::createFromFormat('d.m.Y H:i', "$day.$month.$year $time", $tz);
            if ($dateCheck && $dateCheck < $now) {
                $year = (string)((int)$now->format('Y') + 1);
            }
            $date = "$year-$month-$day";
        }
        else {
            throw new Exception("Неизвестный формат даты/времени: $input");
        }

        // Составляем дату/время в локальном времени
        $dt = DateTime::createFromFormat('Y-m-d H:i', "$date $time", $tz);
        if (!$dt) {
            throw new Exception("Ошибка разбора даты/времени: $input");
        }
        // Переводим в UTC
        $dt->setTimezone(new DateTimeZone('UTC'));
        return $dt->format('Y-m-d H:i:s');
    }
}
