<?php

namespace Osimatic\Helpers\Text;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class PDF
{
	const FILE_EXTENSION = '.pdf';
	const MIME_TYPES = [
		'application/pdf',
		'application/x-pdf',
		'application/vnd.cups-pdf',
		'application/vnd.sealedmedia.softseal.pdf',
	];

	private $header;
	private $footer;
	private $body;

	private $logger;
	private $wkHtmlToPdtBinaryPath;
	private $pdfToImgConverterBinaryPath;
	private $pdfToolkitBinaryPath;

	public function __construct()
	{
		$this->logger = new NullLogger();
	}

	/**
	 * Set the logger to use to log debugging data.
	 * @param LoggerInterface $logger
	 */
	public function setLogger(LoggerInterface $logger)
	{
		$this->logger = $logger;
	}

	// ========== Vérification de PDF ==========

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\Helpers\FileSystem\File::check($filePath, $clientOriginalName, [self::FILE_EXTENSION], self::MIME_TYPES);
	}

	// ========== Génération de PDF ==========

	/**
	 * @param string $html
	 */
	public function setHeader(string $html): void
	{
		$this->header = $html;
	}

	/**
	 * @param string $html
	 */
	public function setFooter(string $html): void
	{
		$this->footer = $html;
	}

	/**
	 * @param string $htmlHeader
	 * @param string $htmlFooter
	 */
	public function setHeaderAndFooter(string $htmlHeader, string $htmlFooter): void
	{
		$this->header = $htmlHeader;
		$this->footer = $htmlFooter;
	}

	/**
	 * @param string $html
	 */
	public function setBody(string $html): void
	{
		$this->body = $html;
	}

	/**
	 * @param string $filePath
	 */
	public function generateFile(string $filePath): void
	{
		if (file_exists($filePath)) {
			unlink($filePath);
		}

		if (!file_exists(dirname($filePath))) {
			\Osimatic\Helpers\FileSystem\FileSystem::createDirectories(dirname($filePath));
		}

		$snappy = new \Knp\Snappy\Pdf();
		$snappy->setBinary($this->wkHtmlToPdtBinaryPath);
		$snappy->setLogger($this->logger);
		if (!empty($this->header)) {
			$snappy->setOption('header-html', $this->header);
		}
		if (!empty($this->footer)) {
			$snappy->setOption('footer-html', $this->footer);
		}

		try {
			$snappy->generateFromHtml($this->body, $filePath);
		}
		catch (\Exception $e) {
			$this->logger->error('Exception lors de la génération du fichier PDF : '.$e->getMessage());
		}
	}


	// ========== Modification de PDF ==========

	/**
	 * @param string $pdfPath
	 * @param string $imagePath
	 * @return bool
	 */
	public function convertToImages(string $pdfPath, string $imagePath): bool
	{
		if (!file_exists($pdfPath)) {
			return false;
		}

		$optionQualiteDoc = '-quality 100 -density 150 ';

		$args = $optionQualiteDoc . $pdfPath . ' ' . $imagePath;
		$commandLine = $this->pdfToImgConverterBinaryPath . ' ' . $args;

		// Envoi de la ligne de commande
		$lastLine = system($commandLine);

		return true;
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

		// Création des dossiers où se trouvera le fichier PDF de destination
		// \Osimatic\Helpers\FileSystem\FileSystem::createDirectories($newPdfPath);

		// Vérification que tous les fichiers existent bien
		if ($profondeur == 0) {
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
				$filePathTemp = sys_get_temp_dir().'/'.uniqid(md5(mt_rand()), true).self::FILE_EXTENSION;
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
		$lastLine = system($commandLine);

		return true;
	}



	/**
	 * @param string $wkHtmlToPdtBinaryPath
	 */
	public function setWkHtmlToPdtBinaryPath(string $wkHtmlToPdtBinaryPath): void
	{
		$this->wkHtmlToPdtBinaryPath = $wkHtmlToPdtBinaryPath;
	}

	/**
	 * @param string $pdfToImgConverterBinaryPath
	 */
	public function setPdfToImgConverterBinaryPath(string $pdfToImgConverterBinaryPath): void
	{
		$this->pdfToImgConverterBinaryPath = $pdfToImgConverterBinaryPath;
	}

	/**
	 * @param string $pdfToolkitBinaryPath
	 */
	public function setPdfToolkitBinaryPath(string $pdfToolkitBinaryPath): void
	{
		$this->pdfToolkitBinaryPath = $pdfToolkitBinaryPath;
	}

}