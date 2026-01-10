<?php

declare(strict_types=1);

namespace Tests\Person;

use Osimatic\Person\Gender;
use Osimatic\Person\Name;
use PHPUnit\Framework\TestCase;

final class NameTest extends TestCase
{
	/* ===================== checkCivility() ===================== */

	public function testCheckCivilityValid(): void
	{
		$this->assertTrue(Name::checkCivility(0));
		$this->assertTrue(Name::checkCivility(1));
		$this->assertTrue(Name::checkCivility(2));
		$this->assertTrue(Name::checkCivility('0'));
		$this->assertTrue(Name::checkCivility('1'));
		$this->assertTrue(Name::checkCivility('2'));
	}

	public function testCheckCivilityInvalid(): void
	{
		$this->assertFalse(Name::checkCivility(3));
		$this->assertFalse(Name::checkCivility('3'));
		$this->assertFalse(Name::checkCivility(null));
		$this->assertFalse(Name::checkCivility(''));
	}

	/* ===================== checkFirstName() ===================== */

	public function testCheckFirstNameValid(): void
	{
		$this->assertTrue(Name::checkFirstName('Jean'));
		$this->assertTrue(Name::checkFirstName('Marie'));
		$this->assertTrue(Name::checkFirstName('Jean-Pierre'));
		$this->assertTrue(Name::checkFirstName("O'Connor"));
		$this->assertTrue(Name::checkFirstName('François'));
		$this->assertTrue(Name::checkFirstName('Björk'));
	}

	public function testCheckFirstNameWithNumbers(): void
	{
		$this->assertFalse(Name::checkFirstName('Jean123'));
		$this->assertTrue(Name::checkFirstName('Jean123', numbersAllowed: true));
	}

	public function testCheckFirstNameMinLength(): void
	{
		$this->assertTrue(Name::checkFirstName('Ann'));
		$this->assertFalse(Name::checkFirstName('Jo')); // trop court
	}

	public function testCheckFirstNameMaxLength(): void
	{
		$longName = str_repeat('a', 120);
		$this->assertTrue(Name::checkFirstName($longName));

		$tooLongName = str_repeat('a', 121);
		$this->assertFalse(Name::checkFirstName($tooLongName));
	}

	public function testCheckFirstNameInvalid(): void
	{
		$this->assertFalse(Name::checkFirstName(''));
		$this->assertFalse(Name::checkFirstName('A'));
		$this->assertFalse(Name::checkFirstName('Jean@'));
		$this->assertFalse(Name::checkFirstName(null));
	}

	/* ===================== checkGivenName() ===================== */

	public function testCheckGivenName(): void
	{
		$this->assertTrue(Name::checkGivenName('Jean'));
		$this->assertFalse(Name::checkGivenName('Jo'));
	}

	/* ===================== checkLastName() ===================== */

	public function testCheckLastNameValid(): void
	{
		$this->assertTrue(Name::checkLastName('Dupont'));
		$this->assertTrue(Name::checkLastName('Martin'));
		$this->assertTrue(Name::checkLastName('De La Fontaine'));
		$this->assertTrue(Name::checkLastName("O'Brien"));
		$this->assertTrue(Name::checkLastName('Müller'));
	}

	public function testCheckLastNameWithNumbers(): void
	{
		$this->assertFalse(Name::checkLastName('Smith123'));
		$this->assertTrue(Name::checkLastName('Smith123', numbersAllowed: true));
	}

	public function testCheckLastNameMinLength(): void
	{
		$this->assertTrue(Name::checkLastName('Li'));
		$this->assertFalse(Name::checkLastName('L')); // trop court
	}

	public function testCheckLastNameMaxLength(): void
	{
		$longName = str_repeat('a', 120);
		$this->assertTrue(Name::checkLastName($longName));

		$tooLongName = str_repeat('a', 121);
		$this->assertFalse(Name::checkLastName($tooLongName));
	}

	public function testCheckLastNameInvalid(): void
	{
		$this->assertFalse(Name::checkLastName(''));
		$this->assertFalse(Name::checkLastName('A'));
		$this->assertFalse(Name::checkLastName('Dupont@'));
		$this->assertFalse(Name::checkLastName(null));
	}

	/* ===================== checkFamilyName() ===================== */

	public function testCheckFamilyName(): void
	{
		$this->assertTrue(Name::checkFamilyName('Dupont'));
		$this->assertFalse(Name::checkFamilyName('L'));
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
