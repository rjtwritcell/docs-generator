<?php

namespace App\Services;

use InvalidArgumentException;

class FinancialYearService
{
    /**
     * Compute and return:
     *  - previousFinancialYear (e.g. '2024-25')
     *  - currentFinancialYear  (e.g. '2025-26')
     *  - currentFinancialYearMonth  (e.g. 'OCT-25')  -> uses the FY start year's last 2 digits
     *  - previousFinancialYearMonth (e.g. 'OCT-24')  -> uses previous FY start year's last 2 digits
     *
     * @param int $year   Calendar year (e.g. 2025)
     * @param string $month Short or full month (e.g. 'OCT' or 'October')
     * @return array
     */
    public function computeFiscalValues(int $year, string $month): array
    {
        $m = $this->monthToNumber($month);

        // current FY start year (Indian FY = Apr(4) - Mar(3))
        $currentFyStart = ($m >= 4) ? $year : ($year - 1);
        $currentFyEnd = $currentFyStart + 1;

        // previous FY start
        $previousFyStart = $currentFyStart - 1;
        $previousFyEnd = $previousFyStart + 1;

        $short = $this->numberToShortMonth($m);

        return [
            'previousFinancialYear'        => $this->formatFy($previousFyStart, $previousFyEnd), // '2024-25'
            'currentFinancialYear'         => $this->formatFy($currentFyStart, $currentFyEnd),   // '2025-26'
            'currentFinancialYearMonth'    => $short . '-' . substr((string)$currentFyStart, -2),   // 'OCT-25'
            'previousFinancialYearMonth'   => $short . '-' . substr((string)$previousFyStart, -2),  // 'OCT-24'
        ];
    }

    // --- minimal helpers ---

    private function monthToNumber(string $month): int
    {
        $map = [
            'JAN'=>1,'FEB'=>2,'MAR'=>3,'APR'=>4,'MAY'=>5,'JUN'=>6,
            'JUL'=>7,'AUG'=>8,'SEP'=>9,'OCT'=>10,'NOV'=>11,'DEC'=>12,
        ];
        return $map[strtoupper(substr(trim($month), 0, 3))];
    }

    private function numberToShortMonth(int $n): string
    {
        $map = [
            1=>'JAN',2=>'FEB',3=>'MAR',4=>'APR',5=>'MAY',6=>'JUN',
            7=>'JUL',8=>'AUG',9=>'SEP',10=>'OCT',11=>'NOV',12=>'DEC',
        ];
        return $map[$n];
    }

    public function fyMonthIndex(string $month): int
    {
        return match (strtoupper($month)) {
            'APR' => 1,
            'MAY' => 2,
            'JUN' => 3,
            'JUL' => 4,
            'AUG' => 5,
            'SEP' => 6,
            'OCT' => 7,
            'NOV' => 8,
            'DEC' => 9,
            'JAN' => 10,
            'FEB' => 11,
            'MAR' => 12,
            default => throw new InvalidArgumentException("Invalid month: $month")
        };
    }

    private function formatFy(int $start, int $end): string
    {
        return $start . '-' . substr((string)$end, -2);
    }
}
