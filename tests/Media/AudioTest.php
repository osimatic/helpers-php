<?php

declare(strict_types=1);

namespace Tests\Media;

use Osimatic\Media\Audio;
use PHPUnit\Framework\TestCase;

final class AudioTest extends TestCase
{
	/* ===================== Constants ===================== */

	public function testMp3Constants(): void
	{
		$this->assertSame('mp3', Audio::MP3_FORMAT);
		$this->assertSame('.mp3', Audio::MP3_EXTENSION);
		$this->assertIsArray(Audio::MP3_EXTENSIONS);
		$this->assertContains('.mp3', Audio::MP3_EXTENSIONS);
		$this->assertIsArray(Audio::MP3_MIME_TYPES);
		$this->assertContains('audio/mpeg', Audio::MP3_MIME_TYPES);
	}

	public function testWavConstants(): void
	{
		$this->assertSame('wav', Audio::WAV_FORMAT);
		$this->assertSame('.wav', Audio::WAV_EXTENSION);
		$this->assertIsArray(Audio::WAV_MIME_TYPES);
		$this->assertContains('audio/x-wav', Audio::WAV_MIME_TYPES);
	}

	public function testOggConstants(): void
	{
		$this->assertSame('ogg', Audio::OGG_FORMAT);
		$this->assertSame('.ogg', Audio::OGG_EXTENSION);
		$this->assertIsArray(Audio::OGG_EXTENSIONS);
		$this->assertContains('.ogg', Audio::OGG_EXTENSIONS);
		$this->assertIsArray(Audio::OGG_MIME_TYPES);
		$this->assertContains('audio/ogg', Audio::OGG_MIME_TYPES);
	}

	public function testAacConstants(): void
	{
		$this->assertSame('aac', Audio::AAC_FORMAT);
		$this->assertSame('.aac', Audio::AAC_EXTENSION);
		$this->assertIsArray(Audio::AAC_MIME_TYPES);
		$this->assertContains('audio/x-aac', Audio::AAC_MIME_TYPES);
	}

	public function testAiffConstants(): void
	{
		$this->assertSame('aiff', Audio::AIFF_FORMAT);
		$this->assertSame('.aiff', Audio::AIFF_EXTENSION);
		$this->assertIsArray(Audio::AIFF_EXTENSIONS);
		$this->assertContains('.aiff', Audio::AIFF_EXTENSIONS);
		$this->assertIsArray(Audio::AIFF_MIME_TYPES);
		$this->assertContains('audio/x-aiff', Audio::AIFF_MIME_TYPES);
	}

	public function testWmaConstants(): void
	{
		$this->assertSame('wma', Audio::WMA_FORMAT);
		$this->assertSame('.wma', Audio::WMA_EXTENSION);
		$this->assertIsArray(Audio::WMA_MIME_TYPES);
		$this->assertContains('audio/x-ms-wma', Audio::WMA_MIME_TYPES);
	}

	public function testWebmConstants(): void
	{
		$this->assertSame('webm', Audio::WEBM_FORMAT);
		$this->assertSame('.weba', Audio::WEBM_EXTENSION);
		$this->assertIsArray(Audio::WEBM_MIME_TYPES);
		$this->assertContains('audio/webm', Audio::WEBM_MIME_TYPES);
	}

	/* ===================== getExtensionsAndMimeTypes() ===================== */

	public function testGetExtensionsAndMimeTypes(): void
	{
		$result = Audio::getExtensionsAndMimeTypes();
		$this->assertIsArray($result);
		$this->assertArrayHasKey('mp3', $result);
		$this->assertArrayHasKey('wav', $result);
		$this->assertArrayHasKey('ogg', $result);
		$this->assertArrayHasKey('aac', $result);
		$this->assertArrayHasKey('aiff', $result);
		$this->assertArrayHasKey('wma', $result);
		$this->assertArrayHasKey('weba', $result);
	}

	public function testGetExtensionsAndMimeTypesMp3Structure(): void
	{
		$result = Audio::getExtensionsAndMimeTypes();
		$this->assertCount(2, $result['mp3']);
		$this->assertIsArray($result['mp3'][0]); // Extensions
		$this->assertIsArray($result['mp3'][1]); // Mime types
	}

	/* ===================== getMimeTypeFromExtension() ===================== */

	public function testGetMimeTypeFromExtensionWithMp3(): void
	{
		$this->assertSame('audio/mpeg', Audio::getMimeTypeFromExtension('.mp3'));
		$this->assertSame('audio/mpeg', Audio::getMimeTypeFromExtension('.mpga'));
	}

	public function testGetMimeTypeFromExtensionWithWav(): void
	{
		$this->assertSame('audio/x-wav', Audio::getMimeTypeFromExtension('.wav'));
	}

	public function testGetMimeTypeFromExtensionWithOgg(): void
	{
		$this->assertSame('audio/ogg', Audio::getMimeTypeFromExtension('.ogg'));
	}

	public function testGetMimeTypeFromExtensionWithAac(): void
	{
		$this->assertSame('audio/x-aac', Audio::getMimeTypeFromExtension('.aac'));
	}

	public function testGetMimeTypeFromExtensionWithInvalidExtension(): void
	{
		$this->assertNull(Audio::getMimeTypeFromExtension('.invalid'));
		$this->assertNull(Audio::getMimeTypeFromExtension('.txt'));
	}

	/* ===================== getExtensionFromMimeType() ===================== */

	public function testGetExtensionFromMimeTypeWithMp3(): void
	{
		$this->assertSame('mp3', Audio::getExtensionFromMimeType('audio/mpeg'));
	}

	public function testGetExtensionFromMimeTypeWithWav(): void
	{
		$this->assertSame('wav', Audio::getExtensionFromMimeType('audio/x-wav'));
	}

	public function testGetExtensionFromMimeTypeWithOgg(): void
	{
		$this->assertSame('ogg', Audio::getExtensionFromMimeType('audio/ogg'));
	}

	public function testGetExtensionFromMimeTypeWithAac(): void
	{
		$extension = Audio::getExtensionFromMimeType('audio/x-aac');
		$this->assertSame('aac', $extension);
	}

	public function testGetExtensionFromMimeTypeWithAlternativeAac(): void
	{
		$extension = Audio::getExtensionFromMimeType('audio/aac');
		$this->assertSame('aac', $extension);
	}

	public function testGetExtensionFromMimeTypeWithInvalidMimeType(): void
	{
		$this->assertNull(Audio::getExtensionFromMimeType('invalid/mime'));
		$this->assertNull(Audio::getExtensionFromMimeType('text/plain'));
	}

	/* ===================== getInfos() ===================== */

	public function testGetInfosWithNonExistentFile(): void
	{
		$result = Audio::getInfos('/non/existent/file.mp3');
		$this->assertNull($result);
	}

	/* ===================== getFormat() ===================== */

	public function testGetFormatWithNonExistentFile(): void
	{
		$result = Audio::getFormat('/non/existent/file.mp3');
		$this->assertNull($result);
	}

	/* ===================== getDuration() ===================== */

	public function testGetDurationWithNonExistentFile(): void
	{
		$result = Audio::getDuration('/non/existent/file.mp3');
		$this->assertSame(0.0, $result);
	}

	/* ===================== checkIsmn() ===================== */

	public function testCheckIsmnAlwaysReturnsTrue(): void
	{
		// TODO: This method is not implemented yet
		$this->assertTrue(Audio::checkIsmn('M-2306-7118-7'));
		$this->assertTrue(Audio::checkIsmn('invalid'));
	}
}