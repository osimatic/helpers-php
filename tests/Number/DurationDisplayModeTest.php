<?php

namespace Tests\Number;

use Osimatic\Number\DurationDisplayMode;
use PHPUnit\Framework\TestCase;

final class DurationDisplayModeTest extends TestCase
{
	/* ===================== Enum Cases ===================== */

	public function testEnumCases(): void
	{
		// Verify all enum cases exist
		$this->assertEquals('standard', DurationDisplayMode::STANDARD->value);
		$this->assertEquals('chrono', DurationDisplayMode::CHRONO->value);
		$this->assertEquals('input_time', DurationDisplayMode::INPUT_TIME->value);
	}

	/* ===================== Parse Method ===================== */

	public function testParse(): void
	{
		// Valid values (lowercase)
		$this->assertEquals(DurationDisplayMode::STANDARD, DurationDisplayMode::parse('standard'));
		$this->assertEquals(DurationDisplayMode::CHRONO, DurationDisplayMode::parse('chrono'));
		$this->assertEquals(DurationDisplayMode::INPUT_TIME, DurationDisplayMode::parse('input_time'));

		// Valid values (uppercase - should be converted to lowercase)
		$this->assertEquals(DurationDisplayMode::STANDARD, DurationDisplayMode::parse('STANDARD'));
		$this->assertEquals(DurationDisplayMode::CHRONO, DurationDisplayMode::parse('CHRONO'));
		$this->assertEquals(DurationDisplayMode::INPUT_TIME, DurationDisplayMode::parse('INPUT_TIME'));

		// Valid values (mixed case)
		$this->assertEquals(DurationDisplayMode::STANDARD, DurationDisplayMode::parse('Standard'));
		$this->assertEquals(DurationDisplayMode::CHRONO, DurationDisplayMode::parse('Chrono'));
		$this->assertEquals(DurationDisplayMode::INPUT_TIME, DurationDisplayMode::parse('Input_Time'));

		// Invalid values
		$this->assertNull(DurationDisplayMode::parse('invalid'));
		$this->assertNull(DurationDisplayMode::parse(''));
		$this->assertNull(DurationDisplayMode::parse('unknown'));

		// Null value
		$this->assertNull(DurationDisplayMode::parse(null));
	}

	/* ===================== Use in Context ===================== */

	public function testUsageWithDurationFormatting(): void
	{
		// Verify enums can be used with Duration class
		$standard = DurationDisplayMode::STANDARD;
		$this->assertInstanceOf(DurationDisplayMode::class, $standard);

		$chrono = DurationDisplayMode::CHRONO;
		$this->assertInstanceOf(DurationDisplayMode::class, $chrono);

		$inputTime = DurationDisplayMode::INPUT_TIME;
		$this->assertInstanceOf(DurationDisplayMode::class, $inputTime);
	}

	public function testEnumComparison(): void
	{
		// Enum comparison
		$mode1 = DurationDisplayMode::STANDARD;
		$mode2 = DurationDisplayMode::STANDARD;
		$mode3 = DurationDisplayMode::CHRONO;

		$this->assertTrue($mode1 === $mode2);
		$this->assertFalse($mode1 === $mode3);
		$this->assertTrue($mode1 !== $mode3);
	}
}