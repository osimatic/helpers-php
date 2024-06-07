<?php

namespace Osimatic\Text;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class XMLGenerator
{
	public function __construct(
		private LoggerInterface $logger=new NullLogger(),
	) {}

	/**
	 * @param LoggerInterface $logger
	 * @return self
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * @param string $filePath
	 * @param array $array
	 * @param string $firstTag
	 * @param string|null $firstTagAttributes
	 * @param array $docType
	 * @return bool
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