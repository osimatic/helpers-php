<?php

declare(strict_types=1);

namespace Tests\Data;

use Osimatic\Data\FormService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

// Enums de test
enum TestStatus: string
{
	case ACTIVE = 'ACTIVE';
	case INACTIVE = 'INACTIVE';
	case PENDING = 'PENDING';
}

enum TestStatusLower: string
{
	case ACTIVE = 'active';
	case INACTIVE = 'inactive';
	case PENDING = 'pending';
}

final class FormServiceTest extends TestCase
{
	/* ===================== trim() ===================== */

	public function testTrimWithNullValue(): void
	{
		$this->assertNull(FormService::trim(null));
	}

	public function testTrimWithWhitespace(): void
	{
		$this->assertSame('test', FormService::trim('  test  '));
		$this->assertSame('test', FormService::trim("\ttest\n"));
		$this->assertSame('test', FormService::trim("\r\ntest\r\n"));
	}

	public function testTrimWithZeroCharacter(): void
	{
		// Par défaut, deleteZero=true, donc trim normal
		$this->assertSame('test', FormService::trim("\0test\0"));
	}

	public function testTrimWithoutDeletingZero(): void
	{
		// Avec deleteZero=false, le caractère nul \0 n'est pas supprimé
		$this->assertSame("\0test\0", FormService::trim("\0test\0", false));
		$this->assertSame('test', FormService::trim("  test  ", false));
	}

	public function testTrimWithEmptyString(): void
	{
		$this->assertSame('', FormService::trim(''));
		$this->assertSame('', FormService::trim('   '));
	}

	public function testTrimWithReturnNullIfEmptyTrue(): void
	{
		$this->assertNull(FormService::trim('', true, true));
		$this->assertNull(FormService::trim('   ', true, true));
		$this->assertNull(FormService::trim("\t\n\r", true, true));
	}

	public function testTrimWithReturnNullIfEmptyFalse(): void
	{
		$this->assertSame('', FormService::trim('', true, false));
		$this->assertSame('', FormService::trim('   ', true, false));
	}

	public function testTrimWithReturnNullIfEmptyButNonEmpty(): void
	{
		$this->assertSame('test', FormService::trim('  test  ', true, true));
		$this->assertSame('hello', FormService::trim('hello', true, true));
	}

	/* ===================== parseArray() ===================== */

	public function testParseArrayWithNull(): void
	{
		$this->assertSame([], FormService::parseArray(null));
	}

	public function testParseArrayWithArray(): void
	{
		$this->assertSame([1, 2, 3], FormService::parseArray([1, 2, 3]));
	}

	public function testParseArrayWithArrayFilteringEmpty(): void
	{
		$this->assertSame([1, 2, 3], FormService::parseArray([1, '', 2, null, 3, 0]));
	}

	public function testParseArrayWithoutFiltering(): void
	{
		$result = FormService::parseArray([1, '', 2, null, 3, 0], false);
		$this->assertCount(6, $result);
		$this->assertSame([1, '', 2, null, 3, 0], $result);
	}

	public function testParseArrayWithString(): void
	{
		$this->assertSame(['test'], FormService::parseArray('test'));
	}

	public function testParseArrayWithStringAndSeparator(): void
	{
		$this->assertSame(['a', 'b', 'c'], FormService::parseArray('a,b,c', true, ','));
		$this->assertSame(['foo', 'bar'], FormService::parseArray('foo|bar', true, '|'));
	}

	public function testParseArrayWithStringAndSeparatorNoFiltering(): void
	{
		$result = FormService::parseArray('a,,c', false, ',');
		$this->assertSame(['a', '', 'c'], $result);
	}

	public function testParseArrayWithNumber(): void
	{
		$this->assertSame([123], FormService::parseArray(123));
	}

	public function testParseArrayWithDoSProtection(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Array size (10001) exceeds maximum allowed size (10000)');

		$largeArray = range(1, 10001);
		FormService::parseArray($largeArray);
	}

	public function testParseArrayWithCustomMaxSize(): void
	{
		$smallArray = range(1, 5);
		$result = FormService::parseArray($smallArray, true, null, 10);
		$this->assertCount(5, $result);
	}

	public function testParseArrayExceedsCustomMaxSize(): void
	{
		$this->expectException(\InvalidArgumentException::class);

		$array = range(1, 11);
		FormService::parseArray($array, true, null, 10);
	}

	public function testParseArrayWithUnlimitedSize(): void
	{
		$largeArray = range(1, 15000);
		$result = FormService::parseArray($largeArray, true, null, 0);
		$this->assertCount(15000, $result);
	}

