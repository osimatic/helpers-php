<?php

namespace Tests\Data;

use Osimatic\Data\Input;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\HttpFoundation\Request;

class InputTest extends TestCase
{
	// ========== get() with Request Tests ==========

	public function testGetWithRequestReturnsValue(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('key')
			->willReturn('value');

		$result = Input::get($request, 'key');
		self::assertSame('value', $result);
	}

	public function testGetWithRequestReturnsNull(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('key')
			->willReturn(null);

		$result = Input::get($request, 'key');
		self::assertNull($result);
	}

	public function testGetWithRequestReturnsArray(): void
	{
		$expectedArray = ['foo' => 'bar'];
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('data')
			->willReturn($expectedArray);

		$result = Input::get($request, 'data');
		self::assertSame($expectedArray, $result);
	}

	// ========== get() with InputInterface (CLI) Tests ==========

	public function testGetWithInputInterfaceReturnsOption(): void
	{
		$input = $this->createMock(InputInterface::class);
		$input->expects(self::once())
			->method('hasOption')
			->with('key')
			->willReturn(true);
		$input->expects(self::once())
			->method('getOption')
			->with('key')
			->willReturn('option-value');

		$result = Input::get($input, 'key');
		self::assertSame('option-value', $result);
	}

	public function testGetWithInputInterfaceReturnsArgument(): void
	{
		$input = $this->createMock(InputInterface::class);
		$input->expects(self::once())
			->method('hasOption')
			->with('key')
			->willReturn(false);
		$input->expects(self::once())
			->method('hasArgument')
			->with('key')
			->willReturn(true);
		$input->expects(self::once())
			->method('getArgument')
			->with('key')
			->willReturn('argument-value');

		$result = Input::get($input, 'key');
		self::assertSame('argument-value', $result);
	}

	public function testGetWithInputInterfaceOptionTakesPrecedenceOverArgument(): void
	{
		$input = $this->createMock(InputInterface::class);
		$input->expects(self::once())
			->method('hasOption')
			->with('key')
			->willReturn(true);
		$input->expects(self::once())
			->method('getOption')
			->with('key')
			->willReturn('option-value');
		$input->expects(self::never())
			->method('hasArgument');

		$result = Input::get($input, 'key');
		self::assertSame('option-value', $result);
	}

	public function testGetWithInputInterfaceReturnsNullWhenOptionIsNull(): void
	{
		$input = $this->createMock(InputInterface::class);
		$input->expects(self::once())
			->method('hasOption')
			->with('key')
			->willReturn(true);
		$input->expects(self::once())
			->method('getOption')
			->with('key')
			->willReturn(null);
		$input->expects(self::once())
			->method('hasArgument')
			->with('key')
			->willReturn(false);

		$result = Input::get($input, 'key');
		self::assertNull($result);
	}

	public function testGetWithInputInterfaceReturnsNullWhenOptionIsEmptyString(): void
	{
		$input = $this->createMock(InputInterface::class);
		$input->expects(self::once())
			->method('hasOption')
			->with('key')
			->willReturn(true);
		$input->expects(self::once())
			->method('getOption')
			->with('key')
			->willReturn('');
		$input->expects(self::once())
			->method('hasArgument')
			->with('key')
			->willReturn(false);

		$result = Input::get($input, 'key');
		self::assertNull($result);
	}

	public function testGetWithInputInterfaceFallsBackToArgumentWhenOptionIsEmpty(): void
	{
		$input = $this->createMock(InputInterface::class);
		$input->expects(self::once())
			->method('hasOption')
			->with('key')
			->willReturn(true);
		$input->expects(self::once())
			->method('getOption')
			->with('key')
			->willReturn('');
		$input->expects(self::once())
			->method('hasArgument')
			->with('key')
			->willReturn(true);
		$input->expects(self::once())
			->method('getArgument')
			->with('key')
			->willReturn('argument-value');

		$result = Input::get($input, 'key');
		self::assertSame('argument-value', $result);
	}

	public function testGetWithInputInterfaceReturnsNullWhenNothingFound(): void
	{
		$input = $this->createMock(InputInterface::class);
		$input->expects(self::once())
			->method('hasOption')
			->with('key')
			->willReturn(false);
		$input->expects(self::once())
			->method('hasArgument')
			->with('key')
			->willReturn(false);

		$result = Input::get($input, 'key');
		self::assertNull($result);
	}

	// ========== getBool() Tests ==========

	public function testGetBoolWithRequestReturnsTrue(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('enabled')
			->willReturn('true');

		$result = Input::getBool($request, 'enabled');
		self::assertTrue($result);
	}

	public function testGetBoolWithRequestReturnsFalse(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('enabled')
			->willReturn('false');

		$result = Input::getBool($request, 'enabled');
		self::assertFalse($result);
	}

