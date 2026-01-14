<?php

namespace Tests\Person;

use Osimatic\Person\Gender;
use Osimatic\Person\Name;
use Osimatic\Person\NameFormatter;
use PHPUnit\Framework\TestCase;

class NameFormatterTest extends TestCase
{
	private NameFormatter $formatter;

	protected function setUp(): void
	{
		$this->formatter = new NameFormatter();
	}

	// ========== formatTitle Tests ==========

	public function testFormatTitleReturnsEmptyString(): void
	{
		self::assertSame('', $this->formatter->formatTitle(null));
		self::assertSame('', $this->formatter->formatTitle(1));
		self::assertSame('', $this->formatter->formatTitle(2));
	}

	// ========== formatFirstName Tests ==========

	public function testFormatFirstNameWithNull(): void
	{
		self::assertSame('', $this->formatter->formatFirstName(null));
	}

	public function testFormatFirstNameWithEmptyString(): void
	{
		self::assertSame('', $this->formatter->formatFirstName(''));
	}

	public function testFormatFirstNameWithUcName(): void
	{
		self::assertSame('John', $this->formatter->formatFirstName('john'));
		self::assertSame('John', $this->formatter->formatFirstName('JOHN'));
		self::assertSame('John', $this->formatter->formatFirstName('John'));
	}

	public function testFormatFirstNameWithoutUcName(): void
	{
		self::assertSame('john', $this->formatter->formatFirstName('john', false));
		self::assertSame('JOHN', $this->formatter->formatFirstName('JOHN', false));
		self::assertSame('John', $this->formatter->formatFirstName('John', false));
	}

	public function testFormatFirstNameTrimsSpaces(): void
	{
		self::assertSame('John', $this->formatter->formatFirstName('  john  '));
		self::assertSame('john', $this->formatter->formatFirstName('  john  ', false));
	}

	public function testFormatFirstNameWithHyphen(): void
	{
		self::assertSame('Jean-Pierre', $this->formatter->formatFirstName('jean-pierre'));
		self::assertSame('Jean-Pierre', $this->formatter->formatFirstName('JEAN-PIERRE'));
	}

	public function testFormatFirstNameWithApostrophe(): void
	{
		self::assertSame('D\'Angelo', $this->formatter->formatFirstName('d\'angelo'));
		self::assertSame('D\'Angelo', $this->formatter->formatFirstName('D\'ANGELO'));
	}

	public function testFormatFirstNameWithAccents(): void
	{
		self::assertSame('Jérôme', $this->formatter->formatFirstName('jérôme'));
		self::assertSame('Élodie', $this->formatter->formatFirstName('ÉLODIE'));
	}

	// ========== formatLastName Tests ==========

	public function testFormatLastNameWithNull(): void
	{
		self::assertSame('', $this->formatter->formatLastName(null));
	}

	public function testFormatLastNameWithEmptyString(): void
	{
		self::assertSame('', $this->formatter->formatLastName(''));
	}

	public function testFormatLastNameWithUpperCase(): void
	{
		self::assertSame('SMITH', $this->formatter->formatLastName('smith'));
		self::assertSame('SMITH', $this->formatter->formatLastName('Smith'));
		self::assertSame('SMITH', $this->formatter->formatLastName('SMITH'));
	}

	public function testFormatLastNameWithoutUpperCase(): void
	{
		self::assertSame('smith', $this->formatter->formatLastName('smith', false));
		self::assertSame('Smith', $this->formatter->formatLastName('Smith', false));
		self::assertSame('SMITH', $this->formatter->formatLastName('SMITH', false));
	}

	public function testFormatLastNameTrimsSpaces(): void
	{
		self::assertSame('SMITH', $this->formatter->formatLastName('  smith  '));
		self::assertSame('smith', $this->formatter->formatLastName('  smith  ', false));
	}

	public function testFormatLastNameWithAccents(): void
	{
		self::assertSame('DUPRÉ', $this->formatter->formatLastName('dupré'));
		self::assertSame('MÜLLER', $this->formatter->formatLastName('müller'));
	}

	public function testFormatLastNameWithHyphen(): void
	{
		self::assertSame('MARTIN-DUBOIS', $this->formatter->formatLastName('martin-dubois'));
	}

	// ========== format Tests ==========

	public function testFormatWithFullName(): void
	{
		$name = (new Name())
			->setFirstName('john')
			->setLastName('smith');

		self::assertSame('John SMITH', $this->formatter->format($name));
	}

	public function testFormatWithFullNameWithoutEditCase(): void
	{
		$name = (new Name())
			->setFirstName('john')
			->setLastName('smith');

		self::assertSame('john smith', $this->formatter->format($name, false));
	}