	/* ===================== parseBoolean() ===================== */

	public function testParseBooleanWithNull(): void
	{
		$this->assertNull(FormService::parseBoolean(null));
	}

	public function testParseBooleanWithBooleanTrue(): void
	{
		$this->assertTrue(FormService::parseBoolean(true));
	}

	public function testParseBooleanWithBooleanFalse(): void
	{
		$this->assertFalse(FormService::parseBoolean(false));
	}

	public function testParseBooleanWithStringTrue(): void
	{
		$this->assertTrue(FormService::parseBoolean('true'));
		$this->assertTrue(FormService::parseBoolean('TRUE'));
		$this->assertTrue(FormService::parseBoolean('1'));
		$this->assertTrue(FormService::parseBoolean('yes'));
		$this->assertTrue(FormService::parseBoolean('YES'));
		$this->assertTrue(FormService::parseBoolean('on'));
		$this->assertTrue(FormService::parseBoolean('ON'));
	}

	public function testParseBooleanWithStringFalse(): void
	{
		$this->assertFalse(FormService::parseBoolean('false'));
		$this->assertFalse(FormService::parseBoolean('FALSE'));
		$this->assertFalse(FormService::parseBoolean('0'));
		$this->assertFalse(FormService::parseBoolean('no'));
		$this->assertFalse(FormService::parseBoolean('NO'));
		$this->assertFalse(FormService::parseBoolean('off'));
		$this->assertFalse(FormService::parseBoolean('OFF'));
		$this->assertFalse(FormService::parseBoolean(''));
	}

	public function testParseBooleanWithInteger(): void
	{
		$this->assertTrue(FormService::parseBoolean(1));
		$this->assertFalse(FormService::parseBoolean(0));
		$this->assertNull(FormService::parseBoolean(42)); // Invalid integer returns null
		$this->assertNull(FormService::parseBoolean(2)); // Invalid integer returns null
		$this->assertNull(FormService::parseBoolean(-1)); // Invalid integer returns null
	}

	public function testParseBooleanWithWhitespace(): void
	{
		$this->assertTrue(FormService::parseBoolean('  true  '));
		$this->assertFalse(FormService::parseBoolean('  false  '));
	}

	/* ===================== parseInteger() ===================== */

	public function testParseIntegerWithNull(): void
	{
		$this->assertNull(FormService::parseInteger(null));
	}

	public function testParseIntegerWithEmptyString(): void
	{
		$this->assertNull(FormService::parseInteger(''));
	}

	public function testParseIntegerWithValidString(): void
	{
		$this->assertSame(42, FormService::parseInteger('42'));
		$this->assertSame(-10, FormService::parseInteger('-10'));
		$this->assertSame(0, FormService::parseInteger('0'));
	}

	public function testParseIntegerWithValidInteger(): void
	{
		$this->assertSame(42, FormService::parseInteger(42));
		$this->assertSame(0, FormService::parseInteger(0));
	}

	public function testParseIntegerWithInvalidString(): void
	{
		$this->assertNull(FormService::parseInteger('abc'));
		$this->assertNull(FormService::parseInteger('12abc'));
	}

	public function testParseIntegerWithMinValidation(): void
	{
		$this->assertSame(50, FormService::parseInteger(50, 0, null));
		$this->assertSame(0, FormService::parseInteger(0, 0, null));
		$this->assertNull(FormService::parseInteger(-5, 0, null));
	}

	public function testParseIntegerWithMaxValidation(): void
	{
		$this->assertSame(50, FormService::parseInteger(50, null, 100));
		$this->assertSame(100, FormService::parseInteger(100, null, 100));
		$this->assertNull(FormService::parseInteger(150, null, 100));
	}

	public function testParseIntegerWithRangeValidation(): void
	{
		$this->assertSame(50, FormService::parseInteger(50, 0, 100));
		$this->assertSame(0, FormService::parseInteger(0, 0, 100));
		$this->assertSame(100, FormService::parseInteger(100, 0, 100));
		$this->assertNull(FormService::parseInteger(-1, 0, 100));
		$this->assertNull(FormService::parseInteger(101, 0, 100));
	}

	/* ===================== parseFloat() ===================== */

	public function testParseFloatWithNull(): void
	{
		$this->assertNull(FormService::parseFloat(null));
	}

	public function testParseFloatWithEmptyString(): void
	{
		$this->assertNull(FormService::parseFloat(''));
	}

	public function testParseFloatWithValidString(): void
	{
		$this->assertSame(42.5, FormService::parseFloat('42.5'));
		$this->assertSame(-10.75, FormService::parseFloat('-10.75'));
		$this->assertSame(0.0, FormService::parseFloat('0.0'));
	}

