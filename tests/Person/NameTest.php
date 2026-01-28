<?php

declare(strict_types=1);

namespace Tests\Person;

use Osimatic\Person\Gender;
use Osimatic\Person\Name;
use PHPUnit\Framework\TestCase;

final class NameTest extends TestCase
{
	/* ===================== isValidCivility() ===================== */

	public function testIsValidCivilityValid(): void
	{
		$this->assertTrue(Name::isValidCivility(0));
		$this->assertTrue(Name::isValidCivility(1));
		$this->assertTrue(Name::isValidCivility(2));
		$this->assertTrue(Name::isValidCivility('0'));
		$this->assertTrue(Name::isValidCivility('1'));
		$this->assertTrue(Name::isValidCivility('2'));
	}

	public function testIsValidCivilityInvalid(): void
	{
		$this->assertFalse(Name::isValidCivility(3));
		$this->assertFalse(Name::isValidCivility('3'));
		$this->assertFalse(Name::isValidCivility(null));
		$this->assertFalse(Name::isValidCivility(''));
	}

	/* ===================== isValidFirstName() ===================== */

	public function testIsValidFirstNameValid(): void
	{
		$this->assertTrue(Name::isValidFirstName('Jean'));
		$this->assertTrue(Name::isValidFirstName('Marie'));
		$this->assertTrue(Name::isValidFirstName('Jean-Pierre'));
		$this->assertTrue(Name::isValidFirstName("O'Connor"));
		$this->assertTrue(Name::isValidFirstName('François'));
		$this->assertTrue(Name::isValidFirstName('Björk'));
	}

	public function testIsValidFirstNameWithNumbers(): void
	{
		$this->assertFalse(Name::isValidFirstName('Jean123'));
		$this->assertTrue(Name::isValidFirstName('Jean123', numbersAllowed: true));
	}

	public function testIsValidFirstNameMinLength(): void
	{
		$this->assertTrue(Name::isValidFirstName('Ann'));
		$this->assertFalse(Name::isValidFirstName('Jo')); // trop court
	}

	public function testIsValidFirstNameMaxLength(): void
	{
		$longName = str_repeat('a', 120);
		$this->assertTrue(Name::isValidFirstName($longName));

		$tooLongName = str_repeat('a', 121);
		$this->assertFalse(Name::isValidFirstName($tooLongName));
	}

	public function testIsValidFirstNameInvalid(): void
	{
		$this->assertFalse(Name::isValidFirstName(''));
		$this->assertFalse(Name::isValidFirstName('A'));
		$this->assertFalse(Name::isValidFirstName('Jean@'));
		$this->assertFalse(Name::isValidFirstName(null));
	}

	/* ===================== isValidGivenName() ===================== */

	public function testIsValidGivenName(): void
	{
		$this->assertTrue(Name::isValidGivenName('Jean'));
		$this->assertFalse(Name::isValidGivenName('Jo'));
	}

	/* ===================== isValidLastName() ===================== */

	public function testIsValidLastNameValid(): void
	{
		$this->assertTrue(Name::isValidLastName('Dupont'));
		$this->assertTrue(Name::isValidLastName('Martin'));
		$this->assertTrue(Name::isValidLastName('De La Fontaine'));
		$this->assertTrue(Name::isValidLastName("O'Brien"));
		$this->assertTrue(Name::isValidLastName('Müller'));
	}

	public function testIsValidLastNameWithNumbers(): void
	{
		$this->assertFalse(Name::isValidLastName('Smith123'));
		$this->assertTrue(Name::isValidLastName('Smith123', numbersAllowed: true));
	}

	public function testIsValidLastNameMinLength(): void
	{
		$this->assertTrue(Name::isValidLastName('Li'));
		$this->assertFalse(Name::isValidLastName('L')); // trop court
	}

	public function testIsValidLastNameMaxLength(): void
	{
		$longName = str_repeat('a', 120);
		$this->assertTrue(Name::isValidLastName($longName));

		$tooLongName = str_repeat('a', 121);
		$this->assertFalse(Name::isValidLastName($tooLongName));
	}

	public function testIsValidLastNameInvalid(): void
	{
		$this->assertFalse(Name::isValidLastName(''));
		$this->assertFalse(Name::isValidLastName('A'));
		$this->assertFalse(Name::isValidLastName('Dupont@'));
		$this->assertFalse(Name::isValidLastName(null));
	}

