<?php

declare(strict_types=1);

namespace Tests\Media;

use Osimatic\Media\ImageResizer;
use Osimatic\Media\ImageSharpeningIntensity;
use Osimatic\Media\ImageCropPosition;
use Osimatic\Media\ImageResizeMode;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;

final class ImageResizerTest extends TestCase
{
	private string $fixturesPath;
	private string $tempDir;

	protected function setUp(): void
	{
		$this->fixturesPath = __DIR__ . '/../fixtures/images/';
		$this->tempDir = sys_get_temp_dir() . '/imageresizer_test_' . uniqid();

		if (!file_exists($this->tempDir)) {
			mkdir($this->tempDir, 0777, true);
		}
	}

	protected function tearDown(): void
	{
		// Clean up temp directory
		if (file_exists($this->tempDir)) {
			$files = glob($this->tempDir . '/*');
			foreach ($files as $file) {
				if (is_file($file)) {
					unlink($file);
				}
			}
			rmdir($this->tempDir);
		}
	}

	/* ===================== Constructor and Setters ===================== */

	public function testConstructorWithoutLogger(): void
	{
		$resizer = new ImageResizer();
		$this->assertInstanceOf(ImageResizer::class, $resizer);
	}

	public function testConstructorWithLogger(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$resizer = new ImageResizer($logger);
		$this->assertInstanceOf(ImageResizer::class, $resizer);
	}

	public function testSetLogger(): void
	{
		$resizer = new ImageResizer();
		$logger = $this->createMock(LoggerInterface::class);

		$result = $resizer->setLogger($logger);
		$this->assertSame($resizer, $result);
	}

	/* ===================== Parameter Validation ===================== */

	public function testValidateQualityTooLow(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Quality must be between 0 and 100, got: -1');

		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';

		$resizer->resize($testImage, 50, 50, -1);
	}

	public function testValidateQualityTooHigh(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Quality must be between 0 and 100, got: 150');

		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';

		$resizer->resize($testImage, 50, 50, 150);
	}

	public function testValidateQualityValid(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';
		$outputImage = $this->tempDir . '/output.jpg';

		// Should not throw for valid quality values
		$result = $resizer->resize($testImage, 50, 50, 0, null, null, $outputImage);
		$this->assertTrue($result);

		$result = $resizer->resize($testImage, 50, 50, 100, null, null, $outputImage);
		$this->assertTrue($result);

		$result = $resizer->resize($testImage, 50, 50, 50, null, null, $outputImage);
		$this->assertTrue($result);
	}

	public function testValidateColorInvalid(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid hex color format');

		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_200x100.png';

		$resizer->resize($testImage, 100, 100, null, 'GGGGGG');
	}

	public function testValidateColorValid(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_200x100.png';
		$outputImage = $this->tempDir . '/output.png';

		// Should not throw for valid color formats
		$result = $resizer->resize($testImage, 100, 100, null, '#FFFFFF', null, $outputImage);
		$this->assertTrue($result);

		$result = $resizer->resize($testImage, 100, 100, null, 'FFFFFF', null, $outputImage);
		$this->assertTrue($result);

		$result = $resizer->resize($testImage, 100, 100, null, '#FFF', null, $outputImage);
		$this->assertTrue($result);

		$result = $resizer->resize($testImage, 100, 100, null, 'FFF', null, $outputImage);
		$this->assertTrue($result);
	}

	public function testValidateRatioInvalidFormat(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Invalid ratio format');

		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';

		$resizer->resize($testImage, 100, 100, null, null, 'invalid');
	}

	public function testValidateRatioZeroValue(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Ratio values must be positive integers');

		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';

		$resizer->resize($testImage, 100, 100, null, null, '0:1');
	}

	public function testValidateRatioValid(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';
		$outputImage = $this->tempDir . '/output.jpg';

		// Should not throw for valid ratios
		$result = $resizer->resize($testImage, 100, 100, null, null, '1:1', $outputImage);
		$this->assertTrue($result);

		$result = $resizer->resize($testImage, 100, 100, null, null, '16:9', $outputImage);
		$this->assertTrue($result);

		$result = $resizer->resize($testImage, 100, 100, null, null, '4:3', $outputImage);
		$this->assertTrue($result);
	}

	/* ===================== Output Path ===================== */