	public function testParseFloatWithValidFloat(): void
	{
		$this->assertSame(42.5, FormService::parseFloat(42.5));
		$this->assertSame(0.0, FormService::parseFloat(0.0));
	}

	public function testParseFloatWithInteger(): void
	{
		$this->assertSame(42.0, FormService::parseFloat(42));
	}

	public function testParseFloatWithInvalidString(): void
	{
		$this->assertNull(FormService::parseFloat('abc'));
		$this->assertNull(FormService::parseFloat('12.5abc'));
	}

	public function testParseFloatWithMinValidation(): void
	{
		$this->assertSame(50.5, FormService::parseFloat(50.5, 0.0, null));
		$this->assertSame(0.0, FormService::parseFloat(0.0, 0.0, null));
		$this->assertNull(FormService::parseFloat(-5.5, 0.0, null));
	}

	public function testParseFloatWithMaxValidation(): void
	{
		$this->assertSame(50.5, FormService::parseFloat(50.5, null, 100.0));
		$this->assertSame(100.0, FormService::parseFloat(100.0, null, 100.0));
		$this->assertNull(FormService::parseFloat(150.5, null, 100.0));
	}

	public function testParseFloatWithRangeValidation(): void
	{
		$this->assertSame(50.5, FormService::parseFloat(50.5, 0.0, 100.0));
		$this->assertSame(0.0, FormService::parseFloat(0.0, 0.0, 100.0));
		$this->assertSame(100.0, FormService::parseFloat(100.0, 0.0, 100.0));
		$this->assertNull(FormService::parseFloat(-0.1, 0.0, 100.0));
		$this->assertNull(FormService::parseFloat(100.1, 0.0, 100.0));
	}

	/* ===================== sanitizeHtml() ===================== */

	public function testSanitizeHtmlWithNull(): void
	{
		$this->assertNull(FormService::sanitizeHtml(null));
	}

	public function testSanitizeHtmlStripsAllTags(): void
	{
		$this->assertSame('Hello World', FormService::sanitizeHtml('<p>Hello World</p>'));
		$this->assertSame('Hello', FormService::sanitizeHtml('<script>alert("xss")</script>Hello'));
		$this->assertSame('Test', FormService::sanitizeHtml('<div><span>Test</span></div>'));
	}

	public function testSanitizeHtmlWithoutTags(): void
	{
		$this->assertSame('Hello World', FormService::sanitizeHtml('Hello World'));
		$this->assertSame('', FormService::sanitizeHtml(''));
	}

	public function testSanitizeHtmlAllowBasicFormatting(): void
	{
		$this->assertSame('<b>Bold</b>', FormService::sanitizeHtml('<b>Bold</b>', true));
		$this->assertSame('<i>Italic</i>', FormService::sanitizeHtml('<i>Italic</i>', true));
		$this->assertSame('<strong>Strong</strong>', FormService::sanitizeHtml('<strong>Strong</strong>', true));
		$this->assertSame('<em>Emphasis</em>', FormService::sanitizeHtml('<em>Emphasis</em>', true));
		$this->assertSame('<u>Underline</u>', FormService::sanitizeHtml('<u>Underline</u>', true));
		$this->assertSame('Line1<br>Line2', FormService::sanitizeHtml('Line1<br>Line2', true));
		$this->assertSame('<p>Paragraph</p>', FormService::sanitizeHtml('<p>Paragraph</p>', true));
	}

	public function testSanitizeHtmlRemovesDangerousTagsEvenWithFormatting(): void
	{
		$this->assertSame('<b>Hello</b> ', FormService::sanitizeHtml('<b>Hello</b> <script>alert("xss")</script>', true));
		$this->assertSame('<p>Test</p>', FormService::sanitizeHtml('<p>Test</p><iframe src="evil"></iframe>', true));
	}

	public function testSanitizeHtmlRemovesNonAllowedFormattingTags(): void
	{
		$this->assertSame('Link', FormService::sanitizeHtml('<a href="#">Link</a>', true));
		$this->assertSame('Div', FormService::sanitizeHtml('<div>Div</div>', true));
	}

	/* ===================== parseEnum() ===================== */

	public function testParseEnumWithValidUpperCase(): void
	{
		$result = FormService::parseEnum(TestStatus::class, 'ACTIVE');
		$this->assertInstanceOf(TestStatus::class, $result);
		$this->assertSame(TestStatus::ACTIVE, $result);
	}

