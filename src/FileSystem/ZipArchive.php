<?php

namespace Osimatic\Helpers\FileSystem;

class ZipArchive
{
	const FILE_EXTENSION = '.zip';
	const MIME_TYPES = [
		'application/x-rar',
		'application/x-rar-compressed',
		'application/zip',
		'application/x-zip',
		'application/x-zip-compressed',
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
		\Osimatic\Helpers\FileSystem\File::output($filePath, $fileName);
	}

	/**
	 * Crée une archive zip contenant la liste des fichiers passée en paramètre.
	 * @param string $filePath Le chemin de l'archive à créer
	 * @param array $files Un tableau contenant la liste des fichiers à ajouter dans l'archive.
	 */
	public static function archive(string $filePath, array $files): void
	{
		\Osimatic\Helpers\FileSystem\FileSystem::initializeFile($filePath);

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
	 * Crée une archive zip contenant les fichiers avec leur contenu passée en paramètre.
	 * @param string $filePath Le chemin de l'archive à créer
	 * @param array $contentFiles Un tableau de contenu avec en clé le nom de fichier dans l'archive et en valeur le contenu du fichier correspondant.
	 */
	public static function archiveFilesFromString(string $filePath, array $contentFiles): void
	{
		\Osimatic\Helpers\FileSystem\FileSystem::initializeFile($filePath);

		$zip = new \ZipArchive();
		$zip->open($filePath, \ZipArchive::CREATE);
		foreach ($contentFiles as $filenamr => $content) {
			$zip->addFromString($filenamr, $content);
		}
		$zip->close();
	}

}