<?php

namespace Tests\Security;

use Osimatic\Security\GoogleReCaptchaValidator;
use PHPUnit\Framework\TestCase;

class GoogleReCaptchaValidatorTest extends TestCase
{
	private const string TEST_SITE_KEY = 'test-site-key';
	private const string TEST_SECRET = 'test-secret';

	public function testValidatorWithValidResponse(): void
	{
		// Note: This test requires mocking HTTPClient to avoid actual API calls
		// For now, we only test the validator can be instantiated
		$validator = new GoogleReCaptchaValidator(self::TEST_SITE_KEY, self::TEST_SECRET);
		self::assertInstanceOf(GoogleReCaptchaValidator::class, $validator);
	}

	public function testValidatorReturnsOwnInstance(): void
	{
		$validator = new GoogleReCaptchaValidator(self::TEST_SITE_KEY, self::TEST_SECRET);
		self::assertInstanceOf(GoogleReCaptchaValidator::class, $validator);
	}

	public function testValidatorWithNullResponseReturnsFalse(): void
	{
		$validator = new GoogleReCaptchaValidator(self::TEST_SITE_KEY, self::TEST_SECRET);
		$result = $validator(null);
		self::assertFalse($result);
	}

	public function testValidatorWithEmptyResponseReturnsFalse(): void
	{
		$validator = new GoogleReCaptchaValidator(self::TEST_SITE_KEY, self::TEST_SECRET);
		$result = $validator('');
		self::assertFalse($result);
	}

	public function testValidatorCanBeInstantiatedWithoutParameters(): void
	{
		$validator = new GoogleReCaptchaValidator();
		self::assertInstanceOf(GoogleReCaptchaValidator::class, $validator);
	}

	public function testValidatorIsReadonly(): void
	{
		$validator = new GoogleReCaptchaValidator(self::TEST_SITE_KEY, self::TEST_SECRET);
		// Readonly class properties cannot be modified after instantiation
		// This test verifies the class is declared as readonly
		$reflection = new \ReflectionClass($validator);
		self::assertTrue($reflection->isReadOnly());
	}
}