	public function testParseEnumWithValidLowerCase(): void
	{
		$result = FormService::parseEnum(TestStatusLower::class, 'active', CASE_LOWER);
		$this->assertInstanceOf(TestStatusLower::class, $result);
		$this->assertSame(TestStatusLower::ACTIVE, $result);
	}

	public function testParseEnumWithMixedCaseConvertsToUpper(): void
	{
		$result = FormService::parseEnum(TestStatus::class, 'active');
		$this->assertInstanceOf(TestStatus::class, $result);
		$this->assertSame(TestStatus::ACTIVE, $result);
	}

	public function testParseEnumWithInvalidValue(): void
	{
		$result = FormService::parseEnum(TestStatus::class, 'INVALID');
		$this->assertNull($result);
	}

	public function testParseEnumWithNull(): void
	{
		$result = FormService::parseEnum(TestStatus::class, null);
		$this->assertNull($result);
	}

	public function testParseEnumThrowsExceptionForInvalidClass(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		$this->expectExceptionMessage('Class "stdClass" is not a valid BackedEnum');

		FormService::parseEnum(\stdClass::class, 'test');
	}

	public function testParseEnumThrowsExceptionForNonExistentClass(): void
	{
		$this->expectException(\InvalidArgumentException::class);

		FormService::parseEnum('NonExistentClass', 'test');
	}

	/* ===================== parseEnumList() ===================== */

	public function testParseEnumListWithValidValues(): void
	{
		$result = FormService::parseEnumList(['ACTIVE', 'PENDING'], TestStatus::class);
		$this->assertCount(2, $result);
		$this->assertSame(TestStatus::ACTIVE, $result[0]);
		$this->assertSame(TestStatus::PENDING, $result[1]);
	}

	public function testParseEnumListWithStringAndSeparator(): void
	{
		$result = FormService::parseEnumList('ACTIVE,PENDING', TestStatus::class, separatorIfString: ',');
		$this->assertCount(2, $result);
		$this->assertSame(TestStatus::ACTIVE, $result[0]);
		$this->assertSame(TestStatus::PENDING, $result[1]);
	}

	public function testParseEnumListWithAllowedValues(): void
	{
		$result = FormService::parseEnumList(
			['ACTIVE', 'INACTIVE', 'PENDING'],
			TestStatus::class,
			[TestStatus::ACTIVE, TestStatus::PENDING]
		);
		$this->assertCount(2, $result);
		$this->assertSame(TestStatus::ACTIVE, $result[0]);
		$this->assertSame(TestStatus::PENDING, $result[1]);
	}

	public function testParseEnumListWithParseFunction(): void
	{
		$parseFunction = fn(string $value) => TestStatus::tryFrom(strtoupper($value));
		$result = FormService::parseEnumList(
			['active', 'pending'],
			TestStatus::class,
			parseFunction: $parseFunction
		);
		$this->assertCount(2, $result);
	}

	public function testParseEnumListWithEmptyArray(): void
	{
		$result = FormService::parseEnumList([], TestStatus::class);
		$this->assertSame([], $result);
	}

	public function testParseEnumListWithNull(): void
	{
		$result = FormService::parseEnumList(null, TestStatus::class);
		$this->assertSame([], $result);
	}

	/* ===================== setFormData() ===================== */

	public function testSetFormDataWithPatchMethod(): void
	{
		$request = Request::create('/test', 'PATCH', [], [], [], [], 'key=value');

		// Vérifie que la méthode s'exécute sans erreur
		// Note: Dans un test unitaire, php://input est vide donc parseRawHttpRequestData ne parsera rien
		FormService::setFormData($request);

		$this->assertInstanceOf(Request::class, $request);
	}

	public function testSetFormDataWithPutMethod(): void
	{
		$request = Request::create('/test', 'PUT', [], [], [], [], 'key=value');

		// Vérifie que la méthode s'exécute sans erreur
		FormService::setFormData($request);

		$this->assertInstanceOf(Request::class, $request);
	}

	public function testSetFormDataWithDeleteMethod(): void
	{
		$request = Request::create('/test', 'DELETE', [], [], [], [], 'key=value');

		// Vérifie que la méthode s'exécute sans erreur
		FormService::setFormData($request);

		$this->assertInstanceOf(Request::class, $request);
	}

	public function testSetFormDataWithGetMethod(): void
	{
		$request = Request::create('/test', 'GET');

		FormService::setFormData($request);

		// Pour GET, rien ne devrait être ajouté
		$this->assertCount(0, $request->request->all());
	}

