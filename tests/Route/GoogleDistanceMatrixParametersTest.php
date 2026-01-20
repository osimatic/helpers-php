<?php

namespace Tests\Route;

use Osimatic\Route\GoogleDistanceMatrixParameters;
use Osimatic\Route\TransitTravelMode;
use PHPUnit\Framework\TestCase;

class GoogleDistanceMatrixParametersTest extends TestCase
{
	// ========== Constructor and Default Values ==========

	public function testConstructorSetsDefaultValues(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		self::assertSame([], $params->getTransitModes());
		self::assertFalse($params->isAvoidTolls());
		self::assertFalse($params->isAvoidHighways());
		self::assertFalse($params->isAvoidFerries());
		self::assertFalse($params->isAvoidIndoor());
	}

	// ========== Transit Modes Tests ==========

	public function testGetTransitModesReturnsEmptyArrayByDefault(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		$result = $params->getTransitModes();

		self::assertIsArray($result);
		self::assertEmpty($result);
	}

	public function testSetTransitModes(): void
	{
		$params = new GoogleDistanceMatrixParameters();
		$modes = [TransitTravelMode::BUS, TransitTravelMode::SUBWAY];

		$result = $params->setTransitModes($modes);

		self::assertSame($params, $result); // Test fluent interface
		self::assertSame($modes, $params->getTransitModes());
	}

	public function testSetTransitModesWithSingleMode(): void
	{
		$params = new GoogleDistanceMatrixParameters();
		$modes = [TransitTravelMode::TRAIN];

		$params->setTransitModes($modes);

		self::assertSame($modes, $params->getTransitModes());
		self::assertCount(1, $params->getTransitModes());
	}

	public function testSetTransitModesWithAllModes(): void
	{
		$params = new GoogleDistanceMatrixParameters();
		$modes = [
			TransitTravelMode::BUS,
			TransitTravelMode::SUBWAY,
			TransitTravelMode::TRAIN,
			TransitTravelMode::LIGHT_RAIL,
		];

		$params->setTransitModes($modes);

		self::assertSame($modes, $params->getTransitModes());
		self::assertCount(4, $params->getTransitModes());
	}

	public function testSetTransitModesReplacesExistingModes(): void
	{
		$params = new GoogleDistanceMatrixParameters();
		$params->setTransitModes([TransitTravelMode::BUS]);

		$newModes = [TransitTravelMode::SUBWAY, TransitTravelMode::TRAIN];
		$params->setTransitModes($newModes);

		self::assertSame($newModes, $params->getTransitModes());
		self::assertNotContains(TransitTravelMode::BUS, $params->getTransitModes());
	}

	public function testSetTransitModesWithEmptyArray(): void
	{
		$params = new GoogleDistanceMatrixParameters();
		$params->setTransitModes([TransitTravelMode::BUS]);

		$params->setTransitModes([]);

		self::assertSame([], $params->getTransitModes());
	}

	// ========== Avoid Tolls Tests ==========

	public function testIsAvoidTollsDefaultsToFalse(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		self::assertFalse($params->isAvoidTolls());
	}

	public function testSetAvoidTollsToTrue(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		$result = $params->setAvoidTolls(true);

		self::assertSame($params, $result); // Test fluent interface
		self::assertTrue($params->isAvoidTolls());
	}

	public function testSetAvoidTollsToFalse(): void
	{
		$params = new GoogleDistanceMatrixParameters();
		$params->setAvoidTolls(true);

		$params->setAvoidTolls(false);

		self::assertFalse($params->isAvoidTolls());
	}

	public function testAvoidTollsConvenienceMethod(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		$result = $params->avoidTolls();

		self::assertSame($params, $result); // Test fluent interface
		self::assertTrue($params->isAvoidTolls());
	}

	// ========== Avoid Highways Tests ==========

	public function testIsAvoidHighwaysDefaultsToFalse(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		self::assertFalse($params->isAvoidHighways());
	}

	public function testSetAvoidHighwaysToTrue(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		$result = $params->setAvoidHighways(true);

		self::assertSame($params, $result); // Test fluent interface
		self::assertTrue($params->isAvoidHighways());
	}

	public function testSetAvoidHighwaysToFalse(): void
	{
		$params = new GoogleDistanceMatrixParameters();
		$params->setAvoidHighways(true);

		$params->setAvoidHighways(false);

		self::assertFalse($params->isAvoidHighways());
	}

	public function testAvoidHighwaysConvenienceMethod(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		$result = $params->avoidHighways();

		self::assertSame($params, $result); // Test fluent interface
		self::assertTrue($params->isAvoidHighways());
	}

	// ========== Avoid Ferries Tests ==========

	public function testIsAvoidFerriesDefaultsToFalse(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		self::assertFalse($params->isAvoidFerries());
	}

	public function testSetAvoidFerriesToTrue(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		$result = $params->setAvoidFerries(true);

		self::assertSame($params, $result); // Test fluent interface
		self::assertTrue($params->isAvoidFerries());
	}

	public function testSetAvoidFerriesToFalse(): void
	{
		$params = new GoogleDistanceMatrixParameters();
		$params->setAvoidFerries(true);

		$params->setAvoidFerries(false);

		self::assertFalse($params->isAvoidFerries());
	}

	public function testAvoidFerriesConvenienceMethod(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		$result = $params->avoidFerries();

		self::assertSame($params, $result); // Test fluent interface
		self::assertTrue($params->isAvoidFerries());
	}

