<?php

namespace Osimatic\Text;

use Osimatic\FileSystem\OutputFile;
use Symfony\Component\HttpFoundation\Response;

class XML
{
	public const string FILE_EXTENSION = '.xml';
	public const array MIME_TYPES = [
		'application/xml',
		'text/xml',
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
	 * @param string $filePath
	 * @param string|null $fileName
	 */
	public static function output(string $filePath, ?string $fileName=null): void
	{
		\Osimatic\FileSystem\File::output($filePath, $fileName, 'text/xml');
	}

	/**
	 * @param OutputFile $file
	 */
	public static function outputFile(OutputFile $file): void
	{
		\Osimatic\FileSystem\File::outputFile($file, 'text/xml');
	}

	/**
	 * @param string $filePath
	 * @param string|null $fileName
	 * @return Response
	 */
	public static function getHttpResponse(string $filePath, ?string $fileName=null): Response
	{
		return \Osimatic\FileSystem\File::getHttpResponse($filePath, $fileName, false, 'text/xml');
	}

	// ========== Conversion ==========

	/**
	 * @param string $xmlContent
	 * @return array|null
	 */
	public static function convertToArray(string $xmlContent): ?array
	{
		$xmlConverter = new XMLConverter();
		return $xmlConverter->convertToArray($xmlContent);
	}
}