	public function testSetFormDataWithPostMethod(): void
	{
		$request = Request::create('/test', 'POST', ['key' => 'value']);

		FormService::setFormData($request);

		// Pour POST, les données existent déjà
		$this->assertTrue($request->request->has('key'));
	}

	/* ===================== getErrorMessages() ===================== */

	public function testGetErrorMessagesWithNoErrors(): void
	{
		$result = FormService::getErrorMessages(null, null);
		$this->assertSame([], $result);
	}

	public function testGetErrorMessagesWithEntityErrors(): void
	{
		$violation = new ConstraintViolation(
			'This value should not be blank.',
			'This value should not be blank.',
			[],
			'root',
			'field.property',
			null
		);
		$violations = new ConstraintViolationList([$violation]);

		$result = FormService::getErrorMessages($violations);

		$this->assertIsArray($result);
		$this->assertArrayHasKey('field.property', $result);
	}

	public function testGetErrorMessagesWithOtherErrors(): void
	{
		$otherErrors = [
			'email' => 'Invalid email format',
			'password' => 'Password too short',
		];

		$result = FormService::getErrorMessages(null, $otherErrors);

		$this->assertArrayHasKey('email', $result);
		$this->assertArrayHasKey('password', $result);
		$this->assertSame('Invalid email format', $result['email']);
		$this->assertSame('Password too short', $result['password']);
	}

	public function testGetErrorMessagesWithArrayErrors(): void
	{
		$otherErrors = [
			'email' => ['error.email.invalid', 'Invalid email'],
		];

		$result = FormService::getErrorMessages(null, $otherErrors, false);

		$this->assertArrayHasKey('email', $result);
		$this->assertSame('Invalid email', $result['email']);
	}

	public function testGetErrorMessagesReturnFullError(): void
	{
		$otherErrors = [
			'email' => ['error.email.invalid', 'Invalid email'],
		];

		$result = FormService::getErrorMessages(null, $otherErrors, false, false);

		$this->assertArrayHasKey('email', $result);
		$this->assertIsArray($result['email']);
		$this->assertSame('error.email.invalid', $result['email'][0]);
		$this->assertSame('Invalid email', $result['email'][1]);
	}

	public function testGetErrorMessagesCombinesAllErrors(): void
	{
		$violation = new ConstraintViolation(
			'Not blank',
			'Not blank',
			[],
			'root',
			'field.property',
			null
		);
		$violations = new ConstraintViolationList([$violation]);

		$otherErrors = [
			'email' => 'Invalid email',
		];

		$result = FormService::getErrorMessages($violations, $otherErrors);

		$this->assertCount(2, $result);
		$this->assertArrayHasKey('field.property', $result);
		$this->assertArrayHasKey('email', $result);
	}

	public function testGetErrorMessagesWithPropertyPathWithoutDot(): void
	{
		// Test le bug fix pour strpos qui retourne false quand il n'y a pas de point dans le propertyPath
		$violation = new ConstraintViolation(
			'This value should not be blank.',
			'This value should not be blank.',
			[],
			'root',
			'simpleField',  // Pas de point dans le propertyPath
			null
		);
		$violations = new ConstraintViolationList([$violation]);

		$result = FormService::getErrorMessages($violations);

		$this->assertIsArray($result);
		$this->assertArrayHasKey('simple_field', $result);
		$this->assertSame('This value should not be blank.', $result['simple_field']);
	}

	public function testGetErrorMessagesConvertsPropertyPathToSnakeCase(): void
	{
		$violation = new ConstraintViolation(
			'Error message',
			'Error message',
			[],
			'root',
			'myFieldName.property',
			null
		);
		$violations = new ConstraintViolationList([$violation]);

		$result = FormService::getErrorMessages($violations);

		$this->assertArrayHasKey('my_field_name.property', $result);
	}

	/* ===================== Integration tests ===================== */

	public function testTrimAndParseArrayTogether(): void
	{
		$input = "  item1  ,  item2  ,  item3  ";
		$array = FormService::parseArray($input, true, ',');

		$trimmed = array_map(fn($item) => FormService::trim($item), $array);

		$this->assertSame(['item1', 'item2', 'item3'], $trimmed);
	}

	public function testParseEnumListFiltersInvalidValues(): void
	{
		// parseEnumList devrait filtrer les valeurs invalides automatiquement
		$result = FormService::parseEnumList(['ACTIVE', 'INVALID', 'PENDING'], TestStatus::class);

		// INVALID n'existe pas dans l'enum, donc devrait être filtré
		$this->assertCount(2, $result);
		$this->assertSame(TestStatus::ACTIVE, $result[0]);
		$this->assertSame(TestStatus::PENDING, $result[1]);
	}
}