	// ========== Avoid Indoor Tests ==========

	public function testIsAvoidIndoorDefaultsToFalse(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		self::assertFalse($params->isAvoidIndoor());
	}

	public function testSetAvoidIndoorToTrue(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		$result = $params->setAvoidIndoor(true);

		self::assertSame($params, $result); // Test fluent interface
		self::assertTrue($params->isAvoidIndoor());
	}

	public function testSetAvoidIndoorToFalse(): void
	{
		$params = new GoogleDistanceMatrixParameters();
		$params->setAvoidIndoor(true);

		$params->setAvoidIndoor(false);

		self::assertFalse($params->isAvoidIndoor());
	}

	public function testAvoidIndoorConvenienceMethod(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		$result = $params->avoidIndoor();

		self::assertSame($params, $result); // Test fluent interface
		self::assertTrue($params->isAvoidIndoor());
	}

	// ========== Fluent Interface Tests ==========

	public function testFluentInterfaceChaining(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		$result = $params
			->setTransitModes([TransitTravelMode::BUS, TransitTravelMode::SUBWAY])
			->avoidTolls()
			->avoidHighways()
			->avoidFerries()
			->avoidIndoor();

		self::assertSame($params, $result);
		self::assertCount(2, $params->getTransitModes());
		self::assertTrue($params->isAvoidTolls());
		self::assertTrue($params->isAvoidHighways());
		self::assertTrue($params->isAvoidFerries());
		self::assertTrue($params->isAvoidIndoor());
	}

	public function testFluentInterfaceWithSetters(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		$result = $params
			->setAvoidTolls(true)
			->setAvoidHighways(false)
			->setAvoidFerries(true)
			->setAvoidIndoor(false);

		self::assertSame($params, $result);
		self::assertTrue($params->isAvoidTolls());
		self::assertFalse($params->isAvoidHighways());
		self::assertTrue($params->isAvoidFerries());
		self::assertFalse($params->isAvoidIndoor());
	}

	public function testFluentInterfaceMixedSettersAndConvenience(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		$params
			->setAvoidTolls(false)
			->avoidHighways()
			->setAvoidFerries(false)
			->avoidIndoor();

		self::assertFalse($params->isAvoidTolls());
		self::assertTrue($params->isAvoidHighways());
		self::assertFalse($params->isAvoidFerries());
		self::assertTrue($params->isAvoidIndoor());
	}

	// ========== Multiple Settings Tests ==========

	public function testSetMultipleAvoidOptions(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		$params->avoidTolls()
			   ->avoidHighways()
			   ->avoidFerries();

		self::assertTrue($params->isAvoidTolls());
		self::assertTrue($params->isAvoidHighways());
		self::assertTrue($params->isAvoidFerries());
		self::assertFalse($params->isAvoidIndoor());
	}

	public function testSettingsAreIndependent(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		$params->setAvoidTolls(true);
		self::assertTrue($params->isAvoidTolls());
		self::assertFalse($params->isAvoidHighways());
		self::assertFalse($params->isAvoidFerries());
		self::assertFalse($params->isAvoidIndoor());

		$params->setAvoidHighways(true);
		self::assertTrue($params->isAvoidTolls());
		self::assertTrue($params->isAvoidHighways());
		self::assertFalse($params->isAvoidFerries());
		self::assertFalse($params->isAvoidIndoor());
	}

	// ========== Edge Cases ==========

	public function testCanToggleSettingsMultipleTimes(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		$params->setAvoidTolls(true);
		self::assertTrue($params->isAvoidTolls());

		$params->setAvoidTolls(false);
		self::assertFalse($params->isAvoidTolls());

		$params->avoidTolls();
		self::assertTrue($params->isAvoidTolls());

		$params->setAvoidTolls(false);
		self::assertFalse($params->isAvoidTolls());
	}

	public function testAllOptionsEnabledSimultaneously(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		$params->setTransitModes([TransitTravelMode::BUS])
			   ->avoidTolls()
			   ->avoidHighways()
			   ->avoidFerries()
			   ->avoidIndoor();

		self::assertNotEmpty($params->getTransitModes());
		self::assertTrue($params->isAvoidTolls());
		self::assertTrue($params->isAvoidHighways());
		self::assertTrue($params->isAvoidFerries());
		self::assertTrue($params->isAvoidIndoor());
	}

	public function testComplexConfiguration(): void
	{
		$params = new GoogleDistanceMatrixParameters();

		$params->setTransitModes([
				TransitTravelMode::BUS,
				TransitTravelMode::SUBWAY,
				TransitTravelMode::TRAIN,
			])
			->setAvoidTolls(true)
			->setAvoidHighways(true)
			->setAvoidFerries(false)
			->setAvoidIndoor(true);

		self::assertCount(3, $params->getTransitModes());
		self::assertContains(TransitTravelMode::BUS, $params->getTransitModes());
		self::assertContains(TransitTravelMode::SUBWAY, $params->getTransitModes());
		self::assertContains(TransitTravelMode::TRAIN, $params->getTransitModes());
		self::assertTrue($params->isAvoidTolls());
		self::assertTrue($params->isAvoidHighways());
		self::assertFalse($params->isAvoidFerries());
		self::assertTrue($params->isAvoidIndoor());
	}
}