<?php

namespace Osimatic\Helpers;

class File
{

	/**
	 * Crée une archive zip contenant la liste des fichiers passée en paramètre.
	 * @param $filePath
	 * @param $files
	 */
	public static function archive($filePath, $files)
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