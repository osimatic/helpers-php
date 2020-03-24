<?php

namespace Osimatic\Helpers;

class FileSystem
{
	/**
	 * Formate un chemin (chemin complet depuis la racine disque ou en chemin relatif) :
	 * - remplace les '\' par des '/'
	 * - retire les '//' et les './' inutiles
	 * @param string $filePath Le chemin à formater.
	 * @return string Le chemin formaté.
	 */
	public static function formatPath(string $filePath): string
	{
		// Retire les './' inutiles
		$filePath = str_replace('/./', DIRECTORY_SEPARATOR, $filePath);

		// Retire les '//' inutiles
		$filePath = preg_replace('#\/(\/)*\/#', DIRECTORY_SEPARATOR, $filePath);

		// Remplace les '\' par des '/'
		$isUnc = substr($filePath, 0, 2) == '\\\\';
		$filePath = str_replace('#\\\/#', DIRECTORY_SEPARATOR, $filePath);
		$filePath = ($isUnc?'\\\\'.substr($filePath, 2):$filePath);

		return $filePath;
	}

	/**
	 * Crée tous les répertoires appartenant au chemin (du répertoire racine au dernier répertoire), s'ils n'exitent pas encore.
	 * @param string $filePath Le chemin complet vers le répertoire à créer (à partir de la racine disque).
	 * @return boolean true si les répertoires appartenant au chemin ont bien été crées, fals si une erreur survient.
	 */
	public static function createDirectoriesOfFilePath(string $filePath): bool
	{
		$dirPath = self::formatPath(dirname($filePath).DIRECTORY_SEPARATOR);

		if (file_exists($dirPath)) {
			return true;
		}

		$currentPath = '';
		$directories = explode(DIRECTORY_SEPARATOR, $dirPath);
		if (!empty($directories)) {
			foreach ($directories as $key => $directory) {
				$currentPath .= $directory.DIRECTORY_SEPARATOR;

				if ((strpos($currentPath, '\\\\') === 0 && $key < 1) || file_exists($currentPath)) {
					continue;
				}

				if (!mkdir($currentPath)) {
					return false;
				}
			}
		}
		return true;
	}

}