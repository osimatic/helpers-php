<?php

namespace Osimatic\FileSystem;

class FileSystem
{
	/**
	 * Formate un chemin (chemin complet depuis la racine disque ou en chemin relatif) :
	 * - retire les '//' et les './' inutiles
	 * - remplace les '\' et '/' par la constante PHP "DIRECTORY_SEPARATOR"
	 * @param string $filePath Le chemin à formater.
	 * @return string Le chemin formaté.
	 */
	public static function formatPath(string $filePath): string
	{
		// Retire les './' inutiles
		$filePath = preg_replace('#\/\.\/#', DIRECTORY_SEPARATOR, $filePath);

		$isUnc = (str_starts_with($filePath, '\\\\'));
		if ($isUnc) {
			$filePath = substr($filePath, 2);
		}
		//$filePath = ($isUnc?substr($filePath, 2):$filePath);

		// Retire les '//' et \\ inutiles
		$filePath = preg_replace('#\/(\/)*\/#', DIRECTORY_SEPARATOR, $filePath);
		$filePath = preg_replace('#\\\\(\\\\)*\\\\#m', DIRECTORY_SEPARATOR, $filePath);

		// Remplace les '\' par des '/'
		$filePath = preg_replace('#\\\\#m', DIRECTORY_SEPARATOR, $filePath);
		$filePath = preg_replace('#\/#', DIRECTORY_SEPARATOR, $filePath);

		return ($isUnc?'\\\\':'').$filePath;
	}

	/**
	 * Retourne le chemin complet sans le nom de fichier
	 * @param string $filePath
	 * @return string
	 */
	public static function dirname(string $filePath): string
	{
		$dirPath = self::formatPath($filePath);
		if (substr($filePath, -1) !== DIRECTORY_SEPARATOR) {
			$dirPath = dirname($dirPath).DIRECTORY_SEPARATOR;
		}
		return $dirPath;
	}

	/**
	 * Crée tous les répertoires appartenant au chemin (du répertoire racine au dernier répertoire), s'ils n'existent pas encore.
	 * @param string $filePath Le chemin complet vers le répertoire à créer (à partir de la racine disque).
	 * @return boolean true si les répertoires appartenant au chemin ont bien été crées, false si une erreur survient.
	 */
	public static function createDirectories(string $filePath): bool
	{
		$dirPath = self::dirname($filePath);

		if (file_exists($dirPath)) {
			return true;
		}

		return mkdir($dirPath, 0777, true);

		/*
		$currentPath = '';
		$directories = explode(DIRECTORY_SEPARATOR, $dirPath);
		if (!empty($directories)) {
			foreach ($directories as $key => $directory) {
				$currentPath .= $directory.DIRECTORY_SEPARATOR;

				if ((strpos($currentPath, '\\\\') === 0 && $key < 1) || file_exists($currentPath)) {
					continue;
				}

				if (!mkdir($currentPath) && !is_dir($currentPath)) {
					return false;
				}
			}
		}
		return true;
		*/
	}

	/**
	 * Supprime le fichier si le fichier existe et crée tous les répertoires appartenant au chemin (du répertoire racine au dernier répertoire), s'ils n'existent pas encore.
	 * @param string $filePath Le chemin complet vers le fichier.
	 */
	public static function initializeFile(string $filePath): void
	{
		// Suppression du fichier s'il existe déjà
		if (file_exists($filePath)) {
			unlink($filePath);
		}

		// Création des répertoires où se trouvera le fichier
		if (!file_exists(dirname($filePath))) {
			self::createDirectories($filePath);
		}
	}

}