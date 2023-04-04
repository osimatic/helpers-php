<?php

namespace Osimatic\Helpers\Text;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Encoder\CsvEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

class CSV
{
	public const FILE_EXTENSION = '.csv';
	public const MIME_TYPES = [
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
		if (($handle = fopen($filename, 'rb')) !== false) {
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
	 * Envoi au navigateur du client un fichier CSV.
	 * Aucun affichage ne doit être effectué avant ou après l'appel à cette fonction.
	 * @param string $filePath
	 * @param string|null $fileName
	 */
	public static function output(string $filePath, ?string $fileName=null): void
	{
		\Osimatic\Helpers\FileSystem\File::output($filePath, $fileName, 'text/csv');
	}

	/**
	 * @param string $filePath
	 * @param string|null $fileName
	 * @return Response
	 */
	public static function getHttpResponse(string $filePath, ?string $fileName=null): Response
	{
		return \Osimatic\Helpers\FileSystem\File::getHttpResponse($filePath, $fileName, false, 'text/csv');
	}

	// ========== Ecriture ==========

	private $title;
	private $tableHead = [];
	private $tableBody = [];
	private $tableFoot = [];

	/**
	 * @param string $title
	 * @return self
	 */
	public function setTitle(string $title): self
	{
		$this->title = $title;

		return $this;
	}

	/**
	 * @param array|null $tableHead
	 * @param array $tableBody
	 * @param array|null $tableFoot
	 * @return self
	 */
	public function setTable(?array $tableHead, array $tableBody, ?array $tableFoot): self
	{
		if (!empty($tableHead)) {
			$this->tableHead = $tableHead;
		}

		$this->tableBody = $tableBody;

		if (!empty($tableFoot)) {
			$this->tableFoot = $tableFoot;
		}

		return $this;
	}

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
		return self::generateFile($filePath, $this->tableHead, $this->tableBody, $this->tableFoot, $this->title);
	}

	/**
	 * @param string|int|float|null $value
	 * @return string
	 */
	public static function forceStringForExcel(string|int|float|null $value): string
	{
		return null !== $value && '' !== $value ? '="'.$value.'"' : '';
	}

	/**
	 * @param string $filePath
	 * @param array|null $tableHead
	 * @param array $tableBody
	 * @param array|null $tableFoot
	 * @param string|null $title
	 * @return bool
	 */
	public static function generateFile(string $filePath, ?array $tableHead, array $tableBody, ?array $tableFoot, ?string $title=null): bool
	{
		\Osimatic\Helpers\FileSystem\FileSystem::initializeFile($filePath);

		$serializer = new Serializer([new ObjectNormalizer()], [new CsvEncoder([CsvEncoder::DELIMITER_KEY => ';', CsvEncoder::OUTPUT_UTF8_BOM_KEY => true])]);

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

		if (!empty($title)) {
			$table = array_merge([[$title]], $table);
		}

		$str = $serializer->encode($table, 'csv');
		$str = substr($str, strpos($str, "\n")+strlen("\n"));
		$str = chr(0xEF).chr(0xBB).chr(0xBF).$str; // UTF-8 BOM pour forcer l'UTF8 sur Excel
		file_put_contents($filePath, $str, FILE_APPEND);

		return true;
	}

}