<?php

namespace Osimatic\Media;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class AudioConverter
 * Provides audio conversion utilities using SoX and FFmpeg command-line tools.
 * Supports converting between WAV, MP3, and WebM formats with various encoding options.
 */
class AudioConverter
{
	/**
	 * @param string|null $soxBinaryPath Path to the SoX binary executable
	 * @param string|null $ffmpegBinaryPath Path to the FFmpeg binary executable
	 * @param LoggerInterface $logger The PSR-3 logger instance for error and debugging (default: NullLogger)
	 */
	public function __construct(
		private ?string $soxBinaryPath = null,
		private ?string $ffmpegBinaryPath = null,
		private LoggerInterface $logger=new NullLogger(),
	) {}

	/**
	 * Set the path to the SoX binary executable.
	 * @param string $soxBinaryPath Path to the SoX binary
	 * @return self Returns the current instance for method chaining
	 */
	public function setSoxBinaryPath(string $soxBinaryPath): self
	{
		$this->soxBinaryPath = $soxBinaryPath;

		return $this;
	}

	/**
	 * Get the path to the SoX binary executable.
	 * @param bool $trim Whether to remove surrounding quotes from the path (default: true)
	 * @return string Path to the SoX binary
	 */
	public function getSoxBinaryPath(bool $trim = true): string
	{
		$path = $this->soxBinaryPath ?? 'sox';
		return $trim ? trim($path, '\'"') : $path;
	}

	/**
	 * Set the path to the FFmpeg binary executable.
	 * @param string $ffmpegBinaryPath Path to the FFmpeg binary
	 * @return self Returns the current instance for method chaining
	 */
	public function setFfmpegBinaryPath(string $ffmpegBinaryPath): self
	{
		$this->ffmpegBinaryPath = $ffmpegBinaryPath;

		return $this;
	}

	/**
	 * Get the path to the FFmpeg binary executable.
	 * @param bool $trim Whether to remove surrounding quotes from the path (default: true)
	 * @return string Path to the FFmpeg binary
	 */
	public function getFfmpegBinaryPath(bool $trim = true): string
	{
		$path = $this->ffmpegBinaryPath ?? 'ffmpeg';
		return $trim ? trim($path, '\'"') : $path;
	}

	/**
	 * Sets the logger for error and debugging information.
	 * @param LoggerInterface $logger The PSR-3 logger instance
	 * @return self Returns this instance for method chaining
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * Convert an audio file to WAV format with CCITT A-Law encoding.
	 * Converts MP3 or WAV files to WAV format with A-Law encoding, mono channel, and 8000 Hz sample rate using SoX.
	 * If destination path is not provided, adds "_converted" suffix to source filename.
	 * @param string $srcAudioFilePath Path to the source audio file (MP3 or WAV format)
	 * @param string|null $destAudioFilePath Path to the destination WAV file (optional, will be auto-generated if not provided)
	 * @return bool True if conversion succeeded, false otherwise
	 */
	public function convertToWavCcittALaw(string $srcAudioFilePath, ?string $destAudioFilePath=null): bool
	{
		// Check that the file is a WAV or MP3 file
		$fileFormat = Audio::getFormat($srcAudioFilePath);
		if (!in_array($fileFormat, [Audio::MP3_FORMAT, Audio::WAV_FORMAT], true)) {
			$this->logger->error('Audio file is not in MP3 or WAV format');
			return false;
		}

		// Check if destination audio file is specified. If not specified, use the source audio file name (adding the wav extension if not already present)
		if (empty($destAudioFilePath)) {
			$destAudioFilePath = substr($srcAudioFilePath, 0, strrpos($srcAudioFilePath, '.')).'_converted'.substr($srcAudioFilePath, strrpos($srcAudioFilePath, '.'));
		}
		if ($fileFormat !== Audio::WAV_FORMAT) {
			$destAudioFilePath = \Osimatic\FileSystem\File::replaceExtension($destAudioFilePath, 'wav');
		}

		return (new \Osimatic\System\Command($this->logger))->run([
			$this->getSoxBinaryPath(),
			$fileFormat === Audio::MP3_FORMAT ? '-t' : null,
			$fileFormat === Audio::MP3_FORMAT ? 'mp3' : null,
			$srcAudioFilePath,
			'-e', 'a-law',
			'-c', '1',
			'-r', '8000',
			$destAudioFilePath
		]);
	}

	/**
	 * Convert a WAV file to MP3 format.
	 * Converts WAV files to MP3 format with mono channel and 8000 Hz sample rate using SoX.
	 * If destination path is not provided, adds "_converted.mp3" suffix to source filename.
	 * Command example: sox -t wav -r 8000 -c 1 file.wav -t mp3 file.mp3
	 * @param string $srcAudioFilePath Path to the source WAV file
	 * @param string|null $destAudioFilePath Path to the destination MP3 file (optional, will be auto-generated if not provided)
	 * @return bool True if conversion succeeded, false otherwise
	 */
	public function convertWavToMp3(string $srcAudioFilePath, ?string $destAudioFilePath=null) : bool
	{
		// Check that the file is a WAV file
		if (Audio::getFormat($srcAudioFilePath) !== Audio::WAV_FORMAT) {
			$this->logger->error('Audio file is not in WAV format');
			return false;
		}

		// Check if destination audio file is specified. If not specified, use the source audio file name (with mp3 extension)
		if (empty($destAudioFilePath)) {
			$destAudioFilePath = substr($srcAudioFilePath, 0, strrpos($srcAudioFilePath, '.')).'_converted.mp3';
		}

		return (new \Osimatic\System\Command($this->logger))->run([
			$this->getSoxBinaryPath(),
			'-t', 'wav',
			'-r', '8000',
			'-c', '1',
			$srcAudioFilePath,
			'-t', 'mp3',
			$destAudioFilePath
		]);
	}

	/**
	 * Convert a WebM file to MP3 format.
	 * Converts WebM files to MP3 format with 160 kbps bitrate and 44100 Hz sample rate using FFmpeg.
	 * If destination path is not provided, adds "_converted.mp3" suffix to source filename.
	 * Deletes the destination file if it already exists before conversion.
	 * @param string $srcAudioFilePath Path to the source WebM file
	 * @param string|null $destAudioFilePath Path to the destination MP3 file (optional, will be auto-generated if not provided)
	 * @return bool True if conversion succeeded, false otherwise
	 */
	public function convertWebMToMp3(string $srcAudioFilePath, ?string $destAudioFilePath=null) : bool
	{
		// Check that the file is in WebM format
		if (Audio::getFormat($srcAudioFilePath) !== Audio::WEBM_FORMAT) {
			$this->logger->error('Audio file is not in WebM format');
			return false;
		}

		// Check if destination audio file is specified. If not specified, use the source audio file name (with mp3 extension)
		if (empty($destAudioFilePath)) {
			$destAudioFilePath = substr($srcAudioFilePath, 0, strrpos($srcAudioFilePath, '.')).'_converted.mp3';
		}

		if (file_exists($destAudioFilePath)) {
			unlink($destAudioFilePath);
		}

		return (new \Osimatic\System\Command($this->logger))->run([
			$this->getFfmpegBinaryPath(),
			'-i', $srcAudioFilePath,
			'-ab', '160k',
			'-ar', '44100',
			$destAudioFilePath
		]);
	}
}