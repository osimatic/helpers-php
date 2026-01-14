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
			'empty string with default false' => ['', false, false],
			'empty string with default true' => ['', true, true],
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
}