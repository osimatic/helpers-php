<?php

namespace Osimatic\Text;

use Osimatic\FileSystem\OutputFile;
use Symfony\Component\HttpFoundation\Response;

class PDF
{
	public const string FILE_EXTENSION = '.pdf';
	public const array MIME_TYPES = [
		'application/pdf',
		'application/x-pdf',
		'application/vnd.cups-pdf',
		'application/vnd.sealedmedia.softseal.pdf',
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
	 * Envoi au navigateur du client un fichier PDF.
	 * Aucun affichage ne doit être effectué avant ou après l'appel à cette fonction.
	 * @param string $filePath
	 * @param string|null $fileName
	 */
	public static function output(string $filePath, ?string $fileName=null): void
	{
		\Osimatic\FileSystem\File::output($filePath, $fileName, 'application/pdf');
	}

	/**
	 * Envoi au navigateur du client un fichier PDF.
	 * Aucun affichage ne doit être effectué avant ou après l'appel à cette fonction.
	 * @param OutputFile $file
	 */
	public static function outputFile(OutputFile $file): void
	{
		\Osimatic\FileSystem\File::outputFile($file, 'application/pdf');
	}

	/**
	 * Envoi au navigateur du client un fichier PDF.
	 * Aucun affichage ne doit être effectué avant ou après l'appel à cette fonction.
	 * @param string $filePath
	 * @param string|null $fileName
	 */
	public static function download(string $filePath, ?string $fileName=null): void
	{
		\Osimatic\FileSystem\File::download($filePath, $fileName);
	}

	/**
	 * Envoi au navigateur du client un fichier PDF.
	 * Aucun affichage ne doit être effectué avant ou après l'appel à cette fonction.
	 * @param OutputFile $file
	 */
	public static function downloadFile(OutputFile $file): void
	{
		\Osimatic\FileSystem\File::downloadFile($file);
	}

	/**
	 * @param string $filePath
	 * @param string|null $fileName
	 * @return Response
	 */
	public static function getHttpResponse(string $filePath, ?string $fileName=null): Response
	{
		return \Osimatic\FileSystem\File::getHttpResponse($filePath, $fileName, false, 'application/pdf');
	}

}