	public function testOutputPathSeparateFile(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';
		$outputImage = $this->tempDir . '/resized_output.jpg';

		$result = $resizer->resize($testImage, 50, 50, null, null, null, $outputImage);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);
		$this->assertFileExists($testImage); // Original should still exist
	}

	public function testOutputPathReplacesOriginal(): void
	{
		$resizer = new ImageResizer();

		// Copy test image to temp directory
		$testImage = $this->tempDir . '/test_copy.jpg';
		copy($this->fixturesPath . 'test_100x50.jpg', $testImage);

		$originalMtime = filemtime($testImage);
		sleep(1); // Ensure time difference

		// Resize without output path should replace original
		$result = $resizer->resize($testImage, 50, 50);

		$this->assertTrue($result);
		$this->assertFileExists($testImage);
		$this->assertGreaterThan($originalMtime, filemtime($testImage));
	}

	/* ===================== Crop Positions ===================== */

	public function testCropPositionCenter(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg'; // 100x50, landscape
		$outputImage = $this->tempDir . '/crop_center.jpg';

		// Crop to 1:1 (square) with center position
		$result = $resizer->resize(
			$testImage,
			50,
			50,
			null,
			null,
			'1:1',
			$outputImage,
			ImageSharpeningIntensity::MEDIUM,
			ImageCropPosition::CENTER
		);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);
	}

	public function testCropPositionTop(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_200x100.png';
		$outputImage = $this->tempDir . '/crop_top.png';

		$result = $resizer->resize(
			$testImage,
			100,
			100,
			null,
			null,
			'1:1',
			$outputImage,
			ImageSharpeningIntensity::MEDIUM,
			ImageCropPosition::TOP
		);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);
	}

	public function testCropPositionBottom(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_200x100.png';
		$outputImage = $this->tempDir . '/crop_bottom.png';

		$result = $resizer->resize(
			$testImage,
			100,
			100,
			null,
			null,
			'1:1',
			$outputImage,
			ImageSharpeningIntensity::MEDIUM,
			ImageCropPosition::BOTTOM
		);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);
	}

	public function testCropPositionLeft(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_200x100.png';
		$outputImage = $this->tempDir . '/crop_left.png';

		$result = $resizer->resize(
			$testImage,
			100,
			100,
			null,
			null,
			'1:1',
			$outputImage,
			ImageSharpeningIntensity::MEDIUM,
			ImageCropPosition::LEFT
		);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);
	}

	public function testCropPositionRight(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_200x100.png';
		$outputImage = $this->tempDir . '/crop_right.png';

		$result = $resizer->resize(
			$testImage,
			100,
			100,
			null,
			null,
			'1:1',
			$outputImage,
			ImageSharpeningIntensity::MEDIUM,
			ImageCropPosition::RIGHT
		);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);
	}

	public function testCropPositionTopLeft(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_200x100.png';
		$outputImage = $this->tempDir . '/crop_topleft.png';

		$result = $resizer->resize(
			$testImage,
			100,
			100,
			null,
			null,
			'1:1',
			$outputImage,
			ImageSharpeningIntensity::MEDIUM,
			ImageCropPosition::TOP_LEFT
		);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);
	}

	public function testCropPositionBottomRight(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_200x100.png';
		$outputImage = $this->tempDir . '/crop_bottomright.png';

		$result = $resizer->resize(
			$testImage,
			100,
			100,
			null,
			null,
			'1:1',
			$outputImage,
			ImageSharpeningIntensity::MEDIUM,
			ImageCropPosition::BOTTOM_RIGHT
		);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);
	}

	/* ===================== Sharpening Intensity ===================== */

	public function testSharpeningNone(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';
		$outputImage = $this->tempDir . '/sharp_none.jpg';

		$result = $resizer->resize(
			$testImage,
			50,
			25,
			null,
			null,
			null,
			$outputImage,
			ImageSharpeningIntensity::NONE
		);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);
	}

	public function testSharpeningLow(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';
		$outputImage = $this->tempDir . '/sharp_low.jpg';

		$result = $resizer->resize(
			$testImage,
			50,
			25,
			null,
			null,
			null,
			$outputImage,
			ImageSharpeningIntensity::LOW
		);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);
	}

	public function testSharpeningMedium(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';
		$outputImage = $this->tempDir . '/sharp_medium.jpg';

		$result = $resizer->resize(
			$testImage,
			50,
			25,
			null,
			null,
			null,
			$outputImage,
			ImageSharpeningIntensity::MEDIUM
		);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);
	}

	public function testSharpeningHigh(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';
		$outputImage = $this->tempDir . '/sharp_high.jpg';

		$result = $resizer->resize(
			$testImage,
			50,
			25,
			null,
			null,
			null,
			$outputImage,
			ImageSharpeningIntensity::HIGH
		);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);
	}

	public function testSharpeningVeryHigh(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';
		$outputImage = $this->tempDir . '/sharp_veryhigh.jpg';

		$result = $resizer->resize(
			$testImage,
			50,
			25,
			null,
			null,
			null,
			$outputImage,
			ImageSharpeningIntensity::VERY_HIGH
		);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);
	}

	/* ===================== Resize Modes ===================== */

	public function testResizeModeFit(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_200x100.png'; // 2:1 landscape
		$outputImage = $this->tempDir . '/mode_fit.png';

		// Resize to 100x100 with FIT mode - should fit within, result will be 100x50
		$result = $resizer->resize(
			$testImage,
			100,
			100,
			null,
			null,
			null,
			$outputImage,
			ImageSharpeningIntensity::MEDIUM,
			ImageCropPosition::CENTER,
			ImageResizeMode::FIT
		);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);

		$size = getimagesize($outputImage);
		$this->assertEquals(100, $size[0]); // Width should be 100
		$this->assertLessThanOrEqual(100, $size[1]); // Height should be <= 100
	}

	public function testResizeModeStretch(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_200x100.png'; // 2:1 landscape
		$outputImage = $this->tempDir . '/mode_stretch.png';

		// Resize to 100x100 with STRETCH mode - should be exactly 100x100 (distorted)
		$result = $resizer->resize(
			$testImage,
			100,
			100,
			null,
			null,
			null,
			$outputImage,
			ImageSharpeningIntensity::MEDIUM,
			ImageCropPosition::CENTER,
			ImageResizeMode::STRETCH
		);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);

		$size = getimagesize($outputImage);
		$this->assertEquals(100, $size[0]); // Width should be exactly 100
		$this->assertEquals(100, $size[1]); // Height should be exactly 100
	}

	public function testResizeModeFill(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_200x100.png'; // 2:1 landscape
		$outputImage = $this->tempDir . '/mode_fill.png';

		// Resize to 100x100 with FILL mode - should cover entire area
		$result = $resizer->resize(
			$testImage,
			100,
			100,
			null,
			null,
			null,
			$outputImage,
			ImageSharpeningIntensity::MEDIUM,
			ImageCropPosition::CENTER,
			ImageResizeMode::FILL
		);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);

		$size = getimagesize($outputImage);
		$this->assertGreaterThanOrEqual(100, $size[0]); // At least one dimension should be >= target
		$this->assertGreaterThanOrEqual(100, $size[1]);
	}

	public function testResizeModeCover(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_200x100.png'; // 2:1 landscape
		$outputImage = $this->tempDir . '/mode_cover.png';

		// Resize to 100x100 with COVER mode - similar to FILL
		$result = $resizer->resize(
			$testImage,
			100,
			100,
			null,
			null,
			null,
			$outputImage,
			ImageSharpeningIntensity::MEDIUM,
			ImageCropPosition::CENTER,
			ImageResizeMode::COVER
		);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);

		$size = getimagesize($outputImage);
		$this->assertGreaterThanOrEqual(100, $size[0]); // At least one dimension should be >= target
		$this->assertGreaterThanOrEqual(100, $size[1]);
	}

	/* ===================== Supported Formats ===================== */

	public function testResizeJpg(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';
		$outputImage = $this->tempDir . '/resized.jpg';

		$result = $resizer->resize($testImage, 50, 25, null, null, null, $outputImage);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);

		$size = getimagesize($outputImage);
		$this->assertEquals(50, $size[0]);
		$this->assertEquals(25, $size[1]);
	}

	public function testResizePng(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_200x100.png';
		$outputImage = $this->tempDir . '/resized.png';

		$result = $resizer->resize($testImage, 100, 50, null, null, null, $outputImage);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);

		$size = getimagesize($outputImage);
		$this->assertEquals(100, $size[0]);
		$this->assertEquals(50, $size[1]);
	}

	public function testResizeGif(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_150x75.gif';
		$outputImage = $this->tempDir . '/resized.png'; // GIF is converted to PNG

		$result = $resizer->resize($testImage, 75, 38, null, null, null, $outputImage);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);
	}

	public function testResizeWebP(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_80x60.webp';
		$outputImage = $this->tempDir . '/resized.webp';

		$result = $resizer->resize($testImage, 40, 30, null, null, null, $outputImage);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);

		$size = getimagesize($outputImage);
		$this->assertEquals(40, $size[0]);
		$this->assertEquals(30, $size[1]);
	}

	/* ===================== Background Color ===================== */

	public function testBackgroundColorWithPng(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_200x100.png';
		$outputImage = $this->tempDir . '/with_bg.png';

		// Resize PNG with background color (fills transparency)
		$result = $resizer->resize($testImage, 100, 50, null, '#FF0000', null, $outputImage);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);
	}

	public function testTransparencyPreservedWithoutColor(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->fixturesPath . 'test_200x100.png';
		$outputImage = $this->tempDir . '/no_bg.png';

		// Resize PNG without background color (preserves transparency)
		$result = $resizer->resize($testImage, 100, 50, null, null, null, $outputImage);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);
	}

	/* ===================== Edge Cases ===================== */

	public function testNonExistentFile(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->tempDir . '/nonexistent.jpg';
		$outputImage = $this->tempDir . '/output.jpg';

		$result = $resizer->resize($testImage, 100, 100, null, null, null, $outputImage);

		$this->assertFalse($result);
	}

	public function testUnsupportedFormat(): void
	{
		$resizer = new ImageResizer();

		// Create a text file with jpg extension
		$testImage = $this->tempDir . '/fake.jpg';
		file_put_contents($testImage, 'This is not an image');
		$outputImage = $this->tempDir . '/output.jpg';

		$result = $resizer->resize($testImage, 100, 100, null, null, null, $outputImage);

		$this->assertFalse($result);
	}

	public function testNoResizeNeeded(): void
	{
		$resizer = new ImageResizer();
		$testImage = $this->tempDir . '/test_copy.jpg';
		copy($this->fixturesPath . 'test_100x50.jpg', $testImage);

		// Image is already smaller than target dimensions
		$result = $resizer->resize($testImage, 200, 200);

		// Should return true without modifying
		$this->assertTrue($result);
	}
}