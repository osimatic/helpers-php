<?php

declare(strict_types=1);

namespace Tests\Location;

use Osimatic\Location\Continent;
use PHPUnit\Framework\TestCase;

final class ContinentTest extends TestCase
{
	/* ===================== Enum values ===================== */

	public function testEnumValues(): void
	{
		$this->assertSame(1, Continent::EUROPE->value);
		$this->assertSame(2, Continent::MIDDLE_EAST->value);
		$this->assertSame(3, Continent::AFRICA->value);
		$this->assertSame(4, Continent::NORTH_AMERICA->value);
		$this->assertSame(5, Continent::SOUTH_AMERICA->value);
		$this->assertSame(6, Continent::ASIA->value);
		$this->assertSame(7, Continent::OCEANIA->value);
		$this->assertSame(8, Continent::ANTARCTICA->value);
	}

	public function testEnumCases(): void
	{
		$cases = Continent::cases();
		$this->assertCount(8, $cases);
		$this->assertContains(Continent::EUROPE, $cases);
		$this->assertContains(Continent::MIDDLE_EAST, $cases);
		$this->assertContains(Continent::AFRICA, $cases);
		$this->assertContains(Continent::NORTH_AMERICA, $cases);
		$this->assertContains(Continent::SOUTH_AMERICA, $cases);
		$this->assertContains(Continent::ASIA, $cases);
		$this->assertContains(Continent::OCEANIA, $cases);
		$this->assertContains(Continent::ANTARCTICA, $cases);
	}

	/* ===================== getName() ===================== */

	public function testGetName(): void
	{
		$this->assertSame("Europe", Continent::EUROPE->getName());
		$this->assertSame("Moyen-Orient", Continent::MIDDLE_EAST->getName());
		$this->assertSame("Afrique", Continent::AFRICA->getName());
		$this->assertSame("Amérique du Nord", Continent::NORTH_AMERICA->getName());
		$this->assertSame("Amérique du Sud", Continent::SOUTH_AMERICA->getName());
		$this->assertSame("Asie", Continent::ASIA->getName());
		$this->assertSame("Océanie", Continent::OCEANIA->getName());
		$this->assertSame("Antarctique", Continent::ANTARCTICA->getName());
	}

	public function testGetNameNotEmpty(): void
	{
		foreach (Continent::cases() as $continent) {
			$this->assertNotEmpty($continent->getName());
		}
	}

	/* ===================== from() ===================== */

	public function testFromValue(): void
	{
		$this->assertSame(Continent::EUROPE, Continent::from(1));
		$this->assertSame(Continent::MIDDLE_EAST, Continent::from(2));
		$this->assertSame(Continent::AFRICA, Continent::from(3));
		$this->assertSame(Continent::NORTH_AMERICA, Continent::from(4));
		$this->assertSame(Continent::SOUTH_AMERICA, Continent::from(5));
		$this->assertSame(Continent::ASIA, Continent::from(6));
		$this->assertSame(Continent::OCEANIA, Continent::from(7));
		$this->assertSame(Continent::ANTARCTICA, Continent::from(8));
	}

	public function testFromInvalidValue(): void
	{
		$this->expectException(\ValueError::class);
		Continent::from(999);
	}

	public function testTryFromValue(): void
	{
		$this->assertSame(Continent::EUROPE, Continent::tryFrom(1));
		$this->assertSame(Continent::ASIA, Continent::tryFrom(6));
		$this->assertNull(Continent::tryFrom(999));
	}
}