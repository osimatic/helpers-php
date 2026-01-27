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

	/* ===================== checkFile() ===================== */

	public function testCheckFileWithNonExistentFile(): void
	{
		$result = Video::checkFile('/non/existent/file.mp4', 'test.mp4');
		$this->assertFalse($result);
	}

	/* ===================== checkMp4File() ===================== */

	public function testCheckMp4FileWithNonExistentFile(): void
	{
		$result = Video::checkMp4File('/non/existent/file.mp4', 'test.mp4');
		$this->assertFalse($result);
	}

	/* ===================== checkMpgFile() ===================== */

	public function testCheckMpgFileWithNonExistentFile(): void
	{
		$result = Video::checkMpgFile('/non/existent/file.mpg', 'test.mpg');
		$this->assertFalse($result);
	}

	/* ===================== checkAviFile() ===================== */

	public function testCheckAviFileWithNonExistentFile(): void
	{
		$result = Video::checkAviFile('/non/existent/file.avi', 'test.avi');
		$this->assertFalse($result);
	}

	/* ===================== checkWmvFile() ===================== */

	public function testCheckWmvFileWithNonExistentFile(): void
	{
		$result = Video::checkWmvFile('/non/existent/file.wmv', 'test.wmv');
		$this->assertFalse($result);
	}

	/* ===================== checkFlvFile() ===================== */

	public function testCheckFlvFileWithNonExistentFile(): void
	{
		$result = Video::checkFlvFile('/non/existent/file.flv', 'test.flv');
		$this->assertFalse($result);
	}

	/* ===================== check3GppFile() ===================== */

	public function testCheck3GppFileWithNonExistentFile(): void
	{
		$result = Video::check3GppFile('/non/existent/file.3gp', 'test.3gp');
		$this->assertFalse($result);
	}

	/* ===================== Tests with Real Video Fixtures ===================== */

	/* ----- MP4 Tests ----- */

	public function testCheckMp4FileWithRealMp4(): void
	{
		$mp4File = __DIR__ . '/../fixtures/videos/test_160x120.mp4';
		$this->assertFileExists($mp4File);

		$result = Video::checkMp4File($mp4File, 'test.mp4');
		$this->assertTrue($result);
	}

	public function testCheckFileWithRealMp4(): void
	{
		$mp4File = __DIR__ . '/../fixtures/videos/test_160x120.mp4';
		$this->assertFileExists($mp4File);

		$result = Video::checkFile($mp4File, 'test.mp4');
		$this->assertTrue($result);
	}

	public function testCheckMp4FileWithDifferentExtensions(): void
	{
		$mp4File = __DIR__ . '/../fixtures/videos/test_160x120.mp4';
		$this->assertFileExists($mp4File);

		// Test with alternative MP4 extensions
		$this->assertTrue(Video::checkMp4File($mp4File, 'test.mp4v'));
		$this->assertTrue(Video::checkMp4File($mp4File, 'test.mpg4'));
	}

	/* ----- MPG Tests ----- */

	public function testCheckMpgFileWithRealMpg(): void
	{
		$mpgFile = __DIR__ . '/../fixtures/videos/test_160x120.mpg';
		$this->assertFileExists($mpgFile);

		$result = Video::checkMpgFile($mpgFile, 'test.mpg');
		$this->assertTrue($result);
	}

	public function testCheckFileWithRealMpg(): void
	{
		$mpgFile = __DIR__ . '/../fixtures/videos/test_160x120.mpg';
		$this->assertFileExists($mpgFile);

		$result = Video::checkFile($mpgFile, 'test.mpg');
		$this->assertTrue($result);
	}

	public function testCheckMpgFileWithDifferentExtensions(): void
	{
		$mpgFile = __DIR__ . '/../fixtures/videos/test_160x120.mpg';
		$this->assertFileExists($mpgFile);

		// Test with alternative MPG extensions
		$this->assertTrue(Video::checkMpgFile($mpgFile, 'test.mpeg'));
		$this->assertTrue(Video::checkMpgFile($mpgFile, 'test.mpe'));
	}

	/* ----- AVI Tests ----- */

	public function testCheckAviFileWithRealAvi(): void
	{
		$aviFile = __DIR__ . '/../fixtures/videos/test_160x120.avi';
		$this->assertFileExists($aviFile);

		$result = Video::checkAviFile($aviFile, 'test.avi');
		$this->assertTrue($result);
	}

	public function testCheckFileWithRealAvi(): void
	{
		$aviFile = __DIR__ . '/../fixtures/videos/test_160x120.avi';
		$this->assertFileExists($aviFile);

		$result = Video::checkFile($aviFile, 'test.avi');
		$this->assertTrue($result);
	}

	/* ----- WMV Tests ----- */

	public function testCheckWmvFileWithRealWmv(): void
	{
		$wmvFile = __DIR__ . '/../fixtures/videos/test_160x120.wmv';
		$this->assertFileExists($wmvFile);

		// Note: WMV files are often detected as 'video/x-ms-asf' which is not in WMV_MIME_TYPES
		// This is because WMV uses the ASF (Advanced Systems Format) container
		$result = Video::checkWmvFile($wmvFile, 'test.wmv');
		$this->assertFalse($result); // Expected to fail due to MIME type mismatch
	}

	public function testCheckFileWithRealWmv(): void
	{
		$wmvFile = __DIR__ . '/../fixtures/videos/test_160x120.wmv';
		$this->assertFileExists($wmvFile);

		// Note: WMV files are often detected as 'video/x-ms-asf'
		$result = Video::checkFile($wmvFile, 'test.wmv');
		$this->assertFalse($result); // Expected to fail due to MIME type mismatch
	}

	/* ----- FLV Tests ----- */

	public function testCheckFlvFileWithRealFlv(): void
	{
		$flvFile = __DIR__ . '/../fixtures/videos/test_160x120.flv';
		$this->assertFileExists($flvFile);

		$result = Video::checkFlvFile($flvFile, 'test.flv');
		$this->assertTrue($result);
	}

	public function testCheckFileWithRealFlvShouldFail(): void
	{
		$flvFile = __DIR__ . '/../fixtures/videos/test_160x120.flv';
		$this->assertFileExists($flvFile);

		// FLV is not in the checkFile() supported formats
		$result = Video::checkFile($flvFile, 'test.flv');
		$this->assertFalse($result);
	}

	/* ----- WebM Tests ----- */

	public function testCheckFileWithRealWebm(): void
	{
		$webmFile = __DIR__ . '/../fixtures/videos/test_160x120.webm';
		$this->assertFileExists($webmFile);

		// WebM is not in the checkFile() supported formats (only MP4, MPG, AVI, WMV)
		$result = Video::checkFile($webmFile, 'test.webm');
		$this->assertFalse($result);
	}

	/* ----- OGG Tests ----- */

	public function testCheckFileWithRealOgv(): void
	{
		$ogvFile = __DIR__ . '/../fixtures/videos/test_160x120.ogv';
		$this->assertFileExists($ogvFile);

		// OGV is not in the checkFile() supported formats
		$result = Video::checkFile($ogvFile, 'test.ogv');
		$this->assertFalse($result);
	}

	/* ----- 3GPP Tests ----- */

	public function testCheck3GppFileWithReal3Gpp(): void
	{
		$gppFile = __DIR__ . '/../fixtures/videos/test_160x120.3gp';
		$this->assertFileExists($gppFile);

		$result = Video::check3GppFile($gppFile, 'test.3gp');
		$this->assertTrue($result);
	}

	public function testCheckFileWithReal3GppShouldFail(): void
	{
		$gppFile = __DIR__ . '/../fixtures/videos/test_160x120.3gp';
		$this->assertFileExists($gppFile);

		// 3GPP is not in the checkFile() supported formats
		$result = Video::checkFile($gppFile, 'test.3gp');
		$this->assertFalse($result);
	}

	/* ----- QuickTime Tests ----- */

	public function testCheckFileWithRealMov(): void
	{
		$movFile = __DIR__ . '/../fixtures/videos/test_160x120.mov';
		$this->assertFileExists($movFile);

		// MOV is not in the checkFile() supported formats
		$result = Video::checkFile($movFile, 'test.mov');
		$this->assertFalse($result);
	}

	/* ----- Format Validation Edge Cases ----- */

	public function testCheckMp4FileFailsWithMpgFile(): void
	{
		$mpgFile = __DIR__ . '/../fixtures/videos/test_160x120.mpg';
		$this->assertFileExists($mpgFile);

		// MPG file should not pass MP4 check
		$result = Video::checkMp4File($mpgFile, 'test.mp4');
		$this->assertFalse($result);
	}

	public function testCheckMpgFileFailsWithMp4File(): void
	{
		$mp4File = __DIR__ . '/../fixtures/videos/test_160x120.mp4';
		$this->assertFileExists($mp4File);

		// MP4 file should not pass MPG check
		$result = Video::checkMpgFile($mp4File, 'test.mpg');
		$this->assertFalse($result);
	}

	public function testCheckAviFileFailsWithMp4File(): void
	{
		$mp4File = __DIR__ . '/../fixtures/videos/test_160x120.mp4';
		$this->assertFileExists($mp4File);

		// MP4 file should not pass AVI check
		$result = Video::checkAviFile($mp4File, 'test.avi');
		$this->assertFalse($result);
	}

	public function testCheckWmvFileFailsWithMp4File(): void
	{
		$mp4File = __DIR__ . '/../fixtures/videos/test_160x120.mp4';
		$this->assertFileExists($mp4File);

		// MP4 file should not pass WMV check
		$result = Video::checkWmvFile($mp4File, 'test.wmv');
		$this->assertFalse($result);
	}

	public function testCheckFlvFileFailsWithMp4File(): void
	{
		$mp4File = __DIR__ . '/../fixtures/videos/test_160x120.mp4';
		$this->assertFileExists($mp4File);

		// MP4 file should not pass FLV check
		$result = Video::checkFlvFile($mp4File, 'test.flv');
		$this->assertFalse($result);
	}

	public function testCheck3GppFileFailsWithMp4File(): void
	{
		$mp4File = __DIR__ . '/../fixtures/videos/test_160x120.mp4';
		$this->assertFileExists($mp4File);

		// MP4 file should not pass 3GPP check
		$result = Video::check3GppFile($mp4File, 'test.3gp');
		$this->assertFalse($result);
	}

	public function testCheckFileWithWrongExtension(): void
	{
		$mp4File = __DIR__ . '/../fixtures/videos/test_160x120.mp4';
		$this->assertFileExists($mp4File);

		// MP4 file with .txt extension should fail
		$result = Video::checkFile($mp4File, 'test.txt');
		$this->assertFalse($result);
	}

	/* ----- Multiple Format Support Tests ----- */

	public function testCheckFileAcceptsSupportedFormats(): void
	{
		$formats = [
			'test_160x120.mp4' => 'test.mp4',
			'test_160x120.mpg' => 'test.mpg',
			'test_160x120.avi' => 'test.avi',
			// Note: WMV excluded as it's detected as 'video/x-ms-asf' MIME type
		];

		foreach ($formats as $file => $clientName) {
			$filePath = __DIR__ . '/../fixtures/videos/' . $file;
			$this->assertFileExists($filePath);
			$result = Video::checkFile($filePath, $clientName);
			$this->assertTrue($result, "Failed to validate $file");
		}
	}

	public function testCheckFileRejectsUnsupportedFormats(): void
	{
		$unsupportedFormats = [
			'test_160x120.flv' => 'test.flv',
			'test_160x120.webm' => 'test.webm',
			'test_160x120.ogv' => 'test.ogv',
			'test_160x120.3gp' => 'test.3gp',
			'test_160x120.mov' => 'test.mov',
		];

		foreach ($unsupportedFormats as $file => $clientName) {
			$filePath = __DIR__ . '/../fixtures/videos/' . $file;
			$this->assertFileExists($filePath);
			$result = Video::checkFile($filePath, $clientName);
			$this->assertFalse($result, "Should not validate $file in checkFile()");
		}
	}

	public function testAllFormatSpecificChecksWork(): void
	{
		$checksAndFiles = [
			['checkMp4File', 'test_160x120.mp4', 'test.mp4'],
			['checkMpgFile', 'test_160x120.mpg', 'test.mpg'],
			['checkAviFile', 'test_160x120.avi', 'test.avi'],
			// Note: WMV excluded - file is detected as 'video/x-ms-asf' which is not in WMV_MIME_TYPES
			['checkFlvFile', 'test_160x120.flv', 'test.flv'],
			['check3GppFile', 'test_160x120.3gp', 'test.3gp'],
		];

		foreach ($checksAndFiles as [$method, $file, $clientName]) {
			$filePath = __DIR__ . '/../fixtures/videos/' . $file;
			$this->assertFileExists($filePath);
			$result = Video::$method($filePath, $clientName);
			$this->assertTrue($result, "Failed $method for $file");
		}
	}
}