<?php

namespace Osimatic\Text;

use Osimatic\FileSystem\OutputFile;
use Symfony\Component\HttpFoundation\Response;

class CSV
{
	public const string FILE_EXTENSION = '.csv';
	public const array MIME_TYPES = [
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
		return \Osimatic\FileSystem\File::check($filePath, $clientOriginalName, [self::FILE_EXTENSION], self::MIME_TYPES);
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
		\Osimatic\FileSystem\File::output($filePath, $fileName, 'text/csv');
	}

	/**
	 * Envoi au navigateur du client un fichier CSV.
	 * Aucun affichage ne doit être effectué avant ou après l'appel à cette fonction.
	 * @param OutputFile $file
	 */
	public static function outputFile(OutputFile $file): void
	{
		\Osimatic\FileSystem\File::outputFile($file, 'text/csv');
	}

	/**
	 * @param string $filePath
	 * @param string|null $fileName
	 * @return Response
	 */
	public static function getHttpResponse(string $filePath, ?string $fileName=null): Response
	{
		return \Osimatic\FileSystem\File::getHttpResponse($filePath, $fileName, false, 'text/csv');
	}

	// ========== Conversion ==========

	/**
	 * @link http://gist.github.com/385876
	 * @param string $filename
	 * @param string $delimiter
	 * @return array|null
	 */
	public static function convertToArray(string $filename, string $delimiter=','): ?array
	{
		if (!file_exists($filename) || !is_readable($filename)) {
			return null;
		}

		$header = null;
		$data = [];
		if (false === ($handle = fopen($filename, 'rb'))) {
			return null;
		}

		while (($row = fgetcsv($handle, 1000, $delimiter)) !== false) {
			if (!$header) {
				$header = $row;
			}
			else {
				$data[] = array_combine($header, $row);
			}
		}
		fclose($handle);

		return $data;
	}

	// ========== Divers ==========

	/**
	 * @param string|null $value
	 * @return string
	 */
	public static function parseSeparator(?string $value): string
	{
		return ';' === $value ? ';' : ',';
	}

	/**
	 * @param string|int|float|null $value
	 * @return string
	 */
	public static function forceStringForExcel(string|int|float|null $value): string
	{
		return null !== $value && '' !== $value ? '="'.$value.'"' : '';
	}
}