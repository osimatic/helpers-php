<?php

namespace Osimatic\Text;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Utility class for generating XML files from PHP arrays.
 * Converts PHP arrays to XML format with customizable root tag, attributes, and document type.
 * For XML validation and parsing, see XML. For XML conversion utilities, see XMLConverter.
 */
class XMLGenerator
{
	public function __construct(
		private LoggerInterface $logger=new NullLogger(),
	) {}

	/**
	 * Sets the logger for error and debugging information.
	 * @param LoggerInterface $logger The logger instance
	 * @return self Returns this instance for method chaining
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * Generates an XML file from a PHP array structure.
	 * @param string $filePath The path where the XML file will be created
	 * @param array $array The PHP array to convert to XML
	 * @param string $firstTag The name of the root XML element (default: 'Document')
	 * @param string|null $firstTagAttributes Optional attributes for the root element (e.g., 'xmlns="..." version="1.0"')
	 * @param array $docType Optional document type declaration parameters
	 * @return bool True if the XML file was successfully generated, false on error
	 */
	public function generateFile(string $filePath, array $array, string $firstTag='Document', ?string $firstTagAttributes=null, array $docType = []): bool
	{
		\Osimatic\FileSystem\FileSystem::initializeFile($filePath);

		$xmlConverter = new XMLConverter($this->logger);
		$xml = $xmlConverter->convertFromArray($array, $firstTag, $docType);

		if (!empty($firstTagAttributes)) {
			$xml = str_replace('<'.$firstTag.'>', '<'.$firstTag.' '.$firstTagAttributes.'>', $xml);
		}

		file_put_contents($filePath, $xml, FILE_APPEND);

		return true;
	}

}