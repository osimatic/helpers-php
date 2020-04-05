<?php

namespace Osimatic\Helpers\FileSystem;

class File
{
	/**
	 * @param string $realPath
	 * @param string $clientOriginalName
	 * @param array $extensionsAllowed
	 * @param array|null $mimeTypesAllowed
	 * @return bool
	 */
	public static function check(string $realPath, string $clientOriginalName, array $extensionsAllowed, ?array $mimeTypesAllowed=null): bool
	{
		if (empty($realPath) || !file_exists($realPath)) {
			return false;
		}

		$extension = strtolower('.'.pathinfo($clientOriginalName, PATHINFO_EXTENSION));
		if (empty($extension) || !in_array($extension, $extensionsAllowed)) {
			return false;
		}

		if (!empty($mimeTypesAllowed)) {
			$fileType = mime_content_type($realPath);
			if (!in_array($fileType, $mimeTypesAllowed)) {
				return false;
			}
		}

		return true;
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

	/**
	 * Retourne la taille plus l'unité arrondie
	 * @param float $bytes taille en octets
	 * @param int $numberOfDecimalPlaces le nombre de chiffre après la virgule pour l'affichage du nombre correspondant à la taille
	 * @return string chaine de caractères formatée
	 */
	public static function formatSize(float $bytes, int $numberOfDecimalPlaces=2): string
	{
		$units = ['B', 'KB', 'MB', 'GB', 'TB'];
		switch (strtoupper(substr(\Locale::getDefault(), 0, 2))) {
			case 'FR': $units = ['o', 'Ko', 'Mo', 'Go', 'To']; break;
		}

		$b = $bytes;

		// Cas des tailles de fichier négatives
		if ($b > 0) {
			$e = (int)(log($b,1024));
			// Si on a pas l'unité on retourne en To
			if (isset($units[$e]) === false) {
				$e = 4;
			}
			$b = $b/pow(1024,$e);
		}
		else {
			$b = 0;
			$e = 0;
		}
		$format = '%.'.$numberOfDecimalPlaces.'f';
		$float = sprintf($format, $b);

		return $float.' '.$units[$e];
	}
}