<?php

namespace Osimatic\Text;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class PDFMerger
{
	public function __construct(
		private LoggerInterface $logger = new NullLogger(),
		private ?string $pdfToolkitBinaryPath = null,
	) {}

	/**
	 * Set the logger to use to log debugging data.
	 * @param LoggerInterface $logger
	 * @return self
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * @param string $pdfToolkitBinaryPath
	 * @return self
	 */
	public function setPdfToolkitBinaryPath(string $pdfToolkitBinaryPath): self
	{
		$this->pdfToolkitBinaryPath = $pdfToolkitBinaryPath;

		return $this;
	}

	/**
	 * @param array $listPdfPath
	 * @param string $newPdfPath
	 * @param int $profondeur
	 * @return bool
	 */
	public function mergeFiles(array $listPdfPath, string $newPdfPath, int $profondeur=0): bool
	{
		$this->logger->info('Intégration de '.count($listPdfPath).' fichiers PDF vers un fichier PDF unique "'.$newPdfPath.'".');

		\Osimatic\FileSystem\FileSystem::initializeFile($newPdfPath);

		// Vérification que tous les fichiers existent bien
		if ($profondeur === 0) {
			foreach ($listPdfPath as $pdfPath) {
				if (!file_exists($pdfPath)) {
					$this->logger->error('Le fichier PDF "'.$pdfPath.'" à intégrer n\'existe pas.');
					return false;
				}
			}
		}

		// Création de la ligne de commande
		$listPdfStringFormat = '';
		$nbFile = 0;
		while (($pdfPath = array_shift($listPdfPath)) !== null) {
			$listPdfStringFormat .= '"'.$pdfPath.'" ';
			$nbFile++;

			// todo : faire en fonction de la taille des noms de fichier (la ligne de commande est limité à un certain nombre de caractère)
			if ($nbFile >= 20 && !empty($listPdfPath)) {
				$filePathTemp = sys_get_temp_dir().'/'.uniqid(md5(mt_rand()), true).PDF::FILE_EXTENSION;
				$this->mergeFiles($listPdfPath, $filePathTemp, $profondeur+1);

				$listPdfStringFormat .= '"'.$filePathTemp.'" ';
				break;
			}
		}

		$params = ' %s cat output "'.$newPdfPath.'" dont_ask';
		$params = sprintf($params, $listPdfStringFormat);
		$commandLine = $this->pdfToolkitBinaryPath.' '.$params;

		// Envoi de la commande
		$this->logger->info('Ligne de commande exécutée : '.$commandLine);
		system($commandLine);

		return true;
	}

}