	public function testFormatWithOnlyFirstName(): void
	{
		$name = (new Name())
			->setFirstName('john');

		self::assertSame('John', $this->formatter->format($name));
	}

	public function testFormatWithOnlyLastName(): void
	{
		$name = (new Name())
			->setLastName('smith');

		self::assertSame('SMITH', $this->formatter->format($name));
	}

	public function testFormatWithEmptyName(): void
	{
		$name = new Name();

		self::assertSame('', $this->formatter->format($name));
	}

	public function testFormatWithNullValues(): void
	{
		$name = (new Name())
			->setFirstName(null)
			->setLastName(null);

		self::assertSame('', $this->formatter->format($name));
	}

	public function testFormatWithSpacesInNames(): void
	{
		$name = (new Name())
			->setFirstName('  john  ')
			->setLastName('  smith  ');

		self::assertSame('John SMITH', $this->formatter->format($name));
	}

	public function testFormatWithHyphenatedNames(): void
	{
		$name = (new Name())
			->setFirstName('jean-pierre')
			->setLastName('martin-dubois');

		self::assertSame('Jean-Pierre MARTIN-DUBOIS', $this->formatter->format($name));
	}

	public function testFormatWithApostrophe(): void
	{
		$name = (new Name())
			->setFirstName('d\'angelo')
			->setLastName('o\'brien');

		self::assertSame('D\'Angelo O\'BRIEN', $this->formatter->format($name));
	}

	public function testFormatWithAccents(): void
	{
		$name = (new Name())
			->setFirstName('jérôme')
			->setLastName('dupré');

		self::assertSame('Jérôme DUPRÉ', $this->formatter->format($name));
	}

	public function testFormatWithGender(): void
	{
		$name = (new Name())
			->setGender(Gender::MALE)
			->setFirstName('john')
			->setLastName('smith');

		self::assertSame('John SMITH', $this->formatter->format($name));
	}

	// ========== ucname Tests ==========

	public function testUcnameWithNull(): void
	{
		self::assertSame('', NameFormatter::ucname(null));
	}

	public function testUcnameWithSimpleName(): void
	{
		self::assertSame('John', NameFormatter::ucname('john'));
		self::assertSame('John', NameFormatter::ucname('JOHN'));
		self::assertSame('John', NameFormatter::ucname('John'));
	}

	public function testUcnameWithMultipleWords(): void
	{
		self::assertSame('John Smith', NameFormatter::ucname('john smith'));
		self::assertSame('John Smith', NameFormatter::ucname('JOHN SMITH'));
	}

	public function testUcnameWithHyphen(): void
	{
		self::assertSame('Jean-Pierre', NameFormatter::ucname('jean-pierre'));
		self::assertSame('Jean-Pierre', NameFormatter::ucname('JEAN-PIERRE'));
		self::assertSame('Marie-Claude', NameFormatter::ucname('marie-claude'));
	}

	public function testUcnameWithApostrophe(): void
	{
		self::assertSame('D\'Angelo', NameFormatter::ucname('d\'angelo'));
		self::assertSame('D\'Angelo', NameFormatter::ucname('D\'ANGELO'));
		self::assertSame('O\'Brien', NameFormatter::ucname('o\'brien'));
	}

	public function testUcnameWithHyphenAndApostrophe(): void
	{
		self::assertSame('Jean-D\'Arc', NameFormatter::ucname('jean-d\'arc'));
		self::assertSame('Jean-D\'Arc', NameFormatter::ucname('JEAN-D\'ARC'));
	}

	public function testUcnameWithAccents(): void
	{
		self::assertSame('Jérôme', NameFormatter::ucname('jérôme'));
		self::assertSame('Jérôme', NameFormatter::ucname('JÉRÔME'));
		self::assertSame('Élodie', NameFormatter::ucname('élodie'));
	}

	public function testUcnameWithAccentsAndHyphen(): void
	{
		self::assertSame('José-Maria', NameFormatter::ucname('josé-maria'));
		self::assertSame('José-Maria', NameFormatter::ucname('JOSÉ-MARIA'));
	}

	public function testUcnameWithEmptyString(): void
	{
		self::assertSame('', NameFormatter::ucname(''));
	}

	public function testUcnameWithOnlySpaces(): void
	{
		self::assertSame('   ', NameFormatter::ucname('   '));
	}

	public function testUcnameComplexCases(): void
	{
		self::assertSame('Anne-Marie-Louise', NameFormatter::ucname('anne-marie-louise'));
		self::assertSame('Pierre-Jean-François', NameFormatter::ucname('PIERRE-JEAN-FRANÇOIS'));
		self::assertSame('D\'Artagnan', NameFormatter::ucname('d\'artagnan'));
	}
}