<?php

namespace Tests\Route;

use Osimatic\Route\TransitTravelMode;
use PHPUnit\Framework\TestCase;

class TransitTravelModeTest extends TestCase
{
	// ========== Basic Enum Tests ==========

	public function testEnumCasesExist(): void
	{
		self::assertSame('BUS', TransitTravelMode::BUS->value);
		self::assertSame('SUBWAY', TransitTravelMode::SUBWAY->value);
		self::assertSame('TRAIN', TransitTravelMode::TRAIN->value);
		self::assertSame('LIGHT_RAIL', TransitTravelMode::LIGHT_RAIL->value);
	}

	public function testEnumHasAllCases(): void
	{
		$cases = TransitTravelMode::cases();

		self::assertCount(4, $cases);
		self::assertContains(TransitTravelMode::BUS, $cases);
		self::assertContains(TransitTravelMode::SUBWAY, $cases);
		self::assertContains(TransitTravelMode::TRAIN, $cases);
		self::assertContains(TransitTravelMode::LIGHT_RAIL, $cases);
	}

	// ========== Parse Method Tests - Direct Matches ==========

	public function testParseWithNullReturnsNull(): void
	{
		$result = TransitTravelMode::parse(null);

		self::assertNull($result);
	}

	public function testParseWithBus(): void
	{
		$result = TransitTravelMode::parse('BUS');

		self::assertSame(TransitTravelMode::BUS, $result);
	}

	public function testParseWithSubway(): void
	{
		$result = TransitTravelMode::parse('SUBWAY');

		self::assertSame(TransitTravelMode::SUBWAY, $result);
	}

	public function testParseWithTrain(): void
	{
		$result = TransitTravelMode::parse('TRAIN');

		self::assertSame(TransitTravelMode::TRAIN, $result);
	}

	public function testParseWithLightRail(): void
	{
		$result = TransitTravelMode::parse('LIGHT_RAIL');

		self::assertSame(TransitTravelMode::LIGHT_RAIL, $result);
	}

	// ========== Parse Method Tests - Case Insensitive ==========

	public function testParseIsCaseInsensitive(): void
	{
		self::assertSame(TransitTravelMode::BUS, TransitTravelMode::parse('bus'));
		self::assertSame(TransitTravelMode::BUS, TransitTravelMode::parse('Bus'));
		self::assertSame(TransitTravelMode::BUS, TransitTravelMode::parse('bUs'));
		self::assertSame(TransitTravelMode::SUBWAY, TransitTravelMode::parse('subway'));
		self::assertSame(TransitTravelMode::SUBWAY, TransitTravelMode::parse('Subway'));
		self::assertSame(TransitTravelMode::TRAIN, TransitTravelMode::parse('train'));
		self::assertSame(TransitTravelMode::LIGHT_RAIL, TransitTravelMode::parse('light_rail'));
	}

	// ========== Parse Method Tests - Aliases ==========

	public function testParseWithTramAliasReturnsLightRail(): void
	{
		$result = TransitTravelMode::parse('TRAM');

		self::assertSame(TransitTravelMode::LIGHT_RAIL, $result);
	}

	public function testParseWithLightSubwayAliasReturnsLightRail(): void
	{
		$result = TransitTravelMode::parse('LIGHT_SUBWAY');

		self::assertSame(TransitTravelMode::LIGHT_RAIL, $result);
	}

	public function testParseWithMetroAliasReturnsSubway(): void
	{
		$result = TransitTravelMode::parse('METRO');

		self::assertSame(TransitTravelMode::SUBWAY, $result);
	}

	public function testParseWithRailAliasReturnsTrain(): void
	{
		$result = TransitTravelMode::parse('RAIL');

		self::assertSame(TransitTravelMode::TRAIN, $result);
	}

	// ========== Parse Method Tests - Aliases Case Insensitive ==========

