<?php

namespace Osimatic\Number;

/**
 * Statistical utility class providing methods for data analysis, comparison, and extrapolation.
 *
 * @link https://en.wikipedia.org/wiki/Extrapolation
 */
class Statistics
{
	// ========================================
	// Percentage Change
	// ========================================

	/**
	 * Computes the percentage change between a value and a reference value.
	 *
	 * Returns the rate of change as a percentage and a direction indicator.
	 * A tolerance band of ±$equalityThreshold percent around the reference
	 * is treated as "equal" to avoid flagging statistical noise as a trend.
	 *
	 * Examples:
	 *   computePercentageChange(110, 100)       → ['value' => 10.0,  'direction' => 'up']
	 *   computePercentageChange(90,  100)       → ['value' => -10.0, 'direction' => 'down']
	 *   computePercentageChange(100, 100)       → ['value' => 0.0,   'direction' => 'equal']
	 *   computePercentageChange(101, 100)       → ['value' => 1.0,   'direction' => 'equal'] // within ±1%
	 *   computePercentageChange(50,  0)         → ['value' => 0.0,   'direction' => 'up']
	 *   computePercentageChange(0,   0)         → ['value' => 0.0,   'direction' => 'equal']
	 *
	 * @param float|int $data               The observed value
	 * @param float|int $reference          The reference value to compare against
	 * @param float     $equalityThreshold  Tolerance in percent around the reference defining the "equal" zone (default: 1.0)
	 * @return array{value: float, direction: string} Percentage change value (rounded to 2 decimals) and direction ('up', 'down', or 'equal')
	 * @link https://en.wikipedia.org/wiki/Relative_change
	 */
	public static function computePercentageChange(
		float|int $data,
		float|int $reference,
		float $equalityThreshold = 1.0
	): array
	{
		if ($reference == 0) {
			return [
				'value'     => 0.0,
				'direction' => $data > 0 ? 'up' : 'equal',
			];
		}

		$changeValue  = round((($data - $reference) / $reference) * 100, 2);
		$lowerBound   = $reference - abs($reference) * ($equalityThreshold / 100);
		$upperBound   = $reference + abs($reference) * ($equalityThreshold / 100);

		if ($data < $lowerBound) {
			$direction = 'down';
		} elseif ($data > $upperBound) {
			$direction = 'up';
		} else {
			$direction = 'equal';
		}

		return [
			'value'     => $changeValue,
			'direction' => $direction,
		];
	}

	// ========================================
	// Monthly Extrapolation
	// ========================================

	/**
	 * Extrapolates the monthly total using simple linear projection.
	 *
	 * Assumes each day contributes equally: projects the current cumulative total
	 * across all days of the month proportionally to the number of days elapsed.
	 * Formula: totalSoFar / daysElapsed * daysInMonth
	 *
	 * This method is fast and requires no historical data. It is well-suited for
	 * metrics that are roughly uniform across days (e.g., subscription revenue).
	 * For data with strong day-of-week patterns, prefer extrapolateMonthlyTotal().
	 *
	 * @param float|int               $totalSoFar    Cumulative value observed so far in the month
	 * @param \DateTimeInterface|null $referenceDate The reference date (defaults to today)
	 * @return float The extrapolated total for the month, or 0.0 if no days have elapsed
	 * @link https://en.wikipedia.org/wiki/Extrapolation
	 */
	public static function extrapolateMonthlyLinear(
		float|int $totalSoFar,
		?\DateTimeInterface $referenceDate = null
	): float {
		$referenceDate ??= new \DateTime();
		$daysElapsed  = (int) $referenceDate->format('j');
		$daysInMonth  = (int) $referenceDate->format('t');

		if ($daysElapsed === 0) {
			return 0.0;
		}

		return $totalSoFar / $daysElapsed * $daysInMonth;
	}

