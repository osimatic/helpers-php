<?php

namespace Osimatic\FileSystem;

use Symfony\Component\HttpFoundation\Response;

class ZipArchive
{
	public const string FILE_EXTENSION = '.zip';
	public const array MIME_TYPES = [
		'application/zip',
		'application/x-zip',
		'application/x-zip-compressed',
		'application/x-rar',
		'application/x-rar-compressed',
		'application/octet-stream',
	];

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkFile(string $filePath, string $clientOriginalName): bool
	{
		return File::check($filePath, $clientOriginalName, [self::FILE_EXTENSION], self::MIME_TYPES);
	}

	/**
	 * Envoi au navigateur du client un fichier zip.
	 * Aucun affichage ne doit être effectué avant ou après l'appel à cette fonction.
	 * @param string $filePath
	 * @param string|null $fileName
	 */
	public static function output(string $filePath, ?string $fileName=null): void
	{
		File::download($filePath, $fileName);
	}

	/**
	 * Envoi au navigateur du client un fichier zip.
	 * Aucun affichage ne doit être effectué avant ou après l'appel à cette fonction.
	 * @param OutputFile $file
	 */
	public static function outputFile(OutputFile $file): void
	{
		File::downloadFile($file);
	}

	/**
	 * @param string $filePath
	 * @param string|null $fileName
	 * @return Response
	 */
	public static function getHttpResponse(string $filePath, ?string $fileName=null): Response
	{
		return File::getHttpResponse($filePath, $fileName, true);
	}

	/**
	 * Crée une archive zip contenant la liste des fichiers passée en paramètre.
	 * @param string $filePath Le chemin de l'archive à créer
	 * @param string[] $files Un tableau contenant la liste des fichiers à ajouter dans l'archive.
	 */
	public static function archive(string $filePath, array $files): void
	{
		FileSystem::initializeFile($filePath);

		$zip = new \ZipArchive();
		$zip->open($filePath, \ZipArchive::CREATE);
		foreach ($files as $f) {
			if (file_exists($f)) {
				$zip->addFile($f, basename($f));
			}
		}
		$zip->close();
	}

	/**
	 * Crée une archive zip contenant la liste des fichiers passée en paramètre.
	 * @param string $filePath Le chemin de l'archive à créer
	 * @param OutputFile[] $files Un tableau contenant la liste des fichiers à ajouter dans l'archive.
	 */
	public static function archiveOutputFiles(string $filePath, array $files): void
	{
		FileSystem::initializeFile($filePath);

		$zip = new \ZipArchive();
		$zip->open($filePath, \ZipArchive::CREATE);
		foreach ($files as $outputFile) {
			if (null !== ($currentFilePath = $outputFile->getFilePath()) && file_exists($currentFilePath)) {
				$zip->addFile($currentFilePath, $outputFile->getFileName() ?? basename($currentFilePath));
			}
		}
		$zip->close();
	}

	/**
	 * Crée une archive zip contenant les fichiers avec leur contenu passée en paramètre.
	 * @param string $filePath Le chemin de l'archive à créer
	 * @param array $contentFiles Un tableau de contenu avec en clé le nom de fichier dans l'archive et en valeur le contenu du fichier correspondant.
	 */
	public static function archiveFilesFromString(string $filePath, array $contentFiles): void
	{
		FileSystem::initializeFile($filePath);

		$zip = new \ZipArchive();
		$zip->open($filePath, \ZipArchive::CREATE);
		foreach ($contentFiles as $filename => $content) {
			$zip->addFromString($filename, $content);
		}
		$zip->close();
	}

}