	public function testParseWithAliasesCaseInsensitive(): void
	{
		self::assertSame(TransitTravelMode::LIGHT_RAIL, TransitTravelMode::parse('tram'));
		self::assertSame(TransitTravelMode::LIGHT_RAIL, TransitTravelMode::parse('Tram'));
		self::assertSame(TransitTravelMode::LIGHT_RAIL, TransitTravelMode::parse('light_subway'));
		self::assertSame(TransitTravelMode::SUBWAY, TransitTravelMode::parse('metro'));
		self::assertSame(TransitTravelMode::SUBWAY, TransitTravelMode::parse('Metro'));
		self::assertSame(TransitTravelMode::TRAIN, TransitTravelMode::parse('rail'));
		self::assertSame(TransitTravelMode::TRAIN, TransitTravelMode::parse('Rail'));
	}

	// ========== Parse Method Tests - Invalid Values ==========

	public function testParseWithInvalidStringReturnsNull(): void
	{
		self::assertNull(TransitTravelMode::parse('INVALID'));
		self::assertNull(TransitTravelMode::parse('CAR'));
		self::assertNull(TransitTravelMode::parse('PLANE'));
		self::assertNull(TransitTravelMode::parse(''));
		self::assertNull(TransitTravelMode::parse('   '));
	}

	public function testParseWithRandomStringReturnsNull(): void
	{
		$result = TransitTravelMode::parse('RANDOM_STRING_123');

		self::assertNull($result);
	}

	// ========== Data Provider Tests ==========

	#[\PHPUnit\Framework\Attributes\DataProvider('validTransitModesProvider')]
	public function testParseWithValidModes(string $input, TransitTravelMode $expected): void
	{
		$result = TransitTravelMode::parse($input);

		self::assertSame($expected, $result);
	}

	public static function validTransitModesProvider(): array
	{
		return [
			// Direct matches
			'BUS uppercase' => ['BUS', TransitTravelMode::BUS],
			'SUBWAY uppercase' => ['SUBWAY', TransitTravelMode::SUBWAY],
			'TRAIN uppercase' => ['TRAIN', TransitTravelMode::TRAIN],
			'LIGHT_RAIL uppercase' => ['LIGHT_RAIL', TransitTravelMode::LIGHT_RAIL],

			// Lowercase
			'bus lowercase' => ['bus', TransitTravelMode::BUS],
			'subway lowercase' => ['subway', TransitTravelMode::SUBWAY],
			'train lowercase' => ['train', TransitTravelMode::TRAIN],

			// Mixed case
			'Bus mixed' => ['Bus', TransitTravelMode::BUS],
			'Subway mixed' => ['Subway', TransitTravelMode::SUBWAY],

			// Aliases
			'TRAM alias' => ['TRAM', TransitTravelMode::LIGHT_RAIL],
			'tram lowercase' => ['tram', TransitTravelMode::LIGHT_RAIL],
			'LIGHT_SUBWAY alias' => ['LIGHT_SUBWAY', TransitTravelMode::LIGHT_RAIL],
			'METRO alias' => ['METRO', TransitTravelMode::SUBWAY],
			'metro lowercase' => ['metro', TransitTravelMode::SUBWAY],
			'RAIL alias' => ['RAIL', TransitTravelMode::TRAIN],
			'rail lowercase' => ['rail', TransitTravelMode::TRAIN],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('invalidTransitModesProvider')]
	public function testParseWithInvalidModes(?string $input): void
	{
		$result = TransitTravelMode::parse($input);

		self::assertNull($result);
	}

	public static function invalidTransitModesProvider(): array
	{
		return [
			'null value' => [null],
			'empty string' => [''],
			'whitespace' => ['   '],
			'invalid mode' => ['INVALID'],
			'car' => ['CAR'],
			'walk' => ['WALK'],
			'plane' => ['PLANE'],
			'random string' => ['RANDOM_123'],
			'number' => ['123'],
		];
	}
}