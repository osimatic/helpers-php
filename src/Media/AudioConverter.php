<?php

namespace Osimatic\Media;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class AudioConverter
{
	/**
	 * @param string|null $soxBinaryPath
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		private ?string $soxBinaryPath = null,
		private LoggerInterface $logger=new NullLogger(),
	) {}

	/**
	 * @param string $soxBinaryPath
	 * @return self
	 */
	public function setSoxBinaryPath(string $soxBinaryPath): self
	{
		$this->soxBinaryPath = $soxBinaryPath;

		return $this;
	}

	/**
	 * @param LoggerInterface $logger
	 * @return self
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * @param string $srcAudioFilePath
	 * @param string|null $destAudioFilePath
	 * @return bool
	 */
	public function convertToWavCcittALaw(string $srcAudioFilePath, ?string $destAudioFilePath=null): bool
	{
		// Vérification que le fichier soit un fichier wav ou mp3
		$fileFormat = Audio::getFormat($srcAudioFilePath);
		if (!in_array($fileFormat, [Audio::MP3_FORMAT, Audio::WAV_FORMAT], true)) {
			$this->logger->error('Message audio pas au format mp3 ou WAV');
			return false;
		}

		// Vérification si fichier audio destination renseigné. Si non renseigné, on utilise le nom de fichier audio source (en mettant l'extension wav si ce n'est pas le cas)
		if (empty($destAudioFilePath)) {
			$destAudioFilePath = substr($srcAudioFilePath, 0, strrpos($srcAudioFilePath, '.')).'_converted'.substr($srcAudioFilePath, strrpos($srcAudioFilePath, '.'));
		}
		if ($fileFormat !== Audio::WAV_FORMAT) {
			$destAudioFilePath = substr($destAudioFilePath, 0, strrpos($destAudioFilePath, '.')+1).'wav';
		}

		$commandLine = escapeshellarg($this->soxBinaryPath) . ($fileFormat === Audio::MP3_FORMAT ? ' -t mp3' : '') . ' ' . escapeshellarg($srcAudioFilePath) . ' -e a-law -c 1 -r 8000 ' . escapeshellarg($destAudioFilePath);

		// Envoi de la commande
		$this->logger->info('Ligne de commande exécutée : '.$commandLine);
		$returnCode = 0;
		system($commandLine, $returnCode);

		if ($returnCode !== 0) {
			$this->logger->error('La conversion a échoué avec le code de retour : ' . $returnCode);
			return false;
		}

		return true;
	}

	/**
	 * Exemple de commande : sox -t wav -r 8000 -c 1 file.wav -t mp3 file.mp3
	 * @param string $srcAudioFilePath
	 * @param string|null $destAudioFilePath
	 * @return bool
	 */
	public function convertWavToMp3(string $srcAudioFilePath, ?string $destAudioFilePath=null) : bool
	{
		// Vérification que le fichier soit un fichier wav
		if (Audio::getFormat($srcAudioFilePath) !== Audio::WAV_FORMAT) {
			$this->logger->error('Message audio pas au format WAV');
			return false;
		}

		// Vérification si fichier audio destination renseigné. Si non renseigné, on utilise le nom de fichier audio source (en mettant l'extension mp3)
		if (empty($destAudioFilePath)) {
			$destAudioFilePath = substr($srcAudioFilePath, 0, strrpos($srcAudioFilePath, '.')).'_converted.mp3';
		}

		$commandLine = escapeshellarg($this->soxBinaryPath) . ' -t wav -r 8000 -c 1 ' . escapeshellarg($srcAudioFilePath) . ' -t mp3 ' . escapeshellarg($destAudioFilePath);

		// Envoi de la commande
		$this->logger->info('Ligne de commande exécutée : '.$commandLine);
		$returnCode = 0;
		system($commandLine, $returnCode);

		if ($returnCode !== 0) {
			$this->logger->error('La conversion a échoué avec le code de retour : ' . $returnCode);
			return false;
		}

		return true;
	}

	/**
	 * @param string $srcAudioFilePath
	 * @param string|null $destAudioFilePath
	 * @return bool
	 */
	public function convertWebMToMp3(string $srcAudioFilePath, ?string $destAudioFilePath=null) : bool
	{
		// Vérification que le fichier soit au format WebM
		if (Audio::getFormat($srcAudioFilePath) !== Audio::WEBM_FORMAT) {
			$this->logger->error('Message audio pas au format WebM');
			return false;
		}

		// Vérification si fichier audio destination renseigné. Si non renseigné, on utilise le nom de fichier audio source (en mettant l'extension mp3)
		if (empty($destAudioFilePath)) {
			$destAudioFilePath = substr($srcAudioFilePath, 0, strrpos($srcAudioFilePath, '.')).'_converted.mp3';
		}

		if (file_exists($destAudioFilePath)) {
			unlink($destAudioFilePath);
		}

		$commandLine = 'ffmpeg -i ' . escapeshellarg($srcAudioFilePath) . ' -ab 160k -ar 44100 ' . escapeshellarg($destAudioFilePath);

		// Envoi de la commande
		$this->logger->info('Ligne de commande exécutée : '.$commandLine);
		$returnCode = 0;
		system($commandLine, $returnCode);

		if ($returnCode !== 0) {
			$this->logger->error('La conversion a échoué avec le code de retour : ' . $returnCode);
			return false;
		}

		return true;
	}
}