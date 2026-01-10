<?php

declare(strict_types=1);

namespace Tests\Media;

use Osimatic\Media\Video;
use PHPUnit\Framework\TestCase;

final class VideoTest extends TestCase
{
	/* ===================== Constants ===================== */

	public function testMp4Constants(): void
	{
		$this->assertSame('.mp4', Video::MP4_EXTENSION);
		$this->assertIsArray(Video::MP4_EXTENSIONS);
		$this->assertContains('.mp4', Video::MP4_EXTENSIONS);
		$this->assertContains('.mp4v', Video::MP4_EXTENSIONS);
		$this->assertIsArray(Video::MP4_MIME_TYPES);
		$this->assertContains('video/mp4', Video::MP4_MIME_TYPES);
	}

	public function testMpgConstants(): void
	{
		$this->assertSame('.mpg', Video::MPG_EXTENSION);
		$this->assertIsArray(Video::MPG_EXTENSIONS);
		$this->assertContains('.mpg', Video::MPG_EXTENSIONS);
		$this->assertContains('.mpeg', Video::MPG_EXTENSIONS);
		$this->assertIsArray(Video::MPG_MIME_TYPES);
		$this->assertContains('video/mpeg', Video::MPG_MIME_TYPES);
	}

	public function testAviConstants(): void
	{
		$this->assertSame('.avi', Video::AVI_EXTENSION);
		$this->assertIsArray(Video::AVI_MIME_TYPES);
		$this->assertContains('video/x-msvideo', Video::AVI_MIME_TYPES);
	}

	public function testWmvConstants(): void
	{
		$this->assertSame('.wmv', Video::WMV_EXTENSION);
		$this->assertIsArray(Video::WMV_MIME_TYPES);
		$this->assertContains('video/x-ms-wmv', Video::WMV_MIME_TYPES);
	}

	public function testFlvConstants(): void
	{
		$this->assertSame('.flv', Video::FLV_EXTENSION);
		$this->assertIsArray(Video::FLV_MIME_TYPES);
		$this->assertContains('video/x-flv', Video::FLV_MIME_TYPES);
	}

	public function testOggConstants(): void
	{
		$this->assertSame('.ogv', Video::OGG_EXTENSION);
		$this->assertIsArray(Video::OGG_MIME_TYPES);
		$this->assertContains('video/ogg', Video::OGG_MIME_TYPES);
	}

	public function testWebmConstants(): void
	{
		$this->assertSame('.webm', Video::WEBM_EXTENSION);
		$this->assertIsArray(Video::WEBM_MIME_TYPES);
		$this->assertContains('video/webm', Video::WEBM_MIME_TYPES);
	}

	public function test3GppConstants(): void
	{
		$this->assertSame('.3gp', Video::_3GPP_EXTENSION);
		$this->assertIsArray(Video::_3GPP_MIME_TYPES);
		$this->assertContains('video/3gpp', Video::_3GPP_MIME_TYPES);
	}

	public function testQuicktimeConstants(): void
	{
		$this->assertSame('.mov', Video::QUICKTIME_EXTENSION);
		$this->assertIsArray(Video::QUICKTIME_EXTENSIONS);
		$this->assertContains('.mov', Video::QUICKTIME_EXTENSIONS);
		$this->assertContains('.qt', Video::QUICKTIME_EXTENSIONS);
		$this->assertIsArray(Video::QUICKTIME_MIME_TYPES);
		$this->assertContains('video/quicktime', Video::QUICKTIME_MIME_TYPES);
	}

	/* ===================== getExtensionsAndMimeTypes() ===================== */

	public function testGetExtensionsAndMimeTypes(): void
	{
		$result = Video::getExtensionsAndMimeTypes();
		$this->assertIsArray($result);
		$this->assertArrayHasKey('mp4', $result);
		$this->assertArrayHasKey('mpg', $result);
		$this->assertArrayHasKey('avi', $result);
		$this->assertArrayHasKey('wmv', $result);
		$this->assertArrayHasKey('flv', $result);
		$this->assertArrayHasKey('ogg', $result);
		$this->assertArrayHasKey('webm', $result);
		$this->assertArrayHasKey('3gpp', $result);
		$this->assertArrayHasKey('quicktime', $result);
	}

