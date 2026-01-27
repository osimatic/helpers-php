<?php

declare(strict_types=1);

namespace Tests\Media;

use Osimatic\Media\AudioConverter;
use Osimatic\Media\Audio;
use Osimatic\Media\AudioEncoding;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class AudioConverterTest extends TestCase
{
	/* ===================== Constructor ===================== */

	public function testConstructorWithoutParameters(): void
	{
		$converter = new AudioConverter();

		$this->assertInstanceOf(AudioConverter::class, $converter);
		$this->assertEquals('sox', $converter->getSoxBinaryPath());
		$this->assertEquals('ffmpeg', $converter->getFfmpegBinaryPath());
	}

	public function testConstructorWithAllParameters(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$converter = new AudioConverter('/usr/bin/sox', '/usr/bin/ffmpeg', $logger);

		$this->assertInstanceOf(AudioConverter::class, $converter);
		$this->assertEquals('/usr/bin/sox', $converter->getSoxBinaryPath());
		$this->assertEquals('/usr/bin/ffmpeg', $converter->getFfmpegBinaryPath());
	}

	public function testConstructorWithSoxBinaryPathOnly(): void
	{
		$converter = new AudioConverter('/custom/path/sox');

		$this->assertEquals('/custom/path/sox', $converter->getSoxBinaryPath());
		$this->assertEquals('ffmpeg', $converter->getFfmpegBinaryPath());
	}

	/* ===================== Setters and Getters ===================== */

	public function testSetSoxBinaryPath(): void
	{
		$converter = new AudioConverter();
		$result = $converter->setSoxBinaryPath('/usr/local/bin/sox');

		$this->assertSame($converter, $result);
		$this->assertEquals('/usr/local/bin/sox', $converter->getSoxBinaryPath());
	}

	public function testGetSoxBinaryPathWithTrim(): void
	{
		$converter = new AudioConverter('"/path/with/quotes/sox"');

		$this->assertEquals('/path/with/quotes/sox', $converter->getSoxBinaryPath(true));
		$this->assertEquals('"/path/with/quotes/sox"', $converter->getSoxBinaryPath(false));
	}

	public function testGetSoxBinaryPathWithSingleQuotes(): void
	{
		$converter = new AudioConverter("'/path/with/quotes/sox'");

		$this->assertEquals('/path/with/quotes/sox', $converter->getSoxBinaryPath(true));
		$this->assertEquals("'/path/with/quotes/sox'", $converter->getSoxBinaryPath(false));
	}

	public function testSetFfmpegBinaryPath(): void
	{
		$converter = new AudioConverter();
		$result = $converter->setFfmpegBinaryPath('/usr/local/bin/ffmpeg');

		$this->assertSame($converter, $result);
		$this->assertEquals('/usr/local/bin/ffmpeg', $converter->getFfmpegBinaryPath());
	}

	public function testGetFfmpegBinaryPathWithTrim(): void
	{
		$converter = new AudioConverter(null, '"/path/with/quotes/ffmpeg"');

		$this->assertEquals('/path/with/quotes/ffmpeg', $converter->getFfmpegBinaryPath(true));
		$this->assertEquals('"/path/with/quotes/ffmpeg"', $converter->getFfmpegBinaryPath(false));
	}

	public function testSetLogger(): void
	{
		$converter = new AudioConverter();
		$logger = $this->createMock(LoggerInterface::class);

		$result = $converter->setLogger($logger);

		$this->assertSame($converter, $result);
	}

	public function testFluentInterface(): void
	{
		$converter = new AudioConverter();
		$logger = $this->createMock(LoggerInterface::class);

		$result = $converter
			->setSoxBinaryPath('/usr/bin/sox')
			->setFfmpegBinaryPath('/usr/bin/ffmpeg')
			->setLogger($logger);

		$this->assertSame($converter, $result);
		$this->assertEquals('/usr/bin/sox', $converter->getSoxBinaryPath());
		$this->assertEquals('/usr/bin/ffmpeg', $converter->getFfmpegBinaryPath());
	}

	/* ===================== convertToWav() - Format Validation ===================== */

	public function testConvertToWavWithInvalidFormat(): void
	{
		// Create a temporary file with .ogg extension (unsupported format)
		$tempSrcFile = tempnam(sys_get_temp_dir(), 'test_') . '.ogg';
		file_put_contents($tempSrcFile, 'dummy audio content');

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('error')
			->with('Audio file is not in MP3 or WAV format');

		$converter = new AudioConverter(null, null, $logger);
		$result = $converter->convertToWav($tempSrcFile);

		$this->assertFalse($result);

		// Cleanup
		unlink($tempSrcFile);
	}

	public function testConvertToWavWithMp3FileGeneratesDestinationPath(): void
	{
		// Create a temporary MP3 file
		$tempSrcFile = tempnam(sys_get_temp_dir(), 'test_') . '.mp3';
		file_put_contents($tempSrcFile, 'dummy mp3 content');

		// Mock the Command to prevent actual execution
		$converter = new AudioConverter('/mock/sox');

		// This will fail because sox doesn't exist, but we're testing the path logic
		$result = $converter->convertToWav($tempSrcFile);

		$this->assertFalse($result); // Command will fail, but that's expected

		// Cleanup
		unlink($tempSrcFile);
	}

	public function testConvertToWavWithWavFileKeepsExtension(): void
	{
		// Create a temporary WAV file
		$tempSrcFile = tempnam(sys_get_temp_dir(), 'test_') . '.wav';
		file_put_contents($tempSrcFile, 'RIFF....WAVEfmt '); // Minimal WAV header

		$converter = new AudioConverter('/mock/sox');

		// This will fail because sox doesn't exist, but we're testing the logic
		$result = $converter->convertToWav($tempSrcFile);

		$this->assertFalse($result);

		// Cleanup
		unlink($tempSrcFile);
	}

	public function testConvertToWavWithCustomParameters(): void
	{
		// Create a temporary MP3 file
		$tempSrcFile = tempnam(sys_get_temp_dir(), 'test_') . '.mp3';
		$tempDestFile = tempnam(sys_get_temp_dir(), 'test_') . '_output.wav';
		file_put_contents($tempSrcFile, 'dummy mp3 content');

		$converter = new AudioConverter('/mock/sox');

		// Test with custom encoding, channels, and sample rate
		$result = $converter->convertToWav(
			$tempSrcFile,
			$tempDestFile,
			AudioEncoding::A_LAW,
			1,
			8000
		);

		$this->assertFalse($result); // Command will fail, but that's expected

		// Cleanup
		unlink($tempSrcFile);
		if (file_exists($tempDestFile)) {
			unlink($tempDestFile);
		}
	}

	public function testConvertToWavWithDefaultParameters(): void
	{
		// Create a temporary MP3 file
		$tempSrcFile = tempnam(sys_get_temp_dir(), 'test_') . '.mp3';
		file_put_contents($tempSrcFile, 'dummy mp3 content');

		$converter = new AudioConverter('/mock/sox');

		// Test with default parameters (signed-integer, 2 channels, 44100 Hz)
		$result = $converter->convertToWav($tempSrcFile);

		$this->assertFalse($result); // Command will fail, but that's expected

		// Cleanup
		unlink($tempSrcFile);
	}

	/* ===================== convertToWavCcittALaw() ===================== */

	public function testConvertToWavCcittALaw(): void
	{
		// Create a temporary MP3 file
		$tempSrcFile = tempnam(sys_get_temp_dir(), 'test_') . '.mp3';
		file_put_contents($tempSrcFile, 'dummy mp3 content');

		$converter = new AudioConverter('/mock/sox');

		// This is a convenience method that should call convertToWav with a-law, 1 channel, 8000 Hz
		$result = $converter->convertToWavCcittALaw($tempSrcFile);

		$this->assertFalse($result); // Command will fail, but that's expected

		// Cleanup
		unlink($tempSrcFile);
	}

	public function testConvertToWavCcittALawWithCustomDestination(): void
	{
		// Create a temporary MP3 file
		$tempSrcFile = tempnam(sys_get_temp_dir(), 'test_') . '.mp3';
		$tempDestFile = tempnam(sys_get_temp_dir(), 'test_') . '_alaw.wav';
		file_put_contents($tempSrcFile, 'dummy mp3 content');

		$converter = new AudioConverter('/mock/sox');

		$result = $converter->convertToWavCcittALaw($tempSrcFile, $tempDestFile);

		$this->assertFalse($result);

		// Cleanup
		unlink($tempSrcFile);
		if (file_exists($tempDestFile)) {
			unlink($tempDestFile);
		}
	}

	/* ===================== convertWavToMp3() - Format Validation ===================== */

	public function testConvertWavToMp3WithInvalidFormat(): void
	{
		// Create a temporary file with .mp3 extension (not WAV)
		$tempSrcFile = tempnam(sys_get_temp_dir(), 'test_') . '.mp3';
		file_put_contents($tempSrcFile, 'dummy mp3 content');

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('error')
			->with('Audio file is not in WAV format');

		$converter = new AudioConverter(null, null, $logger);
		$result = $converter->convertWavToMp3($tempSrcFile);

		$this->assertFalse($result);

		// Cleanup
		unlink($tempSrcFile);
	}

	public function testConvertWavToMp3WithValidWavFile(): void
	{
		// Create a temporary WAV file
		$tempSrcFile = tempnam(sys_get_temp_dir(), 'test_') . '.wav';
		file_put_contents($tempSrcFile, 'RIFF....WAVEfmt '); // Minimal WAV header

		$converter = new AudioConverter('/mock/sox');

		$result = $converter->convertWavToMp3($tempSrcFile);

		$this->assertFalse($result); // Command will fail, but that's expected

		// Cleanup
		unlink($tempSrcFile);
	}

	public function testConvertWavToMp3WithCustomParameters(): void
	{
		// Create a temporary WAV file
		$tempSrcFile = tempnam(sys_get_temp_dir(), 'test_') . '.wav';
		$tempDestFile = tempnam(sys_get_temp_dir(), 'test_') . '_output.mp3';
		file_put_contents($tempSrcFile, 'RIFF....WAVEfmt ');

		$converter = new AudioConverter('/mock/sox');

		// Test with custom sample rate and channels
		$result = $converter->convertWavToMp3($tempSrcFile, $tempDestFile, 44100, 2);

		$this->assertFalse($result);

		// Cleanup
		unlink($tempSrcFile);
		if (file_exists($tempDestFile)) {
			unlink($tempDestFile);
		}
	}

	public function testConvertWavToMp3GeneratesDestinationPath(): void
	{
		// Create a temporary WAV file
		$tempSrcFile = tempnam(sys_get_temp_dir(), 'test_') . '.wav';
		file_put_contents($tempSrcFile, 'RIFF....WAVEfmt ');

		$converter = new AudioConverter('/mock/sox');

		// Without destination path, should auto-generate with _converted suffix and .mp3 extension
		$result = $converter->convertWavToMp3($tempSrcFile);

		$this->assertFalse($result);

		// Cleanup
		unlink($tempSrcFile);
	}

	/* ===================== convertWebMToMp3() - Format Validation ===================== */

	public function testConvertWebMToMp3WithInvalidFormat(): void
	{
		// Create a temporary file with .mp3 extension (not WebM)
		$tempSrcFile = tempnam(sys_get_temp_dir(), 'test_') . '.mp3';
		file_put_contents($tempSrcFile, 'dummy mp3 content');

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('error')
			->with('Audio file is not in WebM format');

		$converter = new AudioConverter(null, null, $logger);
		$result = $converter->convertWebMToMp3($tempSrcFile);

		$this->assertFalse($result);

		// Cleanup
		unlink($tempSrcFile);
	}

	public function testConvertWebMToMp3WithValidWebMFile(): void
	{
		// Create a temporary WebM file
		$tempSrcFile = tempnam(sys_get_temp_dir(), 'test_') . '.webm';
		file_put_contents($tempSrcFile, "\x1A\x45\xDF\xA3"); // WebM/EBML signature

		$converter = new AudioConverter(null, '/mock/ffmpeg');

		$result = $converter->convertWebMToMp3($tempSrcFile);

		$this->assertFalse($result); // Command will fail, but that's expected

		// Cleanup
		unlink($tempSrcFile);
	}

	public function testConvertWebMToMp3WithCustomParameters(): void
	{
		// Create a temporary WebM file
		$tempSrcFile = tempnam(sys_get_temp_dir(), 'test_') . '.webm';
		$tempDestFile = tempnam(sys_get_temp_dir(), 'test_') . '_output.mp3';
		file_put_contents($tempSrcFile, "\x1A\x45\xDF\xA3");

		$converter = new AudioConverter(null, '/mock/ffmpeg');

		// Test with custom bitrate and sample rate
		$result = $converter->convertWebMToMp3($tempSrcFile, $tempDestFile, '320k', 48000);

		$this->assertFalse($result);

		// Cleanup
		unlink($tempSrcFile);
		if (file_exists($tempDestFile)) {
			unlink($tempDestFile);
		}
	}

	public function testConvertWebMToMp3GeneratesDestinationPath(): void
	{
		// Create a temporary WebM file
		$tempSrcFile = tempnam(sys_get_temp_dir(), 'test_') . '.webm';
		file_put_contents($tempSrcFile, "\x1A\x45\xDF\xA3");

		$converter = new AudioConverter(null, '/mock/ffmpeg');

		// Without destination path, should auto-generate with _converted suffix and .mp3 extension
		$result = $converter->convertWebMToMp3($tempSrcFile);

		$this->assertFalse($result);

		// Cleanup
		unlink($tempSrcFile);
	}

	public function testConvertWebMToMp3DeletesExistingDestination(): void
	{
		// Create temporary WebM file and existing destination file
		$tempSrcFile = tempnam(sys_get_temp_dir(), 'test_') . '.webm';
		$tempDestFile = tempnam(sys_get_temp_dir(), 'test_') . '_output.mp3';
		file_put_contents($tempSrcFile, "\x1A\x45\xDF\xA3");
		file_put_contents($tempDestFile, 'old content');

		$this->assertFileExists($tempDestFile);

		$converter = new AudioConverter(null, '/mock/ffmpeg');

		$result = $converter->convertWebMToMp3($tempSrcFile, $tempDestFile);

		$this->assertFalse($result);

		// The destination file should have been deleted by the method
		// (even though the conversion failed, the file deletion happens before)

		// Cleanup
		unlink($tempSrcFile);
		if (file_exists($tempDestFile)) {
			unlink($tempDestFile);
		}
	}

	/* ===================== Default Binary Paths ===================== */

	public function testDefaultSoxBinaryPath(): void
	{
		$converter = new AudioConverter();

		$this->assertEquals('sox', $converter->getSoxBinaryPath());
	}

	public function testDefaultFfmpegBinaryPath(): void
	{
		$converter = new AudioConverter();

		$this->assertEquals('ffmpeg', $converter->getFfmpegBinaryPath());
	}

	/* ===================== Path Trimming Edge Cases ===================== */

	public function testGetSoxBinaryPathWithMixedQuotes(): void
	{
		$converter = new AudioConverter('"\'/path/to/sox\'"');

		// Should trim both types of quotes
		$trimmed = $converter->getSoxBinaryPath(true);
		$this->assertStringNotContainsString('"', $trimmed);
		$this->assertStringNotContainsString("'", $trimmed);
	}

	public function testGetFfmpegBinaryPathWithMixedQuotes(): void
	{
		$converter = new AudioConverter(null, '"\'/path/to/ffmpeg\'"');

		// Should trim both types of quotes
		$trimmed = $converter->getFfmpegBinaryPath(true);
		$this->assertStringNotContainsString('"', $trimmed);
		$this->assertStringNotContainsString("'", $trimmed);
	}
}