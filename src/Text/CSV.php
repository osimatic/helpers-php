<?php

namespace Osimatic\Helpers\Text;

use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CSV
{
	const FILE_EXTENSION = '.csv';
	const MIME_TYPES = [
		'text/csv',
		'txt/csv',
		'application/octet-stream',
		'application/csv-tab-delimited-table',
		'application/vnd.ms-excel',
		'application/vnd.ms-pki.seccat',
		'text/plain',
	];

	// ========== Vérification ==========

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\Helpers\FileSystem\File::check($filePath, $clientOriginalName, [self::FILE_EXTENSION], self::MIME_TYPES);
	}

	// ========== Lecture ==========

	/**
	 * @link http://gist.github.com/385876
	 * @param string $filename
	 * @param string $delimiter
	 * @return array|null
	 */
	public static function toArray(string $filename, string $delimiter=','): ?array
	{
		if (!file_exists($filename) || !is_readable($filename)) {
			return null;
		}

		$header = null;
		$data = [];
		if (($handle = fopen($filename, 'r')) !== false) {
			while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
				if (!$header) {
					$header = $row;
				}
				else {
					$data[] = array_combine($header, $row);
				}
			}
			fclose($handle);
		}
		return $data;
	}

	// ========== Affichage ==========

	/**
	 * @param string $csvFilePath
	 * @param string|null $fileName
	 */
	public static function output(string $csvFilePath, ?string $fileName=null): void
	{
		if (!headers_sent()) {
			header('Content-disposition: attachment; filename="'.($fileName ?? basename($csvFilePath)).'"');
			header('Content-Type: application/force-download');
			header('Content-Transfer-Encoding: text/csv');
			header('Content-Length: ' .filesize($csvFilePath));
			header('Pragma: no-cache');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0, public');
			header('Expires: 0');
			readfile($csvFilePath);
		}
	}

	// ========== Ecriture ==========

	private $tableHead = [];
	private $tableBody = [];
	private $tableFoot = [];

	/**
	 * @param $cell
	 * @return self
	 */
	public function addTableHeadCell($cell): self
	{
		$this->tableHead[] = $cell;

		return $this;
	}

	/**
	 * @param array $line
	 * @return self
	 */
	public function addTableLine(array $line): self
	{
		$this->tableBody[] = $line;

		return $this;
	}

	/**
	 * @param $cell
	 * @return self
	 */
	public function addTableFootCell($cell): self
	{
		$this->tableFoot[] = $cell;

		return $this;
	}

	/**
	 * @param string $filePath
	 * @return bool
	 */
	public function save(string $filePath): bool
	{
		return self::generateFile($filePath, $this->tableHead, $this->tableBody, $this->tableFoot);
	}

	/**
	 * @param string $filePath
	 * @param array|null $tableHead
	 * @param array $tableBody
	 * @param array|null $tableFoot
	 * @return bool
	 */
	public static function generateFile(string $filePath, ?array $tableHead, array $tableBody, ?array $tableFoot): bool
	{
		\Osimatic\Helpers\FileSystem\FileSystem::initializeFile($filePath);

		$serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder([CsvEncoder::DELIMITER_KEY => ';'])]);

		foreach ($tableBody as $key => $line) {
			$tableBody[$key] = array_values($line);
		}
		$table = $tableBody;

		if (!empty($tableHead)) {
			$tableHead = array_values($tableHead);
			$table = array_merge([$tableHead], $table);
		}

		if (!empty($tableFoot)) {
			$tableFoot = array_values($tableFoot);
			$table = array_merge($table, [$tableFoot]);
		}

		$str = $serializer->encode($table, 'csv');
		$str = substr($str, strpos($str, "\n")+strlen("\n"));
		$str = utf8_decode($str);
		file_put_contents($filePath, $str, FILE_APPEND);

		return true;
	}

}