<?php

namespace Osimatic\Helpers\Text;

use Osimatic\Helpers\FileSystem\OutputFile;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Response;

class PDF
{
	public const FILE_EXTENSION = '.pdf';
	public const MIME_TYPES = [
		'application/pdf',
		'application/x-pdf',
		'application/vnd.cups-pdf',
		'application/vnd.sealedmedia.softseal.pdf',
	];

	private ?string $header = null;
	private ?string $footer = null;
	private ?string $body = null;

	public function __construct(
		private LoggerInterface $logger = new NullLogger(),
		private ?string $wkHtmlToPdtBinaryPath = null,
		private ?string $pdfToolkitBinaryPath = null,
		private ?string $imagickConverterBinaryPath = null,
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

	// ========== Vérification ==========

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\Helpers\FileSystem\File::check($filePath, $clientOriginalName, [self::FILE_EXTENSION], self::MIME_TYPES);
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
		\Osimatic\Helpers\FileSystem\File::output($filePath, $fileName, 'application/pdf');
	}

	/**
	 * Envoi au navigateur du client un fichier PDF.
	 * Aucun affichage ne doit être effectué avant ou après l'appel à cette fonction.
	 * @param OutputFile $file
	 */
	public static function outputFile(OutputFile $file): void
	{
		\Osimatic\Helpers\FileSystem\File::outputFile($file, 'application/pdf');
	}

	/**
	 * Envoi au navigateur du client un fichier PDF.
	 * Aucun affichage ne doit être effectué avant ou après l'appel à cette fonction.
	 * @param string $filePath
	 * @param string|null $fileName
	 */
	public static function download(string $filePath, ?string $fileName=null): void
	{
		\Osimatic\Helpers\FileSystem\File::download($filePath, $fileName);
	}

	/**
	 * Envoi au navigateur du client un fichier PDF.
	 * Aucun affichage ne doit être effectué avant ou après l'appel à cette fonction.
	 * @param OutputFile $file
	 */
	public static function downloadFile(OutputFile $file): void
	{
		\Osimatic\Helpers\FileSystem\File::downloadFile($file);
	}

	/**
	 * @param string $filePath
	 * @param string|null $fileName
	 * @return Response
	 */
	public static function getHttpResponse(string $filePath, ?string $fileName=null): Response
	{
		return \Osimatic\Helpers\FileSystem\File::getHttpResponse($filePath, $fileName, false, 'application/pdf');
	}

	// ========== Génération ==========

	/**
	 * @param string $html
	 * @return self
	 */
	public function setHeader(string $html): self
	{
		$this->header = $html;

		return $this;
	}

	/**
	 * @param string $html
	 * @return self
	 */
	public function setFooter(string $html): self
	{
		$this->footer = $html;

		return $this;
	}

	/**
	 * @param string $htmlHeader
	 * @param string $htmlFooter
	 * @return self
	 */
	public function setHeaderAndFooter(string $htmlHeader, string $htmlFooter): self
	{
		$this->header = $htmlHeader;
		$this->footer = $htmlFooter;

		return $this;
	}

	/**
	 * @param string $html
	 * @return self
	 */
	public function setBody(string $html): self
	{
		$this->body = $html;

		return $this;
	}

	/**
	 * @param string $filePath
	 * @param array $options
	 * @return bool
	 */
	public function generateFile(string $filePath, array $options=[]): bool
	{
		\Osimatic\Helpers\FileSystem\FileSystem::initializeFile($filePath);

		$snappy = new \Knp\Snappy\Pdf();
		$snappy->setBinary($this->wkHtmlToPdtBinaryPath);
		$snappy->setLogger($this->logger);
		if (!empty($this->header)) {
			$snappy->setOption('header-html', $this->header);
		}
		if (!empty($this->footer)) {
			$snappy->setOption('footer-html', $this->footer);
		}
		$snappy->setOption('enable-local-file-access', true);

		try {
			$snappy->generateFromHtml($this->body, $filePath, $options);
		}
		catch (\Exception $e) {
			$this->logger->error('Exception lors de la génération du fichier PDF : '.$e->getMessage());
			return false;
		}

		return true;
	}

	/**
	 * @param string $imageFilePath
	 * @param string $pdfFilePath
	 * @return bool
	 */
	public function convertImageToPdf(string $imageFilePath, string $pdfFilePath): bool
	{
		$commandLine = $this->imagickConverterBinaryPath . ' "'.$imageFilePath.'" "'.$pdfFilePath.'"';

		// Envoi de la commande
		$this->logger->info('Ligne de commande exécutée : '.$commandLine);
		$lastLine = system($commandLine);

		/*try {
			$pdf = new \Imagick([$imageFilePath]);
			$pdf->setImageFormat('pdf');
			$pdf->writeImages($pdfFilePath, true);
		} catch (\ImagickException $e) {
			$this->logger->error($e->getMessage());
			return false;
		}*/

		return true;
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
		$commandLine = $this->imagickConverterBinaryPath . ' ' . $args;

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

		\Osimatic\Helpers\FileSystem\FileSystem::initializeFile($newPdfPath);

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
	 * @return self
	 */
	public function setWkHtmlToPdtBinaryPath(string $wkHtmlToPdtBinaryPath): self
	{
		$this->wkHtmlToPdtBinaryPath = $wkHtmlToPdtBinaryPath;

		return $this;
	}

	/**
	 * @param string $imagickConverterBinaryPath
	 * @return self
	 */
	public function setImagickConverterBinaryPath(string $imagickConverterBinaryPath): self
	{
		$this->imagickConverterBinaryPath = $imagickConverterBinaryPath;

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

}