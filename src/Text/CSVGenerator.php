<?php

namespace Osimatic\Text;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CSVGenerator
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
	 * @param array $data
	 * @param string|null $title
	 * @param array $defaultContext
	 * @return bool
	 */
	public function generateFile(string $filePath, array $data, ?string $title=null, array $defaultContext=[]): bool
	{
		\Osimatic\FileSystem\FileSystem::initializeFile($filePath);

		$serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder(array_merge([CsvEncoder::DELIMITER_KEY => ';', CsvEncoder::OUTPUT_UTF8_BOM_KEY => true], $defaultContext))]);

		if (!empty($title)) {
			$data = array_merge([[$title]], $data);
		}

		$str = $serializer->encode($data, 'csv');
		$str = substr($str, strpos($str, "\n")+strlen("\n"));
		$str = chr(0xEF).chr(0xBB).chr(0xBF).$str; // UTF-8 BOM pour forcer l'UTF8 sur Excel
		$nbBytes = file_put_contents($filePath, $str, FILE_APPEND);

		if (false === $nbBytes) {
			$this->logger->error('Error writing CSV file: '.$filePath);
			return false;
		}

		$this->logger->info('New CSV file generated: '.$filePath.'. Nb bytes: '.$nbBytes);
		return true;
	}
}