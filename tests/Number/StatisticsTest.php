<?php

namespace Tests\Number;

use Osimatic\Number\Statistics;
use PHPUnit\Framework\TestCase;

class StatisticsTest extends TestCase
{
	// ========================================
	// Percentage Change Tests
	// ========================================

	public function testComputePercentageChange(): void
	{
		// Standard increase
		$result = Statistics::computePercentageChange(110, 100);
		$this->assertEqualsWithDelta(10.0, $result['value'], 0.001);
		$this->assertEquals('up', $result['direction']);

		// Standard decrease
		$result = Statistics::computePercentageChange(90, 100);
		$this->assertEqualsWithDelta(-10.0, $result['value'], 0.001);
		$this->assertEquals('down', $result['direction']);

		// Exact equality
		$result = Statistics::computePercentageChange(100, 100);
		$this->assertEqualsWithDelta(0.0, $result['value'], 0.001);
		$this->assertEquals('equal', $result['direction']);

		// Within equality threshold (default ±1%): 101 vs 100 → 1% change, treated as equal
		$result = Statistics::computePercentageChange(101, 100);
		$this->assertEqualsWithDelta(1.0, $result['value'], 0.001);
		$this->assertEquals('equal', $result['direction']);

		// Just outside equality threshold: 102 vs 100 → 2% change → up
		$result = Statistics::computePercentageChange(102, 100);
		$this->assertEqualsWithDelta(2.0, $result['value'], 0.001);
		$this->assertEquals('up', $result['direction']);

		// Custom threshold: ±5%
		$result = Statistics::computePercentageChange(104, 100, 5.0);
		$this->assertEqualsWithDelta(4.0, $result['value'], 0.001);
		$this->assertEquals('equal', $result['direction']);

		$result = Statistics::computePercentageChange(106, 100, 5.0);
		$this->assertEqualsWithDelta(6.0, $result['value'], 0.001);
		$this->assertEquals('up', $result['direction']);

		// Reference is 0, data is positive → up
		$result = Statistics::computePercentageChange(50, 0);
		$this->assertEqualsWithDelta(0.0, $result['value'], 0.001);
		$this->assertEquals('up', $result['direction']);

		// Reference is 0, data is also 0 → equal
		$result = Statistics::computePercentageChange(0, 0);
		$this->assertEqualsWithDelta(0.0, $result['value'], 0.001);
		$this->assertEquals('equal', $result['direction']);

		// Negative reference value: -90 vs -100 → ((-90-(-100))/-100)*100 = -10%, but -90 > upperBound(-99) → 'up'
		$result = Statistics::computePercentageChange(-90, -100);
		$this->assertEqualsWithDelta(-10.0, $result['value'], 0.001);
		$this->assertEquals('up', $result['direction']);

		// Large change (rounding check)
		$result = Statistics::computePercentageChange(1, 3);
		$this->assertEqualsWithDelta(-66.67, $result['value'], 0.001);
		$this->assertEquals('down', $result['direction']);
	}

	// ========================================
	// Monthly Extrapolation Tests
	// ========================================

	public function testExtrapolateMonthlyLinear(): void
	{
		// Standard case: Jan 15, 2024 — 1500 total so far
		// 1500 / 15 * 31 = 3100.0
		$result = Statistics::extrapolateMonthlyLinear(1500, new \DateTime('2024-01-15'));
		$this->assertEqualsWithDelta(3100.0, $result, 0.001);

		// Last day of month: Jan 31, 2024 — projection = total itself
		// 3100 / 31 * 31 = 3100.0
		$result = Statistics::extrapolateMonthlyLinear(3100, new \DateTime('2024-01-31'));
		$this->assertEqualsWithDelta(3100.0, $result, 0.001);

		// First day of month: Jan 1, 2024
		// 100 / 1 * 31 = 3100.0
		$result = Statistics::extrapolateMonthlyLinear(100, new \DateTime('2024-01-01'));
		$this->assertEqualsWithDelta(3100.0, $result, 0.001);

		// Leap year February: Feb 14, 2024 (29 days in month)
		// 1400 / 14 * 29 = 2900.0
		$result = Statistics::extrapolateMonthlyLinear(1400, new \DateTime('2024-02-14'));
		$this->assertEqualsWithDelta(2900.0, $result, 0.001);

		// Zero total
		$result = Statistics::extrapolateMonthlyLinear(0, new \DateTime('2024-01-15'));
		$this->assertEqualsWithDelta(0.0, $result, 0.001);

		// Default date (today) — just verify no exception is thrown and result is non-negative
		$result = Statistics::extrapolateMonthlyLinear(1000);
		$this->assertGreaterThanOrEqual(0.0, $result);
	}

