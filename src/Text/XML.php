<?php

namespace Osimatic\Text;

use Osimatic\FileSystem\OutputFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Utility class for working with XML (eXtensible Markup Language) data.
 * Provides methods for parsing, validating, converting and outputting XML.
 */
class XML
{
	public const string FILE_EXTENSION = '.xml';
	public const array MIME_TYPES = [
		'application/xml',
		'text/xml',
	];

	// ========== Validation ==========

	/**
	 * Checks if a file is a valid XML file based on extension and MIME type.
	 * @param string $filePath The path to the file to check
	 * @param string $clientOriginalName The original file name from client
	 * @return bool True if the file is a valid XML file, false otherwise
	 */
	public static function checkFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\FileSystem\File::check($filePath, $clientOriginalName, [self::FILE_EXTENSION], self::MIME_TYPES);
	}

	/**
	 * Validates an XML string for well-formedness and optionally against an XSD schema.
	 * @param string $xml The XML string to validate
	 * @param string|null $xsd Optional XSD schema file path for validation
	 * @return bool True if the XML is valid, false otherwise
	 */
	public static function validate(string $xml, ?string $xsd=null): bool
	{
		$previousSetting = libxml_use_internal_errors(true);
		libxml_clear_errors();

		$doc = new \DOMDocument();
		$valid = $doc->loadXML($xml);

		if ($valid && $xsd !== null && file_exists($xsd)) {
			$valid = $doc->schemaValidate($xsd);
		}

		libxml_use_internal_errors($previousSetting);
		return $valid;
	}

	// ========== Output ==========

	/**
	 * Outputs an XML file to the client browser.
	 * No output should be performed before or after calling this function.
	 * @param string $filePath The path to the XML file to output
	 * @param string|null $fileName Optional file name to send to the client
	 */
	public static function output(string $filePath, ?string $fileName=null): void
	{
		\Osimatic\FileSystem\File::output($filePath, $fileName, 'text/xml');
	}

	/**
	 * Outputs an XML file to the client browser.
	 * No output should be performed before or after calling this function.
	 * @param OutputFile $file The OutputFile object containing the file to output
	 */
	public static function outputFile(OutputFile $file): void
	{
		\Osimatic\FileSystem\File::outputFile($file, 'text/xml');
	}

	/**
	 * Creates an HTTP response for an XML file.
	 * @param string $filePath The path to the XML file
	 * @param string|null $fileName Optional file name for the response
	 * @return Response The HTTP response object
	 */
	public static function getHttpResponse(string $filePath, ?string $fileName=null): Response
	{
		return \Osimatic\FileSystem\File::getHttpResponse($filePath, $fileName, false, 'text/xml');
	}

	// ========== Parsing ==========

	/**
	 * Parses an XML string into a SimpleXMLElement object.
	 * @param string $xml The XML string to parse
	 * @param string $className Optional class name for the result object (default: SimpleXMLElement)
	 * @param int $options Optional bitmask of LIBXML_ constants
	 * @return \SimpleXMLElement|null The parsed XML object, or null on error
	 */
	public static function parse(string $xml, string $className='SimpleXMLElement', int $options=0): ?\SimpleXMLElement
	{
		$previousSetting = libxml_use_internal_errors(true);
		libxml_clear_errors();

		$result = simplexml_load_string($xml, $className, $options);

		libxml_use_internal_errors($previousSetting);
		return $result !== false ? $result : null;
	}

	/**
	 * Parses an XML file into a SimpleXMLElement object.
	 * @param string $filePath The path to the XML file
	 * @param string $className Optional class name for the result object (default: SimpleXMLElement)
	 * @param int $options Optional bitmask of LIBXML_ constants
	 * @return \SimpleXMLElement|null The parsed XML object, or null on error
	 */
	public static function parseFile(string $filePath, string $className='SimpleXMLElement', int $options=0): ?\SimpleXMLElement
	{
		if (!file_exists($filePath)) {
			return null;
		}

		$previousSetting = libxml_use_internal_errors(true);
		libxml_clear_errors();

		$result = simplexml_load_file($filePath, $className, $options);

		libxml_use_internal_errors($previousSetting);
		return $result !== false ? $result : null;
	}

	// ========== Conversion ==========

	/**
	 * Converts an XML string to an associative array.
	 * @param string $xmlContent The XML content to convert
	 * @return array|null The converted array, or null on error
	 */
	public static function convertToArray(string $xmlContent): ?array
	{
		$xmlConverter = new XMLConverter();
		return $xmlConverter->convertToArray($xmlContent);
	}

	/**
	 * Converts an array to an XML string.
	 * @param array $data The array to convert
	 * @param string $rootElement The name of the root element (default: 'root')
	 * @param string $version XML version (default: '1.0')
	 * @param string $encoding XML encoding (default: 'UTF-8')
	 * @return string The XML string
	 */
	public static function arrayToXml(array $data, string $rootElement='root', string $version='1.0', string $encoding='UTF-8'): string
	{
		$xml = new \SimpleXMLElement("<?xml version=\"{$version}\" encoding=\"{$encoding}\"?><{$rootElement}></{$rootElement}>");
		self::arrayToXmlRecursive($data, $xml);
		return $xml->asXML();
	}

	/**
	 * Recursive helper method for array to XML conversion.
	 * @param array $data The array data
	 * @param \SimpleXMLElement $xml The XML element to populate
	 */
	private static function arrayToXmlRecursive(array $data, \SimpleXMLElement $xml): void
	{
		foreach ($data as $key => $value) {
			if (is_array($value)) {
				if (is_numeric($key)) {
					$key = 'item';
				}
				$subnode = $xml->addChild($key);
				self::arrayToXmlRecursive($value, $subnode);
			}
			else {
				$xml->addChild($key, htmlspecialchars((string)$value));
			}
		}
	}

	/**
	 * Gets the last XML parsing errors.
	 * @return array Array of LibXMLError objects
	 */
	public static function getLastErrors(): array
	{
		return libxml_get_errors();
	}
}