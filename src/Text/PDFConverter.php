<?php

namespace Osimatic\Text;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class PDFConverter
{
	public function __construct(
		private LoggerInterface $logger = new NullLogger(),
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
	 * @param string $imageFilePath
	 * @param string $pdfFilePath
	 * @return bool
	 */
	public function convertImageToPdf(string $imageFilePath, string $pdfFilePath): bool
	{
		$commandLine = escapeshellarg($this->imagickConverterBinaryPath) . ' ' . escapeshellarg($imageFilePath) . ' ' . escapeshellarg($pdfFilePath);

		// Envoi de la commande
		$this->logger->info('Ligne de commande exécutée : '.$commandLine);
		$returnCode = 0;
		system($commandLine, $returnCode);

		if ($returnCode !== 0) {
			$this->logger->error('La conversion a échoué avec le code de retour : ' . $returnCode);
			return false;
		}

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

		$commandLine = escapeshellarg($this->imagickConverterBinaryPath) . ' -quality 100 -density 150 ' . escapeshellarg($pdfPath) . ' ' . escapeshellarg($imagePath);

		// Envoi de la ligne de commande
		$returnCode = 0;
		system($commandLine, $returnCode);

		if ($returnCode !== 0) {
			$this->logger->error('La conversion a échoué avec le code de retour : ' . $returnCode);
			return false;
		}

		return true;
	}

}