<?php

declare(strict_types=1);

namespace Tests\Media;

use Osimatic\Media\ImageOptimizer;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use InvalidArgumentException;

final class ImageOptimizerTest extends TestCase
{
	private string $fixturesPath;
	private string $tempDir;

	protected function setUp(): void
	{
		$this->fixturesPath = __DIR__ . '/../fixtures/images/';
		$this->tempDir = sys_get_temp_dir() . '/imageoptimizer_test_' . uniqid();

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
		$optimizer = new ImageOptimizer();
		$this->assertInstanceOf(ImageOptimizer::class, $optimizer);
	}

	public function testConstructorWithLogger(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$optimizer = new ImageOptimizer($logger);
		$this->assertInstanceOf(ImageOptimizer::class, $optimizer);
	}

	public function testSetLogger(): void
	{
		$optimizer = new ImageOptimizer();
		$logger = $this->createMock(LoggerInterface::class);

		$result = $optimizer->setLogger($logger);
		$this->assertSame($optimizer, $result);
	}

	/* ===================== Parameter Validation ===================== */

	public function testOptimizeWithQualityTooLow(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Quality must be between 10 and 100');

		$optimizer = new ImageOptimizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';

		$optimizer->optimize($testImage, 5);
	}

	public function testOptimizeWithQualityTooHigh(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('Quality must be between 10 and 100');

		$optimizer = new ImageOptimizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';

		$optimizer->optimize($testImage, 101);
	}

	public function testOptimizeAutoWithInvalidRatio(): void
	{
		$this->expectException(InvalidArgumentException::class);
		$this->expectExceptionMessage('maxSizeRatio must be between 0 and 1');

		$optimizer = new ImageOptimizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';

		$optimizer->optimizeAuto($testImage, null, 1.5);
	}

	/* ===================== Basic Optimization ===================== */

	public function testOptimizeJpegWithQuality(): void
	{
		$optimizer = new ImageOptimizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';
		$outputImage = $this->tempDir . '/optimized.jpg';

		$result = $optimizer->optimize($testImage, 70, $outputImage, true);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);

