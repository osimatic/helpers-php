<?php

namespace Osimatic\Text;

use Osimatic\FileSystem\OutputFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Utility class for working with DOCX (Office Open XML WordprocessingML) files.
 * Provides methods for validating, outputting and serving DOCX files.
 *
 * @link https://www.iana.org/assignments/media-types/application/vnd.openxmlformats-officedocument.wordprocessingml.document IANA MIME type
 * @link https://en.wikipedia.org/wiki/Office_Open_XML Office Open XML specification
 */
class WordDocument
{
	public const string FILE_EXTENSION = '.docx';
	public const array MIME_TYPES = [
		'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		'application/msword',
		'application/octet-stream',
	];

	// ========== Validation ==========

	/**
	 * Checks if a file is a valid DOCX file based on extension and MIME type.
	 * @param string $filePath The path to the file to check
	 * @param string $clientOriginalName The original file name from client
	 * @return bool True if the file is a valid DOCX file, false otherwise
	 */
	public static function checkFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\FileSystem\File::check($filePath, $clientOriginalName, [self::FILE_EXTENSION], self::MIME_TYPES);
	}

	// ========== Output ==========

	/**
	 * Outputs a DOCX file to the client browser.
	 * No output should be performed before or after calling this function.
	 * @param string $filePath The path to the DOCX file to output
	 * @param string|null $fileName Optional file name to send to the client
	 */
	public static function output(string $filePath, ?string $fileName=null): void
	{
		\Osimatic\FileSystem\File::output($filePath, $fileName, self::MIME_TYPES[0]);
	}

	/**
	 * Outputs a DOCX file to the client browser.
	 * No output should be performed before or after calling this function.
	 * @param OutputFile $file The OutputFile object containing the file to output
	 */
	public static function outputFile(OutputFile $file): void
	{
		\Osimatic\FileSystem\File::outputFile($file, self::MIME_TYPES[0]);
	}

	/**
	 * Creates an HTTP response for a DOCX file.
	 * @param string $filePath The path to the DOCX file
	 * @param string|null $fileName Optional file name for the response
	 * @return Response|null The HTTP response object, or null if file not found
	 */
	public static function getHttpResponse(string $filePath, ?string $fileName=null): ?Response
	{
		return \Osimatic\FileSystem\File::getHttpResponse($filePath, $fileName, true, self::MIME_TYPES[0]);
	}
}