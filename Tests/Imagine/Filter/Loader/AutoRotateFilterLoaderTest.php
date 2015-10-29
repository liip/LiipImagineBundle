<?php

namespace Liip\ImagineBundle\Tests\Filter;

use Liip\ImagineBundle\Imagine\Filter\Loader\AutoRotateFilterLoader;
use Liip\ImagineBundle\Tests\AbstractTest;

/**
 * Test cases for RotateFilterLoader class.
 * Depending on the EXIF value checks whether rotate and flip are called.
 *
 * @covers Liip\ImagineBundle\Imagine\Filter\Loader\AutoRotateFilterLoader
 */
class AutoRotateFilterLoaderTest extends AbstractTest
{
	private $orientationKey = 'exif.Orientation';

	/**
	 * Starts a test with expected results.
	 * @param $exifValue {String} The exif value to be returned by the metadata mock.
	 * @param $expectCallRotateValue {null|number} The expected rotation value, null if no rotation is expected.
	 * @param $expectCallFlip {Boolean} True if a horizontal flip is expected, false otherwise.
	 */
	private function loadExif($exifValue, $expectCallRotateValue, $expectCallFlip)
	{
		$loader = new AutoRotateFilterLoader();

		// Mocks the metadata and makes it return the expected exif value for the rotation.
		$metaData = $this->getMockMetaData();

		$metaData
			->expects($this->once())
			->method('offsetGet')
			->with($this->orientationKey)
			->willReturn($exifValue)
		;

		// Mocks the image and makes it use the fake meta data.
		$image = $this->getMockImage();

		$image
			->expects($this->once())
			->method('metadata')
			->willReturn($metaData)
		;

		// Checks that rotate is called with $expectCallRotateValue, or not called at all if $expectCallRotateValue is null.
		$image
			->expects($expectCallRotateValue !== null ? $this->once() : $this->never())
			->method('rotate')
			->with($expectCallRotateValue)
		;

		// Checks that rotate is called if $expectCallFlip is true, not called if $expectCallFlip is false.
		$image
			->expects($expectCallFlip ? $this->once() : $this->never())
			->method('flipHorizontally')
		;

		$loader->load($image);
	}


	public function testLoadAllExifs()
    {
		/*
			1: 0°
			2: 0° flipped
			3: 180°
			4: 180° flipped
			5: 90° flipped
			6: 90°
			7: -90° flipped
			8: -90°
		 */
		$testCases = array(
			array("1", null, false),
			array("2", null, true),
			array("3", 180, false),
			array("4", 180, true),
			array("5", 90, true),
			array("6", 90, false),
			array("7", -90, true),
			array("8", -90, false),
		);

		foreach ($testCases as $testCase) {
			$this->loadExif($testCase[0], $testCase[1], $testCase[2]);
		}
	}
}