	/* ===================== isValidFamilyName() ===================== */

	public function testIsValidFamilyName(): void
	{
		$this->assertTrue(Name::isValidFamilyName('Dupont'));
		$this->assertFalse(Name::isValidFamilyName('L'));
	}

	/* ===================== getFormattedName() ===================== */

	public function testGetFormattedName(): void
	{
		$formatted = Name::getFormattedName(Gender::MALE, 'Jean', 'Dupont');
		$this->assertNotNull($formatted);
		$this->assertIsString($formatted);
	}

	public function testGetFormattedNameWithNullValues(): void
	{
		$formatted = Name::getFormattedName(null, null, null);
		$this->assertIsString($formatted);
	}

	/* ===================== Name object ===================== */

	public function testNameGettersSetters(): void
	{
		$name = new Name();
		$name->setGender(Gender::FEMALE)
			->setFirstName('Marie')
			->setLastName('Curie');

		$this->assertSame(Gender::FEMALE, $name->getGender());
		$this->assertSame('Marie', $name->getFirstName());
		$this->assertSame('Curie', $name->getLastName());
	}

	public function testNameGivenNameFamilyName(): void
	{
		$name = new Name();
		$name->setGivenName('Albert')
			->setFamilyName('Einstein');

		$this->assertSame('Albert', $name->getGivenName());
		$this->assertSame('Einstein', $name->getFamilyName());
	}

	public function testNameDefaultGender(): void
	{
		$name = new Name();
		$this->assertSame(Gender::UNKNOWN, $name->getGender());
	}

	public function testNameFormat(): void
	{
		$name = new Name();
		$name->setGender(Gender::MALE)
			->setFirstName('Pierre')
			->setLastName('Dupont');

		$formatted = $name->format();
		$this->assertIsString($formatted);
	}

	public function testNameToString(): void
	{
		$name = new Name();
		$name->setGender(Gender::FEMALE)
			->setFirstName('Sophie')
			->setLastName('Martin');

		$string = (string) $name;
		$this->assertIsString($string);
		$this->assertNotEmpty($string);
	}

	public function testNameToStringWithNullValues(): void
	{
		$name = new Name();
		$string = (string) $name;
		$this->assertSame('', $string);
	}

	/* ===================== getNameDay() ===================== */

	public function testGetNameDay(): void
	{
		// 25 décembre - Noël
		$nameDay = Name::getNameDay(12, 25, 'FR');
		$this->assertIsString($nameDay);
		$this->assertNotEmpty($nameDay);
	}

	public function testGetNameDayInvalidDate(): void
	{
		$nameDay = Name::getNameDay(13, 32, 'FR');
		$this->assertNull($nameDay);
	}

	/* ===================== getNameDays() ===================== */

	public function testGetNameDays(): void
	{
		// 25 décembre
		$nameDays = Name::getNameDays(12, 25, 'FR');
		$this->assertIsArray($nameDays);
		$this->assertNotEmpty($nameDays);
	}

	public function testGetNameDaysReturnArray(): void
	{
		$nameDays = Name::getNameDays(1, 1, 'FR');
		$this->assertIsArray($nameDays);
	}

	/* ===================== getNameDaysList() ===================== */

	public function testGetNameDaysList(): void
	{
		$list = Name::getNameDaysList('FR');
		$this->assertIsArray($list);
		$this->assertNotEmpty($list);
	}

	public function testGetNameDaysListWithRare(): void
	{
		$listWithoutRare = Name::getNameDaysList('FR', rare: false);
		$listWithRare = Name::getNameDaysList('FR', rare: true);

		$this->assertIsArray($listWithoutRare);
		$this->assertIsArray($listWithRare);
		$this->assertGreaterThanOrEqual(count($listWithoutRare), count($listWithRare));
	}

	public function testGetNameDaysListInvalidCountry(): void
	{
		$list = Name::getNameDaysList('XX');
		$this->assertIsArray($list);
		$this->assertEmpty($list);
	}

	/* ===================== formatFromTwig() ===================== */

	public function testFormatFromTwig(): void
	{
		$name = new Name();
		$name->setGender(Gender::MALE)
			->setFirstName('Jacques')
			->setLastName('Chirac');

		$formatted = Name::formatFromTwig($name);
		$this->assertIsString($formatted);
	}
}
