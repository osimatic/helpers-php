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
		return \Osimatic\Helpers\FileSystem\File::check($filePath, $clientOriginalName, [self::FILE_EXTENSION], self::MIME_TYPES);
	}

	/**
	 * Envoi au navigateur du client un fichier zip.
	 * Aucun affichage ne doit être effectué avant ou après l'appel à cette fonction.
	 * @param string $zipFilepath
	 * @param string|null $fileName
	 */
	public static function outputBrowser(string $zipFilepath, ?string $fileName=null): void
	{
		if (!headers_sent()) {
			header('Content-disposition: attachment; filename="'.($fileName ?? basename($zipFilepath)).'"');
			header('Content-Type: application/force-download');
			header('Content-Transfer-Encoding: binary'."\n");
			header('Content-Length: '.filesize($zipFilepath));
			header('Pragma: no-cache');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0, public');
			header('Expires: 0');
			//header("Content-type: application/force-download");
			//header("Content-Disposition: attachment; filename=$name");
			readfile($zipFilepath);
		}
	}

	/**
	 * Crée une archive zip contenant la liste des fichiers passée en paramètre.
	 * @param string $filePath Le chemin de l'archive à créer
	 * @param array $files Un tableau contenant la liste des fichiers à ajouter dans l'archive.
	 */
	public static function archive(string $filePath, array $files): void
	{
		if (file_exists($filePath)) {
			unlink($filePath);
		}

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
		if (file_exists($filePath)) {
			unlink($filePath);
		}

		$zip = new \ZipArchive();
		$zip->open($filePath, \ZipArchive::CREATE);
		foreach ($contentFiles as $filenamr => $content) {
			$zip->addFromString($filenamr, $content);
		}
		$zip->close();
	}

}