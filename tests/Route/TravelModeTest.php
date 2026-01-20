<?php

namespace Tests\Route;

use Osimatic\Route\TravelMode;
use PHPUnit\Framework\TestCase;

class TravelModeTest extends TestCase
{
	// ========== Basic Enum Tests ==========

	public function testEnumCasesExist(): void
	{
		self::assertSame('WALK', TravelMode::WALK->value);
		self::assertSame('BICYCLE', TravelMode::BICYCLE->value);
		self::assertSame('DRIVE', TravelMode::DRIVE->value);
		self::assertSame('TWO_WHEELER', TravelMode::TWO_WHEELER->value);
		self::assertSame('TRANSIT', TravelMode::TRANSIT->value);
		self::assertSame('PLANE', TravelMode::PLANE->value);
		self::assertSame('BOAT', TravelMode::BOAT->value);
	}

	public function testEnumHasAllCases(): void
	{
		$cases = TravelMode::cases();

		self::assertCount(7, $cases);
		self::assertContains(TravelMode::WALK, $cases);
		self::assertContains(TravelMode::BICYCLE, $cases);
		self::assertContains(TravelMode::DRIVE, $cases);
		self::assertContains(TravelMode::TWO_WHEELER, $cases);
		self::assertContains(TravelMode::TRANSIT, $cases);
		self::assertContains(TravelMode::PLANE, $cases);
		self::assertContains(TravelMode::BOAT, $cases);
	}

	// ========== tryFrom Tests ==========

	public function testTryFromWithValidValues(): void
	{
		self::assertSame(TravelMode::WALK, TravelMode::tryFrom('WALK'));
		self::assertSame(TravelMode::BICYCLE, TravelMode::tryFrom('BICYCLE'));
		self::assertSame(TravelMode::DRIVE, TravelMode::tryFrom('DRIVE'));
		self::assertSame(TravelMode::TWO_WHEELER, TravelMode::tryFrom('TWO_WHEELER'));
		self::assertSame(TravelMode::TRANSIT, TravelMode::tryFrom('TRANSIT'));
		self::assertSame(TravelMode::PLANE, TravelMode::tryFrom('PLANE'));
		self::assertSame(TravelMode::BOAT, TravelMode::tryFrom('BOAT'));
	}

	public function testTryFromWithInvalidValueReturnsNull(): void
	{
		self::assertNull(TravelMode::tryFrom('INVALID'));
		self::assertNull(TravelMode::tryFrom('walk'));
		self::assertNull(TravelMode::tryFrom('car'));
		self::assertNull(TravelMode::tryFrom(''));
		self::assertNull(TravelMode::tryFrom('RANDOM'));
	}

	// ========== from Tests ==========

	public function testFromWithValidValues(): void
	{
		self::assertSame(TravelMode::WALK, TravelMode::from('WALK'));
		self::assertSame(TravelMode::BICYCLE, TravelMode::from('BICYCLE'));
		self::assertSame(TravelMode::DRIVE, TravelMode::from('DRIVE'));
		self::assertSame(TravelMode::TWO_WHEELER, TravelMode::from('TWO_WHEELER'));
		self::assertSame(TravelMode::TRANSIT, TravelMode::from('TRANSIT'));
		self::assertSame(TravelMode::PLANE, TravelMode::from('PLANE'));
		self::assertSame(TravelMode::BOAT, TravelMode::from('BOAT'));
	}

	public function testFromWithInvalidValueThrowsException(): void
	{
		$this->expectException(\ValueError::class);

		TravelMode::from('INVALID');
	}

	// ========== Enum Properties ==========

	public function testWalkEnumCase(): void
	{
		$mode = TravelMode::WALK;

		self::assertInstanceOf(TravelMode::class, $mode);
		self::assertSame('WALK', $mode->value);
		self::assertSame('WALK', $mode->name);
	}

	public function testBicycleEnumCase(): void
	{
		$mode = TravelMode::BICYCLE;

		self::assertInstanceOf(TravelMode::class, $mode);
		self::assertSame('BICYCLE', $mode->value);
		self::assertSame('BICYCLE', $mode->name);
	}

	public function testDriveEnumCase(): void
	{
		$mode = TravelMode::DRIVE;

		self::assertInstanceOf(TravelMode::class, $mode);
		self::assertSame('DRIVE', $mode->value);
		self::assertSame('DRIVE', $mode->name);
	}

	public function testTwoWheelerEnumCase(): void
	{
		$mode = TravelMode::TWO_WHEELER;

		self::assertInstanceOf(TravelMode::class, $mode);
		self::assertSame('TWO_WHEELER', $mode->value);
		self::assertSame('TWO_WHEELER', $mode->name);
	}

	public function testTransitEnumCase(): void
	{
		$mode = TravelMode::TRANSIT;

		self::assertInstanceOf(TravelMode::class, $mode);
		self::assertSame('TRANSIT', $mode->value);
		self::assertSame('TRANSIT', $mode->name);
	}

	public function testPlaneEnumCase(): void
	{
		$mode = TravelMode::PLANE;

		self::assertInstanceOf(TravelMode::class, $mode);
		self::assertSame('PLANE', $mode->value);
		self::assertSame('PLANE', $mode->name);
	}

	public function testBoatEnumCase(): void
	{
		$mode = TravelMode::BOAT;

		self::assertInstanceOf(TravelMode::class, $mode);
		self::assertSame('BOAT', $mode->value);
		self::assertSame('BOAT', $mode->name);
	}

	// ========== Comparison Tests ==========

	public function testEnumCasesAreComparable(): void
	{
		self::assertTrue(TravelMode::WALK === TravelMode::WALK);
		self::assertFalse(TravelMode::WALK === TravelMode::BICYCLE);
		self::assertFalse(TravelMode::WALK === TravelMode::DRIVE);
	}

	public function testEnumCasesCanBeUsedInMatch(): void
	{
		$mode = TravelMode::DRIVE;

		$result = match ($mode) {
			TravelMode::WALK => 'walking',
			TravelMode::BICYCLE => 'cycling',
			TravelMode::DRIVE => 'driving',
			TravelMode::TWO_WHEELER => 'motorcycle',
			TravelMode::TRANSIT => 'public transport',
			TravelMode::PLANE => 'flying',
			TravelMode::BOAT => 'sailing',
		};

		self::assertSame('driving', $result);
	}

	// ========== Data Provider Tests ==========

	#[\PHPUnit\Framework\Attributes\DataProvider('travelModesProvider')]
	public function testAllTravelModes(string $value, TravelMode $expectedCase): void
	{
		$mode = TravelMode::from($value);

		self::assertSame($expectedCase, $mode);
		self::assertSame($value, $mode->value);
	}

	public static function travelModesProvider(): array
	{
		return [
			'WALK' => ['WALK', TravelMode::WALK],
			'BICYCLE' => ['BICYCLE', TravelMode::BICYCLE],
			'DRIVE' => ['DRIVE', TravelMode::DRIVE],
			'TWO_WHEELER' => ['TWO_WHEELER', TravelMode::TWO_WHEELER],
			'TRANSIT' => ['TRANSIT', TravelMode::TRANSIT],
			'PLANE' => ['PLANE', TravelMode::PLANE],
			'BOAT' => ['BOAT', TravelMode::BOAT],
		];
	}
}