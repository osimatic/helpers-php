<?php

declare(strict_types=1);

namespace Tests\Text;

use Osimatic\Text\JSON;
use PHPUnit\Framework\TestCase;

final class JSONTest extends TestCase
{
	/* ===================== reduce() - JSON without comments ===================== */

	public function testReduceWithSimpleJsonString(): void
	{
		$json = '{"name": "John", "age": 30}';
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John", "age": 30}', $result);
	}

	public function testReduceWithJsonArray(): void
	{
		$json = '[1, 2, 3, 4, 5]';
		$result = JSON::reduce($json);

		$this->assertSame('[1, 2, 3, 4, 5]', $result);
	}

	public function testReduceWithNestedJson(): void
	{
		$json = '{"user": {"name": "John", "address": {"city": "Paris"}}}';
		$result = JSON::reduce($json);

		$this->assertSame('{"user": {"name": "John", "address": {"city": "Paris"}}}', $result);
	}

	public function testReduceWithEmptyJson(): void
	{
		$json = '{}';
		$result = JSON::reduce($json);

		$this->assertSame('{}', $result);
	}

	public function testReduceWithEmptyArray(): void
	{
		$json = '[]';
		$result = JSON::reduce($json);

		$this->assertSame('[]', $result);
	}

	/* ===================== reduce() - Whitespace handling ===================== */

