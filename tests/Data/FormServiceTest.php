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