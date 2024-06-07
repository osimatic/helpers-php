<?php

namespace Osimatic\Text;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class PDFGenerator
{
	public function __construct(
		private LoggerInterface $logger = new NullLogger(),
		private ?string $wkHtmlToPdtBinaryPath = null,
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
	 * @param string $wkHtmlToPdtBinaryPath
	 * @return self
	 */
	public function setWkHtmlToPdtBinaryPath(string $wkHtmlToPdtBinaryPath): self
	{
		$this->wkHtmlToPdtBinaryPath = $wkHtmlToPdtBinaryPath;

		return $this;
	}

	/**
	 * @param string $filePath
	 * @param string $bodyHtml
	 * @param string|null $headerHtml
	 * @param string|null $footerHtml
	 * @param array $options
	 * @return bool
	 */
	public function generateFile(string $filePath, string $bodyHtml, ?string $headerHtml=null, ?string $footerHtml=null, array $options=[]): bool
	{
		\Osimatic\FileSystem\FileSystem::initializeFile($filePath);

		$snappy = new \Knp\Snappy\Pdf();
		$snappy->setBinary($this->wkHtmlToPdtBinaryPath);
		$snappy->setLogger($this->logger);
		if (!empty($headerHtml)) {
			$snappy->setOption('header-html', $headerHtml);
		}
		if (!empty($footerHtml)) {
			$snappy->setOption('footer-html', $footerHtml);
		}
		$snappy->setOption('enable-local-file-access', true);

		try {
			$snappy->generateFromHtml($bodyHtml, $filePath, $options);
		}
		catch (\Exception $e) {
			$this->logger->error('Exception lors de la génération du fichier PDF : '.$e->getMessage());
			return false;
		}

		return true;
	}

}