	public function testExtrapolateMonthlyTotal(): void
	{
		// ---- Case 1: day > 7, scalar values ----
		// Reference date: January 15, 2024 (Monday, day 15)
		// Jan 1=Mon(1), 2=Tue(2), 3=Wed(3), 4=Thu(4), 5=Fri(5), 6=Sat(6), 7=Sun(7)
		// Jan 8=Mon(1), 9=Tue(2), 10=Wed(3), 11=Thu(4), 12=Fri(5), 13=Sat(6), 14=Sun(7)
		// Days 1–14 are processed (day < 15); day 15 (today) is excluded.
		$valuesByDay = [
			'2024-01-01' => 100, // Mon
			'2024-01-02' => 80,  // Tue
			'2024-01-03' => 90,  // Wed
			'2024-01-04' => 110, // Thu
			'2024-01-05' => 120, // Fri
			'2024-01-06' => 60,  // Sat
			'2024-01-07' => 50,  // Sun
			'2024-01-08' => 105, // Mon
			'2024-01-09' => 85,  // Tue
			'2024-01-10' => 95,  // Wed
			'2024-01-11' => 115, // Thu
			'2024-01-12' => 125, // Fri
			'2024-01-13' => 65,  // Sat
			'2024-01-14' => 55,  // Sun
		];
		// Per-day-type averages:
		//   Mon(1): (100+105)/2 = 102.5 — remaining: Jan 22, 29 = 2  → 205.0
		//   Tue(2): (80+85)/2   = 82.5  — remaining: Jan 16, 23, 30 = 3 → 247.5
		//   Wed(3): (90+95)/2   = 92.5  — remaining: Jan 17, 24, 31 = 3 → 277.5
		//   Thu(4): (110+115)/2 = 112.5 — remaining: Jan 18, 25 = 2  → 225.0
		//   Fri(5): (120+125)/2 = 122.5 — remaining: Jan 19, 26 = 2  → 245.0
		//   Sat(6): (60+65)/2   = 62.5  — remaining: Jan 20, 27 = 2  → 125.0
		//   Sun(7): (50+55)/2   = 52.5  — remaining: Jan 21, 28 = 2  → 105.0
		// Sum actual (Jan 1–14): 1255.0
		// Sum projected (Jan 16–31): 205+247.5+277.5+225+245+125+105 = 1430.0
		// Expected total: 1255.0 + 1430.0 = 2685.0
		$result = Statistics::extrapolateMonthlyTotal(
			$valuesByDay,
			[],
			fn($v) => $v,
			new \DateTime('2024-01-15')
		);
		$this->assertEqualsWithDelta(2685.0, $result, 0.001);

		// ---- Case 2: day <= 7, fallback data needed ----
		// Reference date: January 3, 2024 (Wednesday, day 3)
		// 7-day window: Jan 2 (Tue/2), Jan 1 (Mon/1), Dec 31 (Sun/7),
		//               Dec 30 (Sat/6), Dec 29 (Fri/5), Dec 28 (Thu/4), Dec 27 (Wed/3)
		// Current-month days (Jan 1, Jan 2) are added to extrapolatedTotal.
		// Previous-month days (Dec 27–31) are used only for day-type averages.
		$currentMonthValues = [
			'2024-01-01' => 100, // Mon — actual, added to total
			'2024-01-02' => 80,  // Tue — actual, added to total
		];
		$fallbackValues = [
			'2023-12-27' => 90,  // Wed(3)
			'2023-12-28' => 110, // Thu(4)
			'2023-12-29' => 120, // Fri(5)
			'2023-12-30' => 60,  // Sat(6)
			'2023-12-31' => 50,  // Sun(7)
		];
		// extrapolatedTotal after loop: 100 + 80 = 180
		// averages (count=1 each): Mon=100, Tue=80, Wed=90, Thu=110, Fri=120, Sat=60, Sun=50
		// Remaining days after Jan 3 (Jan 4–31 = 28 days), each day type appears exactly 4 times:
		//   Mon(1) × 4 = 400, Tue(2) × 4 = 320, Wed(3) × 4 = 360,
		//   Thu(4) × 4 = 440, Fri(5) × 4 = 480, Sat(6) × 4 = 240, Sun(7) × 4 = 200
		// Sum projected: 400+320+360+440+480+240+200 = 2440
		// Expected total: 180 + 2440 = 2620.0
		$result = Statistics::extrapolateMonthlyTotal(
			$currentMonthValues,
			$fallbackValues,
			fn($v) => $v,
			new \DateTime('2024-01-03')
		);
		$this->assertEqualsWithDelta(2620.0, $result, 0.001);

		// ---- Case 3: callable on objects ----
		// Same data as Case 1 but wrapped in stdClass objects.
		$objectValuesByDay = [];
		foreach ($valuesByDay as $date => $val) {
			$obj = new \stdClass();
			$obj->count = $val;
			$objectValuesByDay[$date] = $obj;
		}
		$result = Statistics::extrapolateMonthlyTotal(
			$objectValuesByDay,
			[],
			fn($item) => $item->count,
			new \DateTime('2024-01-15')
		);
		$this->assertEqualsWithDelta(2685.0, $result, 0.001);

		// ---- Case 4: empty data arrays (day > 7) ----
		// No data → no averages → no projection → result is 0.
		$result = Statistics::extrapolateMonthlyTotal(
			[],
			[],
			fn($v) => $v,
			new \DateTime('2024-01-15')
		);
		$this->assertEqualsWithDelta(0.0, $result, 0.001);

		// ---- Case 5: last day of month ----
		// Reference date: January 31, 2024 (Wednesday).
		// All days 1–30 have value 100 (processed since itemDay < 31).
		// No remaining days → no projection.
		// Expected: 30 × 100 = 3000.0
		$lastDayValues = [];
		for ($d = 1; $d <= 30; $d++) {
			$lastDayValues[sprintf('2024-01-%02d', $d)] = 100;
		}
		$result = Statistics::extrapolateMonthlyTotal(
			$lastDayValues,
			[],
			fn($v) => $v,
			new \DateTime('2024-01-31')
		);
		$this->assertEqualsWithDelta(3000.0, $result, 0.001);

		// ---- Case 6: missing day-type data (gap in averages) ----
		// Reference date: January 15, 2024.
		// Only Monday and Tuesday have data — other day types are skipped in the projection.
		$partialValues = [
			'2024-01-01' => 100, // Mon
			'2024-01-02' => 80,  // Tue
			'2024-01-08' => 105, // Mon
			'2024-01-09' => 85,  // Tue
		];
		// Actual sum: 100+80+105+85 = 370
		// Averages: Mon(1)=102.5, Tue(2)=82.5 — all other day types skipped
		// Projected: Mon(1) × 2 = 205, Tue(2) × 3 = 247.5 → 452.5
		// Expected: 370 + 452.5 = 822.5
		$result = Statistics::extrapolateMonthlyTotal(
			$partialValues,
			[],
			fn($v) => $v,
			new \DateTime('2024-01-15')
		);
		$this->assertEqualsWithDelta(822.5, $result, 0.001);

		// ---- Case 7: numerically indexed array of objects with $dateAccessor ----
		// Same data as Case 1 but as a numerically indexed array of objects with a date property.
		// Expected result: same 2685.0
		$indexedObjects = [];
		foreach ($valuesByDay as $sqlDate => $val) {
			$obj = new \stdClass();
			$obj->date  = new \DateTime($sqlDate);
			$obj->count = $val;
			$indexedObjects[] = $obj;
		}
		$result = Statistics::extrapolateMonthlyTotal(
			$indexedObjects,
			[],
			fn($item) => $item->count,
			new \DateTime('2024-01-15'),
			fn($item) => $item->date
		);
		$this->assertEqualsWithDelta(2685.0, $result, 0.001);
	}
}