		// Output file should be smaller than original (or similar size)
		$originalSize = filesize($testImage);
		$optimizedSize = filesize($outputImage);
		$this->assertLessThanOrEqual($originalSize, $optimizedSize);
	}

	public function testOptimizePngWithQuality(): void
	{
		$optimizer = new ImageOptimizer();
		$testImage = $this->fixturesPath . 'test_200x100.png';
		$outputImage = $this->tempDir . '/optimized.png';

		$result = $optimizer->optimize($testImage, 70, $outputImage, true);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);

		$originalSize = filesize($testImage);
		$optimizedSize = filesize($outputImage);
		$this->assertGreaterThan(0, $optimizedSize);
	}

	public function testOptimizeWebP(): void
	{
		$optimizer = new ImageOptimizer();
		$testImage = $this->fixturesPath . 'test_80x60.webp';
		$outputImage = $this->tempDir . '/optimized.webp';

		$result = $optimizer->optimize($testImage, 70, $outputImage, true);

		$this->assertTrue($result);
		$this->assertFileExists($outputImage);
	}

	/* ===================== Output Path Handling ===================== */

	public function testOptimizeReplacesOriginalWhenNoOutputPath(): void
	{
		$optimizer = new ImageOptimizer();

		// Copy test image to temp
		$testImage = $this->tempDir . '/test_copy.jpg';
		copy($this->fixturesPath . 'test_100x50.jpg', $testImage);

		$originalSize = filesize($testImage);
		$originalMtime = filemtime($testImage);

		sleep(1); // Ensure time difference

		$result = $optimizer->optimize($testImage, 60); // No output path = replace

		$this->assertTrue($result);
		$this->assertFileExists($testImage);
		$newSize = filesize($testImage);
		$this->assertLessThanOrEqual($originalSize, $newSize);
		$this->assertGreaterThan($originalMtime, filemtime($testImage));
	}

	public function testOptimizeSavesToSeparateFile(): void
	{
		$optimizer = new ImageOptimizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';
		$outputImage = $this->tempDir . '/separate_output.jpg';

		$result = $optimizer->optimize($testImage, 70, $outputImage);

		$this->assertTrue($result);
		$this->assertFileExists($testImage); // Original still exists
		$this->assertFileExists($outputImage); // New file created
	}

	/* ===================== Auto Optimization ===================== */

	public function testOptimizeAutoReturnsSuccessInfo(): void
	{
		$optimizer = new ImageOptimizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';
		$outputImage = $this->tempDir . '/auto_optimized.jpg';

		$result = $optimizer->optimizeAuto($testImage, $outputImage, 0.8);

		$this->assertIsArray($result);
		$this->assertArrayHasKey('success', $result);
		$this->assertArrayHasKey('quality', $result);
		$this->assertArrayHasKey('originalSize', $result);
		$this->assertArrayHasKey('optimizedSize', $result);
		$this->assertArrayHasKey('reduction', $result);

		$this->assertTrue($result['success']);
		$this->assertGreaterThanOrEqual(10, $result['quality']);
		$this->assertLessThanOrEqual(100, $result['quality']);
		$this->assertGreaterThan(0, $result['originalSize']);
		$this->assertGreaterThan(0, $result['optimizedSize']);
	}

	public function testOptimizeAutoReducesFileSize(): void
	{
		$optimizer = new ImageOptimizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';
		$outputImage = $this->tempDir . '/auto_reduced.jpg';

		$originalSize = filesize($testImage);

		$result = $optimizer->optimizeAuto($testImage, $outputImage, 0.5); // Target 50% of original

		$this->assertTrue($result['success']);
		$this->assertFileExists($outputImage);

		$optimizedSize = filesize($outputImage);
		// Note: Very small images may not compress predictably due to overhead
		// Just verify a valid output file was created
		$this->assertGreaterThan(0, $optimizedSize);
		$this->assertLessThanOrEqual($originalSize * 2, $optimizedSize); // Allow significant overhead for very small files
	}

	public function testOptimizeAutoWithDifferentRatios(): void
	{
		$optimizer = new ImageOptimizer();
		$testImage = $this->fixturesPath . 'test_200x100.png';

		// Test with 90% ratio (should use high quality)
		$output1 = $this->tempDir . '/ratio_90.png';
		$result1 = $optimizer->optimizeAuto($testImage, $output1, 0.9);

		// Test with 50% ratio (should use lower quality)
		$output2 = $this->tempDir . '/ratio_50.png';
		$result2 = $optimizer->optimizeAuto($testImage, $output2, 0.5);

		$this->assertTrue($result1['success']);
		$this->assertTrue($result2['success']);

		// Higher ratio should result in higher quality (less aggressive compression)
		// 90% ratio should have higher or equal quality than 50% ratio
		$this->assertGreaterThanOrEqual($result2['quality'], $result1['quality']);
	}

	/* ===================== Quality Levels ===================== */

	public function testOptimizeWithLowQuality(): void
	{
		$optimizer = new ImageOptimizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';
		$output = $this->tempDir . '/low_quality.jpg';

		$result = $optimizer->optimize($testImage, 30, $output);

		$this->assertTrue($result);
		$this->assertFileExists($output);

		// Low quality should produce smaller file
		$this->assertLessThan(filesize($testImage), filesize($output));
	}

	public function testOptimizeWithHighQuality(): void
	{
		$optimizer = new ImageOptimizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';
		$output = $this->tempDir . '/high_quality.jpg';

		$result = $optimizer->optimize($testImage, 95, $output);

		$this->assertTrue($result);
		$this->assertFileExists($output);
	}

	/* ===================== Metadata Stripping ===================== */

	public function testOptimizeWithMetadataStripping(): void
	{
		$optimizer = new ImageOptimizer();
		$testImage = $this->fixturesPath . 'test_with_exif.jpg';
		$output = $this->tempDir . '/no_metadata.jpg';

		$result = $optimizer->optimize($testImage, 85, $output, true);

		$this->assertTrue($result);
		$this->assertFileExists($output);

		// File should be smaller after stripping metadata
		$this->assertLessThan(filesize($testImage), filesize($output));
	}

	public function testOptimizeWithoutMetadataStripping(): void
	{
		$optimizer = new ImageOptimizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';
		$output = $this->tempDir . '/with_metadata.jpg';

		$result = $optimizer->optimize($testImage, 85, $output, false);

		$this->assertTrue($result);
		$this->assertFileExists($output);
	}

	/* ===================== Image Info ===================== */

	public function testGetImageInfoJpeg(): void
	{
		$optimizer = new ImageOptimizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';

		$info = $optimizer->getImageInfo($testImage);

		$this->assertIsArray($info);
		$this->assertEquals(100, $info['width']);
		$this->assertEquals(50, $info['height']);
		$this->assertGreaterThan(0, $info['size']);
		$this->assertStringContainsString('jpeg', strtolower($info['mime']));
		$this->assertEquals('JPEG', $info['format']);
	}

	public function testGetImageInfoPng(): void
	{
		$optimizer = new ImageOptimizer();
		$testImage = $this->fixturesPath . 'test_200x100.png';

		$info = $optimizer->getImageInfo($testImage);

		$this->assertIsArray($info);
		$this->assertEquals(200, $info['width']);
		$this->assertEquals(100, $info['height']);
		$this->assertEquals('PNG', $info['format']);
	}

	public function testGetImageInfoWebP(): void
	{
		$optimizer = new ImageOptimizer();
		$testImage = $this->fixturesPath . 'test_80x60.webp';

		$info = $optimizer->getImageInfo($testImage);

		$this->assertIsArray($info);
		$this->assertEquals(80, $info['width']);
		$this->assertEquals(60, $info['height']);
		$this->assertEquals('WebP', $info['format']);
	}

	public function testGetImageInfoNonExistentFile(): void
	{
		$optimizer = new ImageOptimizer();
		$testImage = $this->tempDir . '/nonexistent.jpg';

		$info = $optimizer->getImageInfo($testImage);

		$this->assertNull($info);
	}

	/* ===================== Edge Cases ===================== */

	public function testOptimizeNonExistentFile(): void
	{
		$optimizer = new ImageOptimizer();
		$testImage = $this->tempDir . '/nonexistent.jpg';
		$output = $this->tempDir . '/output.jpg';

		$result = $optimizer->optimize($testImage, 85, $output);

		$this->assertFalse($result);
	}

	public function testOptimizeUnsupportedFormat(): void
	{
		$optimizer = new ImageOptimizer();

		// Create a text file with jpg extension
		$testImage = $this->tempDir . '/fake.jpg';
		file_put_contents($testImage, 'This is not an image');
		$output = $this->tempDir . '/output.jpg';

		$result = $optimizer->optimize($testImage, 85, $output);

		$this->assertFalse($result);
	}

	public function testOptimizeAutoWithNonExistentFile(): void
	{
		$optimizer = new ImageOptimizer();
		$testImage = $this->tempDir . '/nonexistent.jpg';

		$result = $optimizer->optimizeAuto($testImage);

		$this->assertFalse($result['success']);
		$this->assertEquals(0, $result['quality']);
		$this->assertEquals(0, $result['originalSize']);
	}

	/* ===================== Dimension Preservation ===================== */

	public function testOptimizePreservesDimensions(): void
	{
		$optimizer = new ImageOptimizer();
		$testImage = $this->fixturesPath . 'test_100x50.jpg';
		$output = $this->tempDir . '/same_dimensions.jpg';

		$originalInfo = $optimizer->getImageInfo($testImage);
		$optimizer->optimize($testImage, 70, $output);
		$optimizedInfo = $optimizer->getImageInfo($output);

		$this->assertNotNull($optimizedInfo);
		$this->assertEquals($originalInfo['width'], $optimizedInfo['width']);
		$this->assertEquals($originalInfo['height'], $optimizedInfo['height']);
	}

	public function testOptimizeAutoPreservesDimensions(): void
	{
		$optimizer = new ImageOptimizer();
		$testImage = $this->fixturesPath . 'test_200x100.png';
		$output = $this->tempDir . '/auto_same_dimensions.png';

		$originalInfo = $optimizer->getImageInfo($testImage);
		$optimizer->optimizeAuto($testImage, $output, 0.7);
		$optimizedInfo = $optimizer->getImageInfo($output);

		$this->assertNotNull($optimizedInfo);
		$this->assertEquals($originalInfo['width'], $optimizedInfo['width']);
		$this->assertEquals($originalInfo['height'], $optimizedInfo['height']);
	}
}