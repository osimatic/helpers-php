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

	public function testGetMimeTypeFromExtensionWithAiff(): void
	{
		$this->assertSame('audio/x-aiff', Audio::getMimeTypeFromExtension('.aiff'));
		$this->assertSame('audio/x-aiff', Audio::getMimeTypeFromExtension('.aif'));
		$this->assertSame('audio/x-aiff', Audio::getMimeTypeFromExtension('.aifc'));
	}

	public function testGetMimeTypeFromExtensionWithWma(): void
	{
		$this->assertSame('audio/x-ms-wma', Audio::getMimeTypeFromExtension('.wma'));
	}

	public function testGetMimeTypeFromExtensionWithWebm(): void
	{
		$this->assertSame('audio/webm', Audio::getMimeTypeFromExtension('.weba'));
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

	public function testGetExtensionFromMimeTypeWithAiff(): void
	{
		$extension = Audio::getExtensionFromMimeType('audio/x-aiff');
		// Can be either 'aif' or 'aiff' depending on array order
		$this->assertContains($extension, ['aif', 'aiff']);
	}

	public function testGetExtensionFromMimeTypeWithWma(): void
	{
		$extension = Audio::getExtensionFromMimeType('audio/x-ms-wma');
		$this->assertSame('wma', $extension);
	}

	public function testGetExtensionFromMimeTypeWithWebm(): void
	{
		$extension = Audio::getExtensionFromMimeType('audio/webm');
		$this->assertSame('weba', $extension);
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

	/* ===================== normalizeIsmn() ===================== */

	public function testNormalizeIsmnRemovesHyphens(): void
	{
		$this->assertSame('9790230671187', Audio::normalizeIsmn('979-0-2306-7118-7'));
	}

	public function testNormalizeIsmnRemovesSpaces(): void
	{
		$this->assertSame('9790230671187', Audio::normalizeIsmn('979 0 2306 7118 7'));
	}

	public function testNormalizeIsmnTrimsWhitespace(): void
	{
		$this->assertSame('9790230671187', Audio::normalizeIsmn('  979-0-2306-7118-7  '));
	}

	/* ===================== checkIsmn() ===================== */

	public function testCheckIsmnWithValidIsmn(): void
	{
		$this->assertTrue(Audio::checkIsmn('979-0-2306-7118-7'));
		$this->assertTrue(Audio::checkIsmn('9790230671187'));
		$this->assertTrue(Audio::checkIsmn('979-0-001-01234-8'));
	}

	public function testCheckIsmnWithInvalidIsmn(): void
	{
		$this->assertFalse(Audio::checkIsmn('invalid'));
		$this->assertFalse(Audio::checkIsmn('123'));
		$this->assertFalse(Audio::checkIsmn('9790000000000'));
	}

	public function testCheckIsmnWithEmptyString(): void
	{
		$this->assertFalse(Audio::checkIsmn(''));
	}

	public function testCheckIsmnWithWrongPrefix(): void
	{
		// ISMN must start with 9790
		$this->assertFalse(Audio::checkIsmn('978-0-2306-7118-7'));
		$this->assertFalse(Audio::checkIsmn('9780230671187'));
	}

	public function testCheckIsmnWithWrongLength(): void
	{
		$this->assertFalse(Audio::checkIsmn('97902306711'));
		$this->assertFalse(Audio::checkIsmn('97902306711870'));
	}

	/* ===================== getBitrate() ===================== */

	public function testGetBitrateWithNonExistentFile(): void
	{
		$result = Audio::getBitrate('/non/existent/file.mp3');
		$this->assertNull($result);
	}

	/* ===================== getSampleRate() ===================== */

	public function testGetSampleRateWithNonExistentFile(): void
	{
		$result = Audio::getSampleRate('/non/existent/file.mp3');
		$this->assertNull($result);
	}

	/* ===================== getChannels() ===================== */

	public function testGetChannelsWithNonExistentFile(): void
	{
		$result = Audio::getChannels('/non/existent/file.mp3');
		$this->assertNull($result);
	}

	/* ===================== getArtist() ===================== */

	public function testGetArtistWithNonExistentFile(): void
	{
		$result = Audio::getArtist('/non/existent/file.mp3');
		$this->assertNull($result);
	}

	/* ===================== getTitle() ===================== */

	public function testGetTitleWithNonExistentFile(): void
	{
		$result = Audio::getTitle('/non/existent/file.mp3');
		$this->assertNull($result);
	}

	/* ===================== getAlbum() ===================== */

	public function testGetAlbumWithNonExistentFile(): void
	{
		$result = Audio::getAlbum('/non/existent/file.mp3');
		$this->assertNull($result);
	}

	/* ===================== getYear() ===================== */

	public function testGetYearWithNonExistentFile(): void
	{
		$result = Audio::getYear('/non/existent/file.mp3');
		$this->assertNull($result);
	}

	/* ===================== getTags() ===================== */

	public function testGetTagsWithNonExistentFile(): void
	{
		$result = Audio::getTags('/non/existent/file.mp3');
		$this->assertIsArray($result);
		$this->assertEmpty($result);
	}

	public function testGetTagsStructure(): void
	{
		// Test that getTags returns the expected array structure even with non-existent file
		$result = Audio::getTags('/non/existent/file.mp3');
		$this->assertIsArray($result);
		// Structure should be empty array when file doesn't exist
	}

	/* ===================== checkFile() ===================== */

	public function testCheckFileWithNonExistentFile(): void
	{
		$result = Audio::checkFile('/non/existent/file.mp3', 'test.mp3');
		$this->assertFalse($result);
	}

	/* ===================== checkMp3File() ===================== */

	public function testCheckMp3FileWithNonExistentFile(): void
	{
		$result = Audio::checkMp3File('/non/existent/file.mp3', 'test.mp3');
		$this->assertFalse($result);
	}

	/* ===================== checkWavFile() ===================== */

	public function testCheckWavFileWithNonExistentFile(): void
	{
		$result = Audio::checkWavFile('/non/existent/file.wav', 'test.wav');
		$this->assertFalse($result);
	}
}