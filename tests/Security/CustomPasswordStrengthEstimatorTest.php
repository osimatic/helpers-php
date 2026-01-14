<?php

namespace Tests\Security;

use Osimatic\Security\CustomPasswordStrengthEstimator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Constraints\PasswordStrength;

class CustomPasswordStrengthEstimatorTest extends TestCase
{
	private CustomPasswordStrengthEstimator $estimator;

	protected function setUp(): void
	{
		$this->estimator = new CustomPasswordStrengthEstimator();
	}

	public function testEmptyPasswordIsVeryWeak(): void
	{
		$result = ($this->estimator)('');
		self::assertSame(PasswordStrength::STRENGTH_VERY_WEAK, $result);
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('weakPasswordsProvider')]
	public function testWeakPasswordsAreVeryWeak(string $password): void
	{
		$result = ($this->estimator)($password);
		self::assertSame(PasswordStrength::STRENGTH_VERY_WEAK, $result);
	}

	public static function weakPasswordsProvider(): array
	{
		return [
			['password'],
			['PASSWORD'], // Test case insensitive
			['azerty'],
			['AZERTY'],
			['123456'],
			['qwerty'],
			['QWERTY'],
			['admin'],
			['Admin'],
			['test'],
			['Test'],
			['welcome'],
			['Welcome'],
		];
	}

	public function testVeryWeakPassword(): void
	{
		// Score < 2: only lowercase letters, short
		$result = ($this->estimator)('abc');
		self::assertSame(PasswordStrength::STRENGTH_VERY_WEAK, $result);
	}

	public function testWeakPassword(): void
	{
		// Score 2-3: lowercase + length 8
		$result = ($this->estimator)('abcdefgh');
		self::assertSame(PasswordStrength::STRENGTH_WEAK, $result);
	}

	public function testWeakPasswordWithUpperAndLower(): void
	{
		// Score 3: lowercase + uppercase + length 8
		$result = ($this->estimator)('Abcdefgh');
		self::assertSame(PasswordStrength::STRENGTH_WEAK, $result);
	}

	public function testMediumPassword(): void
	{
		// Score 4: lowercase + uppercase + number + length 8
		$result = ($this->estimator)('Abcdef12');
		self::assertSame(PasswordStrength::STRENGTH_MEDIUM, $result);
	}

	public function testMediumPasswordWithSpecialChar(): void
	{
		// Score 4: lowercase + uppercase + special char + length 8
		$result = ($this->estimator)('Abcdef!@');
		self::assertSame(PasswordStrength::STRENGTH_MEDIUM, $result);
	}

	public function testStrongPassword(): void
	{
		// Score 5: lowercase + uppercase + number + special char + length 8
		$result = ($this->estimator)('Abcd12!@');
		self::assertSame(PasswordStrength::STRENGTH_STRONG, $result);
	}

	public function testVeryStrongPassword(): void
	{
		// Score 6: lowercase + uppercase + number + special char + length 8 + bonus (length >= 12 and score >= 4)
		$result = ($this->estimator)('Abcdef123!@#');
		self::assertSame(PasswordStrength::STRENGTH_VERY_STRONG, $result);
	}

	public function testVeryStrongPasswordWithLongLength(): void
	{
		// Score 6: all criteria met with long password
		$result = ($this->estimator)('MyP@ssw0rd123');
		self::assertSame(PasswordStrength::STRENGTH_VERY_STRONG, $result);
	}

	public function testLongPasswordWithoutBonusCriteria(): void
	{
		// Long password (>= 12) but score < 4 before bonus, so no bonus point
		$result = ($this->estimator)('abcdefghijkl'); // only lowercase + length 8 = score 2
		self::assertSame(PasswordStrength::STRENGTH_WEAK, $result);
	}

	public function testPasswordWithOnlyUppercase(): void
	{
		// Score 2: uppercase + length 8
		$result = ($this->estimator)('ABCDEFGH');
		self::assertSame(PasswordStrength::STRENGTH_WEAK, $result);
	}

	public function testPasswordWithOnlyNumbers(): void
	{
		// Score 2: numbers + length 8
		$result = ($this->estimator)('12345678');
		self::assertSame(PasswordStrength::STRENGTH_WEAK, $result);
	}

	public function testPasswordWithOnlySpecialChars(): void
	{
		// Score 2: special chars + length 8
		$result = ($this->estimator)('!@#$%^&*');
		self::assertSame(PasswordStrength::STRENGTH_WEAK, $result);
	}

	public function testShortPasswordWithAllCriteria(): void
	{
		// Score 4: lowercase + uppercase + number + special char, but length < 8
		$result = ($this->estimator)('Abc1!');
		self::assertSame(PasswordStrength::STRENGTH_MEDIUM, $result);
	}

	public function testPasswordLength7(): void
	{
		// Score 4: lowercase + uppercase + number + special char, length = 7 (no length bonus)
		$result = ($this->estimator)('Abc12!@');
		self::assertSame(PasswordStrength::STRENGTH_MEDIUM, $result);
	}

	public function testPasswordLength8(): void
	{
		// Score 5: lowercase + uppercase + number + special char + length >= 8
		$result = ($this->estimator)('Abc123!@');
		self::assertSame(PasswordStrength::STRENGTH_STRONG, $result);
	}

	public function testPasswordLength12WithScoreUnder4(): void
	{
		// Length >= 12 but score < 4, no bonus
		$result = ($this->estimator)('abcdefghijklm'); // only lowercase + length = score 2
		self::assertSame(PasswordStrength::STRENGTH_WEAK, $result);
	}

	public function testPasswordLength12WithScore4(): void
	{
		// Length >= 12 and score = 4 (before bonus), gets bonus point -> score 5
		$result = ($this->estimator)('Abcd12345678'); // lowercase + uppercase + number + length 8 = 4, +1 bonus = 5
		self::assertSame(PasswordStrength::STRENGTH_STRONG, $result);
	}

	public function testComplexPassword(): void
	{
		// Very complex password with all criteria
		$result = ($this->estimator)('MyC0mpl3x!P@ssw0rd#2024');
		self::assertSame(PasswordStrength::STRENGTH_VERY_STRONG, $result);
	}

	public function testPasswordWithMultipleSpecialChars(): void
	{
		// Test multiple special characters - needs digits to be very strong
		$result = ($this->estimator)('Test123!@#$%^&*()');
		self::assertSame(PasswordStrength::STRENGTH_VERY_STRONG, $result);
	}

	public function testPasswordWithSpaces(): void
	{
		// Spaces count as special characters
		$result = ($this->estimator)('My Pass Word 123');
		self::assertSame(PasswordStrength::STRENGTH_VERY_STRONG, $result);
	}

	public function testPasswordWithAccents(): void
	{
		// Accented characters count as special characters
		$result = ($this->estimator)('Pässwörd123');
		self::assertSame(PasswordStrength::STRENGTH_VERY_STRONG, $result);
	}
}