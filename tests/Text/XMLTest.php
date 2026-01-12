<?php

declare(strict_types=1);

namespace Tests\Text;

use Osimatic\Text\XML;
use Osimatic\Text\XMLConverter;
use PHPUnit\Framework\TestCase;

final class XMLTest extends TestCase
{
	/* ===================== Constants ===================== */

	public function testFileExtensionConstant(): void
	{
		$this->assertSame('.xml', XML::FILE_EXTENSION);
	}

	public function testMimeTypesConstant(): void
	{
		$this->assertIsArray(XML::MIME_TYPES);
		$this->assertContains('application/xml', XML::MIME_TYPES);
		$this->assertContains('text/xml', XML::MIME_TYPES);
	}

	/* ===================== convertToArray() ===================== */

	public function testConvertToArrayWithValidXml(): void
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?><root><item>value</item></root>';
		$result = XML::convertToArray($xml);

		$this->assertIsArray($result);
		$this->assertArrayHasKey('root', $result);
		$this->assertArrayHasKey('item', $result['root']);
		$this->assertSame('value', $result['root']['item']);
	}

	public function testConvertToArrayWithComplexXml(): void
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?><root><item id="1">First</item><item id="2">Second</item></root>';
		$result = XML::convertToArray($xml);

		$this->assertIsArray($result);
		$this->assertArrayHasKey('root', $result);
	}

	public function testConvertToArrayWithEmptyXml(): void
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?><root/>';
		$result = XML::convertToArray($xml);

		$this->assertIsArray($result);
		$this->assertArrayHasKey('root', $result);
	}

	public function testConvertToArrayWithInvalidXml(): void
	{
		$xml = '<invalid>unclosed tag';
		$result = XML::convertToArray($xml);

		$this->assertNull($result);
	}

	public function testConvertToArrayWithEmptyString(): void
	{
		$result = XML::convertToArray('');
		$this->assertNull($result);
	}

	/* ===================== XMLConverter::convertToArray() ===================== */

	public function testXMLConverterConvertToArrayWithValidXml(): void
	{
		$converter = new XMLConverter();
		$xml = '<?xml version="1.0" encoding="UTF-8"?><root><name>Test</name><value>123</value></root>';
		$result = $converter->convertToArray($xml);

		$this->assertIsArray($result);
		$this->assertArrayHasKey('root', $result);
		$this->assertSame('Test', $result['root']['name']);
		$this->assertSame('123', $result['root']['value']);
	}

	public function testXMLConverterConvertToArrayWithInvalidXml(): void
	{
		$converter = new XMLConverter();
		$result = $converter->convertToArray('<invalid>');

		$this->assertNull($result);
	}

	/* ===================== XMLConverter::convertFromArray() ===================== */

	public function testXMLConverterConvertFromArrayWithValidArray(): void
	{
		$converter = new XMLConverter();
		$array = [
			'item' => 'value',
			'number' => '123',
		];
		$result = $converter->convertFromArray($array, 'root');

		$this->assertIsString($result);
		$this->assertStringContainsString('<root>', $result);
		$this->assertStringContainsString('<item>value</item>', $result);
		$this->assertStringContainsString('<number>123</number>', $result);
		$this->assertStringContainsString('</root>', $result);
	}

	public function testXMLConverterConvertFromArrayWithEmptyArray(): void
	{
		$converter = new XMLConverter();
		$result = $converter->convertFromArray([], 'root');

		$this->assertIsString($result);
		$this->assertStringContainsString('<root', $result);
	}

	public function testXMLConverterConvertFromArrayWithNestedArray(): void
	{
		$converter = new XMLConverter();
		$array = [
			'parent' => [
				'child' => 'value',
			],
		];
		$result = $converter->convertFromArray($array, 'root');

		$this->assertIsString($result);
		$this->assertStringContainsString('<parent>', $result);
		$this->assertStringContainsString('<child>value</child>', $result);
	}

	/* ===================== XMLConverter::convertToArrayOld() ===================== */

	public function testXMLConverterConvertToArrayOldWithValidXml(): void
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?><root><item>value</item></root>';
		$result = XMLConverter::convertToArrayOld($xml);

		$this->assertIsArray($result);
		$this->assertArrayHasKey('root', $result);
	}

	public function testXMLConverterConvertToArrayOldWithAttributes(): void
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?><root><item id="1">value</item></root>';
		$result = XMLConverter::convertToArrayOld($xml, true, 'tag');

		$this->assertIsArray($result);
		$this->assertArrayHasKey('root', $result);
	}

	public function testXMLConverterConvertToArrayOldWithoutAttributes(): void
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?><root><item id="1">value</item></root>';
		$result = XMLConverter::convertToArrayOld($xml, false);

		$this->assertIsArray($result);
		$this->assertArrayHasKey('root', $result);
	}

	public function testXMLConverterConvertToArrayOldWithAttributePriority(): void
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?><root><item id="1">value</item></root>';
		$result = XMLConverter::convertToArrayOld($xml, true, 'attribute');

		$this->assertIsArray($result);
		$this->assertArrayHasKey('root', $result);
	}

	public function testXMLConverterConvertToArrayOldWithEmptyString(): void
	{
		$result = XMLConverter::convertToArrayOld('');

		$this->assertIsArray($result);
		$this->assertEmpty($result);
	}

	public function testXMLConverterConvertToArrayOldWithInvalidXml(): void
	{
		$result = XMLConverter::convertToArrayOld('<invalid>unclosed');

		$this->assertIsArray($result);
		// Invalid XML should still return an array (might be empty or partial)
	}

	/* ===================== XMLConverter::setLogger() ===================== */

	public function testXMLConverterSetLogger(): void
	{
		$converter = new XMLConverter();
		$logger = new \Psr\Log\NullLogger();

		$result = $converter->setLogger($logger);

		$this->assertInstanceOf(XMLConverter::class, $result);
		$this->assertSame($converter, $result);
	}

	/* ===================== Round-trip conversion ===================== */

	public function testRoundTripConversion(): void
	{
		$converter = new XMLConverter();

		// Array -> XML -> Array
		$originalArray = [
			'name' => 'Test',
			'value' => '123',
		];

		$xml = $converter->convertFromArray($originalArray, 'root');
		$this->assertIsString($xml);

		$resultArray = $converter->convertToArray($xml);
		$this->assertIsArray($resultArray);
		$this->assertArrayHasKey('root', $resultArray);
		$this->assertSame('Test', $resultArray['root']['name']);
		$this->assertSame('123', $resultArray['root']['value']);
	}
}