	public function testReduceRemovesLeadingWhitespace(): void
	{
		$json = '   {"name": "John"}';
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceRemovesTrailingWhitespace(): void
	{
		$json = '{"name": "John"}   ';
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceRemovesLeadingAndTrailingWhitespace(): void
	{
		$json = '   {"name": "John"}   ';
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceRemovesLeadingTabs(): void
	{
		$json = "\t\t{\"name\": \"John\"}";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceRemovesLeadingNewlines(): void
	{
		$json = "\n\n{\"name\": \"John\"}";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceRemovesMixedWhitespace(): void
	{
		$json = " \t\n  {\"name\": \"John\"}  \n\t ";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReducePreservesInternalWhitespace(): void
	{
		$json = '{"name": "John Doe", "title": "Software Engineer"}';
		$result = JSON::reduce($json);

		// Les espaces à l'intérieur du JSON ne sont pas supprimés
		$this->assertSame('{"name": "John Doe", "title": "Software Engineer"}', $result);
	}

	/* ===================== reduce() - Single-line comments ===================== */

	public function testReduceRemovesSingleLineCommentAtStart(): void
	{
		$json = "// This is a comment\n{\"name\": \"John\"}";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceRemovesSingleLineCommentWithSpacesAtStart(): void
	{
		$json = "   // This is a comment\n{\"name\": \"John\"}";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceRemovesMultipleSingleLineComments(): void
	{
		$json = "// First comment\n// Second comment\n{\"name\": \"John\"}";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceRemovesSingleLineCommentInMiddle(): void
	{
		$json = "{\"name\": \"John\",\n// This is a comment\n\"age\": 30}";
		$result = JSON::reduce($json);

		$this->assertSame("{\"name\": \"John\",\n\"age\": 30}", $result);
	}

	public function testReduceRemovesSingleLineCommentWithSpecialChars(): void
	{
		$json = "// Comment with special chars: @#$%^&*()\n{\"name\": \"John\"}";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceRemovesSingleLineCommentWithUrl(): void
	{
		$json = "// See: https://example.com/api\n{\"name\": \"John\"}";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	/* ===================== reduce() - Multi-line comments at start ===================== */

	public function testReduceRemovesMultiLineCommentAtStart(): void
	{
		$json = "/* This is a comment */\n{\"name\": \"John\"}";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceRemovesMultiLineCommentWithNewlinesAtStart(): void
	{
		$json = "/* This is\na multi-line\ncomment */\n{\"name\": \"John\"}";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceRemovesMultiLineCommentWithSpacesAtStart(): void
	{
		$json = "   /* Comment */   \n{\"name\": \"John\"}";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceRemovesLongMultiLineCommentAtStart(): void
	{
		$json = "/*\n * This is a long comment\n * with multiple lines\n * and asterisks\n */\n{\"name\": \"John\"}";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	/* ===================== reduce() - Multi-line comments at end ===================== */

	public function testReduceRemovesMultiLineCommentAtEnd(): void
	{
		$json = "{\"name\": \"John\"}\n/* This is a comment */";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceRemovesMultiLineCommentWithNewlinesAtEnd(): void
	{
		$json = "{\"name\": \"John\"}\n/* This is\na multi-line\ncomment */";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceRemovesMultiLineCommentWithSpacesAtEnd(): void
	{
		$json = "{\"name\": \"John\"}\n/* Comment */   ";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceRemovesLongMultiLineCommentAtEnd(): void
	{
		$json = "{\"name\": \"John\"}\n/*\n * This is a long comment\n * with multiple lines\n * and asterisks\n */";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	/* ===================== reduce() - Multiple comment types ===================== */

	public function testReduceRemovesMixedComments(): void
	{
		$json = "// Single line comment\n/* Multi-line comment */\n{\"name\": \"John\"}\n/* End comment */";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceWithCommentsAndWhitespace(): void
	{
		$json = "  // Comment 1\n  /* Comment 2 */  \n  {\"name\": \"John\"}  \n  /* Comment 3 */  ";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceWithComplexJson(): void
	{
		$json = <<<JSON
// Configuration file
/*
 * Multi-line header comment
 * with details
 */
{
	"name": "John",
	// User age
	"age": 30,
	"address": {
		"city": "Paris"
	}
}
/* End of configuration */
JSON;

		$result = JSON::reduce($json);

		$expected = <<<JSON
{
	"name": "John",
	"age": 30,
	"address": {
		"city": "Paris"
	}
}
JSON;

		$this->assertSame($expected, $result);
	}

	/* ===================== reduce() - Edge cases ===================== */

	public function testReduceWithEmptyString(): void
	{
		$json = '';
		$result = JSON::reduce($json);

		$this->assertSame('', $result);
	}

	public function testReduceWithOnlyWhitespace(): void
	{
		$json = '   ';
		$result = JSON::reduce($json);

		$this->assertSame('', $result);
	}

	public function testReduceWithOnlySingleLineComment(): void
	{
		$json = '// Just a comment';
		$result = JSON::reduce($json);

		$this->assertSame('', $result);
	}

	public function testReduceWithOnlyMultiLineCommentAtStart(): void
	{
		$json = '/* Just a comment */';
		$result = JSON::reduce($json);

		$this->assertSame('', $result);
	}

	public function testReduceWithJsonNull(): void
	{
		$json = 'null';
		$result = JSON::reduce($json);

		$this->assertSame('null', $result);
	}

	public function testReduceWithJsonTrue(): void
	{
		$json = 'true';
		$result = JSON::reduce($json);

		$this->assertSame('true', $result);
	}

	public function testReduceWithJsonFalse(): void
	{
		$json = 'false';
		$result = JSON::reduce($json);

		$this->assertSame('false', $result);
	}

	public function testReduceWithJsonNumber(): void
	{
		$json = '42';
		$result = JSON::reduce($json);

		$this->assertSame('42', $result);
	}

	public function testReduceWithJsonString(): void
	{
		$json = '"Hello World"';
		$result = JSON::reduce($json);

		$this->assertSame('"Hello World"', $result);
	}

	/* ===================== reduce() - Comments inside JSON values ===================== */

	public function testReducePreservesDoubleSlashInJsonString(): void
	{
		// Les "//" à l'intérieur d'une chaîne JSON ne doivent pas être considérés comme un commentaire
		$json = '{"url": "http://example.com"}';
		$result = JSON::reduce($json);

		$this->assertSame('{"url": "http://example.com"}', $result);
	}

	public function testReducePreservesSlashStarInJsonString(): void
	{
		// Les "/*" à l'intérieur d'une chaîne JSON ne doivent pas être considérés comme un commentaire
		$json = '{"comment": "This is a /* test */ value"}';
		$result = JSON::reduce($json);

		$this->assertSame('{"comment": "This is a /* test */ value"}', $result);
	}

	/* ===================== reduce() - Real-world examples ===================== */

	public function testReduceWithPackageJsonLikeStructure(): void
	{
		$json = <<<JSON
// Package configuration
{
	"name": "my-package",
	"version": "1.0.0",
	// Dependencies
	"dependencies": {
		"library": "^2.0.0"
	}
}
JSON;

		$result = JSON::reduce($json);

		$expected = <<<JSON
{
	"name": "my-package",
	"version": "1.0.0",
	"dependencies": {
		"library": "^2.0.0"
	}
}
JSON;

		$this->assertSame($expected, $result);
	}

	public function testReduceWithTsConfigLikeStructure(): void
	{
		$json = <<<JSON
/* TypeScript configuration */
{
	// Compiler options
	"compilerOptions": {
		"target": "ES2020",
		"module": "commonjs"
	}
}
/* End of config */
JSON;

		$result = JSON::reduce($json);

		$expected = <<<JSON
{
	"compilerOptions": {
		"target": "ES2020",
		"module": "commonjs"
	}
}
JSON;

		$this->assertSame($expected, $result);
	}

	public function testReduceWithApiResponseExample(): void
	{
		$json = <<<JSON
// API Response
{
	"status": "success",
	"data": {
		"id": 123,
		"name": "John Doe"
	}
}
JSON;

		$result = JSON::reduce($json);

		$expected = <<<JSON
{
	"status": "success",
	"data": {
		"id": 123,
		"name": "John Doe"
	}
}
JSON;

		$this->assertSame($expected, $result);
	}

	/* ===================== reduce() - Multiple empty lines ===================== */

	public function testReduceWithMultipleEmptyLines(): void
	{
		$json = "\n\n\n{\"name\": \"John\"}\n\n\n";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceWithCarriageReturns(): void
	{
		$json = "\r\n{\"name\": \"John\"}\r\n";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	/* ===================== reduce() - Special characters in comments ===================== */

	public function testReduceWithUnicodeInComment(): void
	{
		$json = "// Commentaire avec émojis 🎉 et accents éàü\n{\"name\": \"John\"}";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceWithHtmlInComment(): void
	{
		$json = "// Comment with <html> tags\n{\"name\": \"John\"}";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	public function testReduceWithRegexPatternInComment(): void
	{
		$json = "// Pattern: /^[a-z]+$/i\n{\"name\": \"John\"}";
		$result = JSON::reduce($json);

		$this->assertSame('{"name": "John"}', $result);
	}

	/* ===================== validate() ===================== */

	public function testValidateValidJson(): void
	{
		$json = '{"key": "value"}';
		$this->assertTrue(JSON::validate($json));
	}

	public function testValidateInvalidJson(): void
	{
		$json = '{key: value}'; // Missing quotes
		$this->assertFalse(JSON::validate($json));
	}

	public function testValidateEmptyString(): void
	{
		$this->assertFalse(JSON::validate(''));
	}

	public function testValidateWithComments(): void
	{
		// JSON with comments is invalid JSON (comments are not part of JSON spec)
		$json = '{"key": "value" /* comment */}';
		$this->assertFalse(JSON::validate($json));
	}

	/* ===================== decode() ===================== */

	public function testDecodeValidJsonToArray(): void
	{
		$json = '{"key": "value", "number": 42}';
		$result = JSON::decode($json, true);
		$this->assertIsArray($result);
		$this->assertEquals('value', $result['key']);
		$this->assertEquals(42, $result['number']);
	}

	public function testDecodeValidJsonToObject(): void
	{
		$json = '{"key": "value", "number": 42}';
		$result = JSON::decode($json, false);
		$this->assertIsObject($result);
		$this->assertEquals('value', $result->key);
		$this->assertEquals(42, $result->number);
	}

	public function testDecodeInvalidJsonReturnsNull(): void
	{
		$json = '{invalid json}';
		$result = JSON::decode($json);
		$this->assertNull($result);
	}

	public function testDecodeWithMaxDepth(): void
	{
		$json = '{"level1": {"level2": {"level3": "value"}}}';

		// With sufficient depth
		$result = JSON::decode($json, true, 512);
		$this->assertIsArray($result);
		$this->assertEquals('value', $result['level1']['level2']['level3']);

		// With insufficient depth (depth of 2 means max 2 levels)
		$result = JSON::decode($json, true, 2);
		$this->assertNull($result);
	}

	/* ===================== encode() ===================== */

	public function testEncodeArray(): void
	{
		$data = ['key' => 'value', 'number' => 42];
		$result = JSON::encode($data);
		$this->assertIsString($result);
		$this->assertEquals('{"key":"value","number":42}', $result);
	}

	public function testEncodeObject(): void
	{
		$data = new \stdClass();
		$data->key = 'value';
		$data->number = 42;
		$result = JSON::encode($data);
		$this->assertIsString($result);
		$this->assertStringContainsString('"key":"value"', $result);
		$this->assertStringContainsString('"number":42', $result);
	}

	public function testEncodeWithPrettyPrint(): void
	{
		$data = ['key' => 'value', 'nested' => ['item' => 1]];
		$result = JSON::encode($data, JSON_PRETTY_PRINT);
		$this->assertIsString($result);
		$this->assertStringContainsString("\n", $result);
		$this->assertStringContainsString('    ', $result); // Indentation
	}

	public function testEncodeInvalidData(): void
	{
		// Create a resource (resources cannot be encoded to JSON)
		$resource = fopen('php://memory', 'r');
		$result = JSON::encode($resource);
		fclose($resource);
		$this->assertNull($result);
	}

	/* ===================== prettify() ===================== */

	public function testPrettifyValidJson(): void
	{
		$json = '{"key":"value","nested":{"item":1}}';
		$result = JSON::prettify($json);
		$this->assertIsString($result);
		$this->assertStringContainsString("\n", $result);
		// Check indentation (4 spaces)
		$this->assertStringContainsString('    "key"', $result);
	}

	public function testPrettifyInvalidJsonReturnsNull(): void
	{
		$json = '{invalid json}';
		$result = JSON::prettify($json);
		$this->assertNull($result);
	}

	public function testPrettifyVerifyIndentation(): void
	{
		$json = '{"key":"value"}';
		$result = JSON::prettify($json);
		$lines = explode("\n", $result);
		$this->assertGreaterThan(1, count($lines));
	}

	/* ===================== minify() ===================== */

	public function testMinifyValidJson(): void
	{
		$json = '{
			"key": "value",
			"nested": {
				"item": 1
			}
		}';
		$result = JSON::minify($json);
		$this->assertIsString($result);
		$this->assertStringNotContainsString("\n", $result);
		$this->assertStringNotContainsString("\t", $result);
		$this->assertEquals('{"key":"value","nested":{"item":1}}', $result);
	}

	public function testMinifyInvalidJsonReturnsNull(): void
	{
		$json = '{invalid json}';
		$result = JSON::minify($json);
		$this->assertNull($result);
	}

	public function testMinifyRemovesSpaces(): void
	{
		$json = '{ "key" : "value" }';
		$result = JSON::minify($json);
		$this->assertIsString($result);
		$this->assertEquals('{"key":"value"}', $result);
	}

	/* ===================== getLastError() ===================== */

	public function testGetLastErrorAfterError(): void
	{
		// Trigger an error by decoding invalid JSON
		JSON::decode('{invalid json}');
		$error = JSON::getLastError();
		$this->assertIsString($error);
		$this->assertNotEmpty($error);
		$this->assertNotEquals('No error', $error);
	}

	public function testGetLastErrorNoError(): void
	{
		// Decode valid JSON
		JSON::decode('{"key":"value"}');
		$error = JSON::getLastError();
		$this->assertIsString($error);
		$this->assertEquals('No error', $error);
	}
}
