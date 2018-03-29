<?php

namespace Flagship\Components\Helpers\Services;

class CanadianHolidaysService
{
    const SECONDS_PER_DAY = 86400;

    public static $sh = [
        1 => [
            [
                'str' => 'January 1',
                'applies' => ['nationwide'],
                'except' => [],
                'name' => "New year's day",
                ],
        ],
        2 => [
            [
                'str' => 'third Monday of February',
                'applies' => ['PE'],
                'except' => [],
                'name' => "Islander Day",
            ],
            [
                'str' => 'third Monday of February',
                'applies' => ['AB', 'SK', 'ON'],
                'except' => [],
                'name' => "Family Day",
            ],
            [
                'str' => 'second Monday of February',
                'applies' => ['BC'],
                'except' => [],
                'name' => "Family Day",
            ],
            [
                'str' => 'third Monday of February',
                'applies' => ['MB'],
                'except' => [],
                'name' => "Louis Riel Day",
            ],
            [
                'str' => 'third Monday of February',
                'applies' => ['NS'],
                'except' => [],
                'name' => "Heritage Day",
            ],
        ],
        3 => [
            [
                'str' => 'March 17',
                'applies' => ['NL'],
                'except' => [],
                'name' => "St. Patrick's Day",
            ],
        ],
        4 => [
            [
                'str' => 'April 23',
                'applies' => ['NL'],
                'except' => [],
                'name' => "St. George's Day",
            ],
        ],
        5 => [
            [
                'str' => 'May 25 Monday -7 days',
                'applies' => ['nationwide'],
                'except' => ['NB', 'NS', 'PE', 'NL', 'QC'],
                'name' => "Victoria Day",
            ],
            [
                'str' => 'May 25 Monday -7 days',
                'applies' => ['QC'],
                'except' => [],
                'name' => "Patriots Day",
            ],
        ],
        6 => [
            [
                'str' => 'June 21',
                'applies' => ['NT'],
                'except' => [],
                'name' => "Victoria Day",
            ],
            [
                'str' => 'June 24',
                'applies' => ['QC'],
                'except' => [],
                'name' => "St. Jean Baptiste Day",
            ],
            [
                'str' => 'June 24',
                'applies' => ['NL'],
                'except' => [],
                'name' => "Discovery Day",
            ],
        ],
        7 => [
            [
                'str' => 'July 1',
                'applies' => ['nationwide'],
                'except' => [],
                'name' => "Canada Day",
            ],
            [
                'str' => 'July 9',
                'applies' => ['NU'],
                'except' => [],
                'name' => "Nunavut Day",
            ],
        ],
        8 => [
            [
                'str' => 'first Monday of August',
                'applies' => ['AB', 'BC', 'SK', 'ON', 'NB', 'NU'],
                'except' => [],
                'name' => "Civic Holiday",
            ],
        ],
        9 => [
            [
                'str' => 'first Monday of September',
                'applies' => ['nationwide'],
                'except' => [],
                'name' => "Labour Day",
            ],
        ],
        10 => [
            [
                'str' => 'second Monday of October',
                'applies' => ['nationwide'],
                'except' => ['NB', 'NS', 'PE', 'NL'],
                'name' => "Thanksgiving",
            ],
        ],
        11 => [
            [
                'str' => 'November 11',
                'applies' => ['nationwide'],
                'except' => ['ON', 'QC', 'NS', 'NL'],
                'name' => "Remembrance Day",
            ],
        ],
        12 => [
            [
                'str' => 'December 25',
                'applies' => ['nationwide'],
                'except' => [],
                'name' => "Christmas Day",
            ],
            [
                'str' => 'December 26',
                'applies' => ['ON'],
                'except' => [],
                'name' => "Boxing Day",
            ],
        ],
    ];

    /**
     * List of month holidays cache
     *
     * @var array
     */
    public static $monthHolidaysCache = [];

    public static function isBusinessDay(string $province, string $date) : bool
    {
        $target = strtotime($date);
        if (in_array(date('l', $target), ['Saturday', 'Sunday'])) {
            return false;
        }

        $holidays = self::findApplicableHolidays($province, $target);

        return !in_array(date('Y-m-d', $target), $holidays);
    }

