<?php

namespace Osimatic\Text;

use Osimatic\FileSystem\OutputFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * Utility class for working with PDF (Portable Document Format) files.
 * Provides methods for validating, outputting and extracting information from PDF files.
 * For PDF generation, see PDFGenerator. For PDF conversion, see PDFConverter. For PDF merging, see PDFMerger.
 */
class PDF
{
	public const string FILE_EXTENSION = '.pdf';
	public const array MIME_TYPES = [
		'application/pdf',
		'application/x-pdf',
		'application/vnd.cups-pdf',
		'application/vnd.sealedmedia.softseal.pdf',
	];

	// ========== Validation ==========

	/**
	 * Checks if a file is a valid PDF file based on extension and MIME type.
	 * @param string $filePath The path to the file to check
	 * @param string $clientOriginalName The original file name from client
	 * @return bool True if the file is a valid PDF file, false otherwise
	 */
	public static function checkFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\FileSystem\File::check($filePath, $clientOriginalName, [self::FILE_EXTENSION], self::MIME_TYPES);
	}

	// ========== Output ==========

	/**
	 * Outputs a PDF file to the client browser for inline viewing.
	 * No output should be performed before or after calling this function.
	 * @param string $filePath The path to the PDF file to output
	 * @param string|null $fileName Optional file name to send to the client
	 */
	public static function output(string $filePath, ?string $fileName=null): void
	{
		\Osimatic\FileSystem\File::output($filePath, $fileName, 'application/pdf');
	}

	/**
	 * Outputs a PDF file to the client browser for inline viewing.
	 * No output should be performed before or after calling this function.
	 * @param OutputFile $file The OutputFile object containing the file to output
	 */
	public static function outputFile(OutputFile $file): void
	{
		\Osimatic\FileSystem\File::outputFile($file, 'application/pdf');
	}

	/**
	 * Forces download of a PDF file to the client browser.
	 * No output should be performed before or after calling this function.
	 * @param string $filePath The path to the PDF file to download
	 * @param string|null $fileName Optional file name for the download
	 */
	public static function download(string $filePath, ?string $fileName=null): void
	{
		\Osimatic\FileSystem\File::download($filePath, $fileName);
	}

	/**
	 * Forces download of a PDF file to the client browser.
	 * No output should be performed before or after calling this function.
	 * @param OutputFile $file The OutputFile object containing the file to download
	 */
	public static function downloadFile(OutputFile $file): void
	{
		\Osimatic\FileSystem\File::downloadFile($file);
	}

	/**
	 * Creates an HTTP response for a PDF file.
	 * @param string $filePath The path to the PDF file
	 * @param string|null $fileName Optional file name for the response
	 * @return Response The HTTP response object
	 */
	public static function getHttpResponse(string $filePath, ?string $fileName=null): Response
	{
		return \Osimatic\FileSystem\File::getHttpResponse($filePath, $fileName, false, 'application/pdf');
	}

}