	public function testGetBoolWithRequestReturnsDefaultWhenNull(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('enabled')
			->willReturn(null);

		$result = Input::getBool($request, 'enabled', true);
		self::assertTrue($result);
	}

	public function testGetBoolWithRequestReturnsDefaultFalseWhenNull(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('enabled')
			->willReturn(null);

		$result = Input::getBool($request, 'enabled', false);
		self::assertFalse($result);
	}

	public function testGetBoolWithRequestDefaultIsFalseWhenNotProvided(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('enabled')
			->willReturn(null);

		$result = Input::getBool($request, 'enabled');
		self::assertFalse($result);
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('booleanValuesProvider')]
	public function testGetBoolWithVariousValues(mixed $value, bool $expected): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('key')
			->willReturn($value);

		$result = Input::getBool($request, 'key');
		self::assertSame($expected, $result);
	}

	public static function booleanValuesProvider(): array
	{
		return [
			// True values
			'boolean true' => [true, true],
			'string 1' => ['1', true],
			'string true' => ['true', true],
			'string TRUE' => ['TRUE', true],
			'string yes' => ['yes', true],
			'string YES' => ['YES', true],
			'string on' => ['on', true],
			'string ON' => ['ON', true],

			// False values
			'boolean false' => [false, false],
			'string 0' => ['0', false],
			'string false' => ['false', false],
			'string FALSE' => ['FALSE', false],
			'string no' => ['no', false],
			'string NO' => ['NO', false],
			'string off' => ['off', false],
			'string OFF' => ['OFF', false],

			// Null returns default (false)
			'null' => [null, false],
		];
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('invalidBooleanValuesProvider')]
	public function testGetBoolWithInvalidValuesReturnsDefault(mixed $value, bool $default, bool $expected): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('key')
			->willReturn($value);

		$result = Input::getBool($request, 'key', $default);
		self::assertSame($expected, $result);
	}

	public static function invalidBooleanValuesProvider(): array
	{
		return [
			'string random with default false' => ['random', false, false],
			'string random with default true' => ['random', true, true],
			// Empty string is a valid false value, not invalid
			// 'empty string with default false' => ['', false, false],
			// 'empty string with default true' => ['', true, true],
			'integer 2 with default false' => [2, false, false],
			'integer 2 with default true' => [2, true, true],
		];
	}

	public function testGetBoolWithInputInterface(): void
	{
		$input = $this->createMock(InputInterface::class);
		$input->expects(self::once())
			->method('hasOption')
			->with('verbose')
			->willReturn(true);
		$input->expects(self::once())
			->method('getOption')
			->with('verbose')
			->willReturn('yes');

		$result = Input::getBool($input, 'verbose');
		self::assertTrue($result);
	}

	// ========== getInt() Tests ==========

	public function testGetIntWithRequestReturnsInteger(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('page')
			->willReturn('42');

		$result = Input::getInt($request, 'page');
		self::assertSame(42, $result);
	}

	public function testGetIntWithRequestReturnsDefaultWhenNull(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('page')
			->willReturn(null);

		$result = Input::getInt($request, 'page', 1);
		self::assertSame(1, $result);
	}

	public function testGetIntWithRequestReturnsDefaultWhenInvalid(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('page')
			->willReturn('invalid');

		$result = Input::getInt($request, 'page', 10);
		self::assertSame(10, $result);
	}

	public function testGetIntWithMinValidation(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('age')
			->willReturn('5');

		$result = Input::getInt($request, 'age', 18, 18, null);
		self::assertSame(18, $result); // Below min, returns default
	}

	public function testGetIntWithMaxValidation(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('age')
			->willReturn('150');

		$result = Input::getInt($request, 'age', 100, null, 100);
		self::assertSame(100, $result); // Above max, returns default
	}

	public function testGetIntWithRangeValidation(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('count')
			->willReturn('50');

		$result = Input::getInt($request, 'count', 10, 1, 100);
		self::assertSame(50, $result);
	}

	// ========== getFloat() Tests ==========

	public function testGetFloatWithRequestReturnsFloat(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('price')
			->willReturn('19.99');

		$result = Input::getFloat($request, 'price');
		self::assertSame(19.99, $result);
	}

	public function testGetFloatWithRequestReturnsDefaultWhenNull(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('price')
			->willReturn(null);

		$result = Input::getFloat($request, 'price', 0.0);
		self::assertSame(0.0, $result);
	}

	public function testGetFloatWithRequestReturnsDefaultWhenInvalid(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('price')
			->willReturn('invalid');

		$result = Input::getFloat($request, 'price', 10.5);
		self::assertSame(10.5, $result);
	}

	public function testGetFloatWithRangeValidation(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('discount')
			->willReturn('25.5');

		$result = Input::getFloat($request, 'discount', 0.0, 0.0, 100.0);
		self::assertSame(25.5, $result);
	}

	public function testGetFloatOutOfRangeReturnsDefault(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('discount')
			->willReturn('150.0');

		$result = Input::getFloat($request, 'discount', 0.0, 0.0, 100.0);
		self::assertSame(0.0, $result); // Above max, returns default
	}

	// ========== getString() Tests ==========

	public function testGetStringWithRequestReturnsString(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('name')
			->willReturn('John');

		$result = Input::getString($request, 'name');
		self::assertSame('John', $result);
	}

	public function testGetStringWithRequestTrimsValue(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('name')
			->willReturn('  John  ');

		$result = Input::getString($request, 'name');
		self::assertSame('John', $result);
	}

	public function testGetStringWithoutTrimming(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('name')
			->willReturn('  John  ');

		$result = Input::getString($request, 'name', '', false);
		self::assertSame('  John  ', $result);
	}

	public function testGetStringReturnsDefaultWhenNull(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('name')
			->willReturn(null);

		$result = Input::getString($request, 'name', 'Guest');
		self::assertSame('Guest', $result);
	}

	public function testGetStringConvertsNonStringToString(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('id')
			->willReturn(123);

		$result = Input::getString($request, 'id');
		self::assertSame('123', $result);
	}

	// ========== getArray() Tests ==========

	public function testGetArrayWithRequestReturnsArray(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('ids')
			->willReturn([1, 2, 3]);

		$result = Input::getArray($request, 'ids');
		self::assertSame([1, 2, 3], $result);
	}

	public function testGetArrayWithRequestReturnsDefaultWhenNull(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('ids')
			->willReturn(null);

		$result = Input::getArray($request, 'ids', ['default']);
		self::assertSame(['default'], $result);
	}

	public function testGetArrayWithSeparator(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('tags')
			->willReturn('php,symfony,doctrine');

		$result = Input::getArray($request, 'tags', [], ',');
		self::assertSame(['php', 'symfony', 'doctrine'], $result);
	}

	public function testGetArrayFiltersEmptyValues(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('values')
			->willReturn([1, '', 2, null, 3]);

		$result = Input::getArray($request, 'values');
		self::assertSame([1, 2, 3], $result);
	}

	public function testGetArrayWithoutFiltering(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('values')
			->willReturn([1, '', 2, null, 3]);

		$result = Input::getArray($request, 'values', [], null, false);
		self::assertSame([1, '', 2, null, 3], $result);
	}

	public function testGetArrayConvertsNonArrayToArray(): void
	{
		$request = $this->createMock(Request::class);
		$request->expects(self::once())
			->method('get')
			->with('value')
			->willReturn('single');

		$result = Input::getArray($request, 'value');
		self::assertSame(['single'], $result);
	}

	// ========== has() Tests ==========

	public function testHasWithRequestReturnsTrueWhenParameterExists(): void
	{
		$request = Request::create('/test', 'GET', ['id' => 123]);

		$result = Input::has($request, 'id');
		self::assertTrue($result);
	}

	public function testHasWithRequestReturnsFalseWhenParameterDoesNotExist(): void
	{
		$request = Request::create('/test', 'GET', []);

		$result = Input::has($request, 'id');
		self::assertFalse($result);
	}

	public function testHasWithRequestChecksQueryParameters(): void
	{
		$request = Request::create('/test?id=123', 'GET');

		$result = Input::has($request, 'id');
		self::assertTrue($result);
	}

	public function testHasWithRequestChecksRequestParameters(): void
	{
		$request = Request::create('/test', 'POST', ['name' => 'John']);

		$result = Input::has($request, 'name');
		self::assertTrue($result);
	}

	public function testHasWithInputInterfaceReturnsTrueWhenOptionExists(): void
	{
		$input = $this->createMock(InputInterface::class);
		$input->expects(self::once())
			->method('hasOption')
			->with('verbose')
			->willReturn(true);

		$result = Input::has($input, 'verbose');
		self::assertTrue($result);
	}

	public function testHasWithInputInterfaceReturnsTrueWhenArgumentExists(): void
	{
		$input = $this->createMock(InputInterface::class);
		$input->expects(self::once())
			->method('hasOption')
			->with('name')
			->willReturn(false);
		$input->expects(self::once())
			->method('hasArgument')
			->with('name')
			->willReturn(true);

		$result = Input::has($input, 'name');
		self::assertTrue($result);
	}

	public function testHasWithInputInterfaceReturnsFalseWhenNotFound(): void
	{
		$input = $this->createMock(InputInterface::class);
		$input->expects(self::once())
			->method('hasOption')
			->with('missing')
			->willReturn(false);
		$input->expects(self::once())
			->method('hasArgument')
			->with('missing')
			->willReturn(false);

		$result = Input::has($input, 'missing');
		self::assertFalse($result);
	}
}