    public static function getBusinessDayFor(string $date, int $businessDaysToAdd, string $province = '', bool $useNextMonthToo = null) : string
    {
        $target = strtotime($date);
        $count = 0;
        $monthBefore = (int) date('n', $target);
        $nextMonthHolidays = [];
        if ($useNextMonthToo === true) {
            $nextMonthHolidays = self::getNexMonthHolidays($date, $province);
        }
        $holidays = array_merge(self::findApplicableHolidays($province, $target), $nextMonthHolidays);

        //Avoids weekends during each itteration, so that we don't consider weekends to be business days and not increment the count for them
        do {
            $target += self::SECONDS_PER_DAY;
            $count += in_array(date('Y-m-d', $target), $holidays) ? 0 : 1;
            $target = self::avoidWeekend($target);
        } while ($count < $businessDaysToAdd || in_array(date('Y-m-d', $target), $holidays));

        $result = date('Y-m-d', $target);

        // If after adding the days we are in a different month, we recalculate everything with the holidays of both months
        // We check if $useNextMonthToo was provided to avoid an infinite loop
        $monthAfter =  (int) date('n', $target);
        if ($monthAfter !== $monthBefore && !$useNextMonthToo) {
            return self::getBusinessDayFor($date, $businessDaysToAdd, $province, true);
        }

        return $result;
    }

    public static function getNextBusinessDayAfter(string $province, string $date, bool $useNextMonthToo = null) : string
    {
        $target = strtotime($date);
        $monthBefore = (int) date('n', $target);
        $nextMonthHolidays = [];
        if ($useNextMonthToo === true) {
            $nextMonthHolidays = self::getNexMonthHolidays($date, $province);
        }
        $holidays = array_merge(self::findApplicableHolidays($province, $target), $nextMonthHolidays);

        // Next day
        $target += self::SECONDS_PER_DAY;

        // Avoid holidays
        while (in_array(date('Y-m-d', $target), $holidays)) {
            $target += self::SECONDS_PER_DAY;
        }
        // Avoid weekends
        $target = self::avoidWeekend($target);

        // Avoid holidays again
        while (in_array(date('Y-m-d', $target), $holidays)) {
            $target += self::SECONDS_PER_DAY;
        }

        $result = date('Y-m-d', $target);

        // If after adding the days we are in a different month, we recalculate everything with the holidays of both months
        // We check if $useNextMonthToo was provided to avoid an infinite loop
        $monthAfter =  (int) date('n', $target);
        if ($monthAfter !== $monthBefore && !$useNextMonthToo) {
            return self::getNextBusinessDayAfter($province, $date, true);
        }

        return $result;
    }

    protected static function findApplicableHolidays(string $province, int $time)
    {
        $month = (int) date('n', $time);
        $year = (int) date('Y', $time);

        if (isset(self::$monthHolidaysCache[$year.$month.$province])) {
            return self::$monthHolidaysCache[$year.$month.$province];
        }

        $monthHolidays = self::findMonthHolidays($time);
        // Find applicable holidays
        $applicable = array_filter($monthHolidays, function ($holiday) use ($province) {
            $applies = reset($holiday['applies']) === 'nationwide' || in_array($province, $holiday['applies']);

            return $applies && !in_array($province, $holiday['except']);
        });

        $result = self::formatApplicableHolidays($applicable, $time);

        self::$monthHolidaysCache[$year.$month.$province] = $result;
        
        return $result;
    }

    protected static function findMonthHolidays(int $time)
    {
        $month = (int) date('n', $time);
        $monthHolidays = self::$sh[$month];
        $easter = easter_date((int) date('Y', $time));

        //we only add good friday as a holiday, since the couriers consider easter monday a business day
        $goodFriday = $easter - (2 * self::SECONDS_PER_DAY);
        if (((int) date('n', $goodFriday)) === $month) {
            $monthHolidays[] = [
                'str' => date('Y-m-d', $goodFriday),
                'applies' => ['nationwide'],
                'except' => [],
                'name' => 'Good Friday',
            ];
        }

        return $monthHolidays;
    }

    protected static function formatApplicableHolidays(array $applicableHolidays, int $target)
    {
        // Create an array of holidays in Y-m-d format
        $holidays = [];
        array_walk($applicableHolidays, function ($holiday) use (&$holidays, $target) {
            $h = strtotime($holiday['str'].' '.date('Y', $target));
            // If the holiday is on a weekend, move it to the following Monday
            $h = self::avoidWeekend($h);
            $holidays[] = date('Y-m-d', $h);
        });

        return $holidays;
    }

    protected static function avoidWeekend(int $time)
    {
        while (in_array(date('l', $time), ['Saturday', 'Sunday'])) {
            $time += self::SECONDS_PER_DAY;
        }

        return $time;
    }

    protected static function getNexMonthHolidays($dateString, $province)
    {
        $dt = new \DateTime($dateString);
        $dt->add(new \DateInterval('P1M'));

        return self::findApplicableHolidays($province, ((int) $dt->format('U')));
    }
}