	public function testGetExtensionsAndMimeTypesMp4Structure(): void
	{
		$result = Video::getExtensionsAndMimeTypes();
		$this->assertCount(2, $result['mp4']);
		$this->assertIsArray($result['mp4'][0]); // Extensions
		$this->assertIsArray($result['mp4'][1]); // Mime types
	}

	/* ===================== getMimeTypeFromExtension() ===================== */

	public function testGetMimeTypeFromExtensionWithMp4(): void
	{
		$this->assertSame('video/mp4', Video::getMimeTypeFromExtension('.mp4'));
		$this->assertSame('video/mp4', Video::getMimeTypeFromExtension('.mp4v'));
	}

	public function testGetMimeTypeFromExtensionWithMpg(): void
	{
		$this->assertSame('video/mpeg', Video::getMimeTypeFromExtension('.mpg'));
		$this->assertSame('video/mpeg', Video::getMimeTypeFromExtension('.mpeg'));
	}

	public function testGetMimeTypeFromExtensionWithAvi(): void
	{
		$this->assertSame('video/x-msvideo', Video::getMimeTypeFromExtension('.avi'));
	}

	public function testGetMimeTypeFromExtensionWithWmv(): void
	{
		$this->assertSame('video/x-ms-wmv', Video::getMimeTypeFromExtension('.wmv'));
	}

	public function testGetMimeTypeFromExtensionWithFlv(): void
	{
		$this->assertSame('video/x-flv', Video::getMimeTypeFromExtension('.flv'));
	}

	public function testGetMimeTypeFromExtensionWithOgg(): void
	{
		$this->assertSame('video/ogg', Video::getMimeTypeFromExtension('.ogv'));
	}

	public function testGetMimeTypeFromExtensionWithWebm(): void
	{
		$this->assertSame('video/webm', Video::getMimeTypeFromExtension('.webm'));
	}

	public function testGetMimeTypeFromExtensionWith3Gpp(): void
	{
		$this->assertSame('video/3gpp', Video::getMimeTypeFromExtension('.3gp'));
	}

	public function testGetMimeTypeFromExtensionWithQuicktime(): void
	{
		$this->assertSame('video/quicktime', Video::getMimeTypeFromExtension('.mov'));
		$this->assertSame('video/quicktime', Video::getMimeTypeFromExtension('.qt'));
	}

	public function testGetMimeTypeFromExtensionWithInvalidExtension(): void
	{
		$this->assertNull(Video::getMimeTypeFromExtension('.invalid'));
		$this->assertNull(Video::getMimeTypeFromExtension('.txt'));
	}

	/* ===================== getExtensionFromMimeType() ===================== */

	public function testGetExtensionFromMimeTypeWithMp4(): void
	{
		$this->assertSame('mp4', Video::getExtensionFromMimeType('video/mp4'));
	}

	public function testGetExtensionFromMimeTypeWithMpeg(): void
	{
		$this->assertSame('mpg', Video::getExtensionFromMimeType('video/mpeg'));
	}

	public function testGetExtensionFromMimeTypeWithAvi(): void
	{
		$this->assertSame('avi', Video::getExtensionFromMimeType('video/x-msvideo'));
	}

	public function testGetExtensionFromMimeTypeWithWmv(): void
	{
		$this->assertSame('wmv', Video::getExtensionFromMimeType('video/x-ms-wmv'));
	}

	public function testGetExtensionFromMimeTypeWithFlv(): void
	{
		$this->assertSame('flv', Video::getExtensionFromMimeType('video/x-flv'));
	}

	public function testGetExtensionFromMimeTypeWithOgg(): void
	{
		$this->assertSame('ogv', Video::getExtensionFromMimeType('video/ogg'));
	}

	public function testGetExtensionFromMimeTypeWithWebm(): void
	{
		$this->assertSame('webm', Video::getExtensionFromMimeType('video/webm'));
	}

	public function testGetExtensionFromMimeTypeWith3Gpp(): void
	{
		$this->assertSame('3gp', Video::getExtensionFromMimeType('video/3gpp'));
	}

	public function testGetExtensionFromMimeTypeWithQuicktime(): void
	{
		$this->assertSame('mov', Video::getExtensionFromMimeType('video/quicktime'));
	}

	public function testGetExtensionFromMimeTypeWithInvalidMimeType(): void
	{
		$this->assertNull(Video::getExtensionFromMimeType('invalid/mime'));
		$this->assertNull(Video::getExtensionFromMimeType('text/plain'));
	}
}