	/**
	 * Extrapolates the total value for the current month based on partial daily data.
	 *
	 * The method builds per-day-type (Monday–Sunday) averages from the elapsed days of the month,
	 * then projects those averages onto the remaining days to estimate the monthly total.
	 *
	 * When fewer than 7 days have elapsed in the current month, the method supplements the
	 * current-month data with the fallback period (e.g., the previous month) to ensure
	 * reliable day-type averages across a full 7-day window.
	 *
	 * The $valueAccessor callable makes this method data-structure agnostic:
	 * - Simple scalar array:    fn($item) => $item
	 * - Array of arrays:        fn($item) => $item['count']
	 * - Array of objects:       fn($item) => $item->getValue()
	 *
	 * By default, both arrays must be keyed by SQL date (YYYY-MM-DD).
	 * When $dateAccessor is provided, numerically indexed arrays are accepted instead:
	 * the callable extracts the SQL date from each item and the arrays are re-indexed automatically.
	 * - Array of arrays:  fn($item) => $item['date']
	 * - Array of objects: fn($item) => $item->getDate()
	 *
	 * @param array<string|int, mixed> $valuesByDay         Daily data for the current period, keyed by SQL date or numerically indexed
	 * @param array<string|int, mixed> $fallbackValuesByDay Daily data for the fallback period (e.g., previous month), keyed by SQL date or numerically indexed
	 * @param callable                 $valueAccessor       Extracts a numeric value from a data item: fn(mixed $item): float|int
	 * @param \DateTimeInterface|null  $referenceDate       The reference date for the projection (defaults to today)
	 * @param callable|null            $dateAccessor        Extracts a date from a data item: fn(mixed $item): \DateTimeInterface. Required when arrays are numerically indexed.
	 * @return float The extrapolated total value for the month
	 * @link https://en.wikipedia.org/wiki/Extrapolation
	 */
	public static function extrapolateMonthlyTotal(
		array $valuesByDay,
		array $fallbackValuesByDay,
		callable $valueAccessor,
		?\DateTimeInterface $referenceDate = null,
		?callable $dateAccessor = null
	): float
	{
		$referenceDate ??= new \DateTime();

		if ($dateAccessor !== null) {
			$indexed = [];
			foreach ($valuesByDay as $item) {
				$indexed[$dateAccessor($item)->format('Y-m-d')] = $item;
			}
			$valuesByDay = $indexed;

			$indexedFallback = [];
			foreach ($fallbackValuesByDay as $item) {
				$indexedFallback[$dateAccessor($item)->format('Y-m-d')] = $item;
			}
			$fallbackValuesByDay = $indexedFallback;
		}
		$year  = (int) $referenceDate->format('Y');
		$month = (int) $referenceDate->format('m');
		$day   = (int) $referenceDate->format('d');

		$extrapolatedTotal = 0.0;
		$sumByDayType      = [];  // cumulative value sum per ISO-8601 day type (1=Mon … 7=Sun)
		$countByDayType    = [];  // number of occurrences per ISO-8601 day type

		if ($day <= 7) {
			// Not enough data in the current month yet — use the last 7 days,
			// falling back to the previous period for days before the 1st of this month.
			for ($daysBack = 1; $daysBack <= 7; $daysBack++) {
				$pastDate    = (new \DateTime($referenceDate->format('Y-m-d')))->modify("-{$daysBack} days");
				$pastYear    = (int) $pastDate->format('Y');
				$pastMonth   = (int) $pastDate->format('m');
				$pastSqlDate = $pastDate->format('Y-m-d');
				$dayType     = (int) $pastDate->format('N');

				if ($pastYear === $year && $pastMonth === $month) {
					// Current month: count as actual data and add to the running total
					$value = isset($valuesByDay[$pastSqlDate])
						? (float) $valueAccessor($valuesByDay[$pastSqlDate])
						: 0.0;
					$extrapolatedTotal += $value;
				} else {
					// Previous period: use only for computing the day-type average
					$value = isset($fallbackValuesByDay[$pastSqlDate])
						? (float) $valueAccessor($fallbackValuesByDay[$pastSqlDate])
						: 0.0;
				}

				$sumByDayType[$dayType]   = ($sumByDayType[$dayType] ?? 0.0) + $value;
				$countByDayType[$dayType] = ($countByDayType[$dayType] ?? 0) + 1;
			}
		} else {
			// Enough data: build day-type averages from the elapsed days of the current month
			foreach ($valuesByDay as $sqlDate => $item) {
				$itemDay = (int) substr($sqlDate, 8, 2);
				if ($itemDay >= $day) {
					// Skip today and any future entries
					continue;
				}

				$dayType = (int) date('N', mktime(0, 0, 0, $month, $itemDay, $year));
				$value   = (float) $valueAccessor($item);

				$sumByDayType[$dayType]   = ($sumByDayType[$dayType] ?? 0.0) + $value;
				$countByDayType[$dayType] = ($countByDayType[$dayType] ?? 0) + 1;
				$extrapolatedTotal        += $value;
			}
		}

		// Project the remaining days using the computed per-day-type averages
		$remainingDaysByType = \Osimatic\Calendar\Date::getRemainingDayTypeCountForMonth($referenceDate);

		foreach ($remainingDaysByType as $dayType => $remainingCount) {
			if (!isset($sumByDayType[$dayType]) || $countByDayType[$dayType] === 0) {
				// No reference data available for this day type — skip (conservative estimate)
				continue;
			}
			$average = $sumByDayType[$dayType] / $countByDayType[$dayType];
			$extrapolatedTotal += $average * $remainingCount;
		}

		return $extrapolatedTotal;
	}
}