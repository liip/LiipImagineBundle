<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Utils\Composer;

use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\RootPackageInterface;
use Composer\Script\Event;
use Liip\ImagineBundle\Utils\Composer\UpgradeNotice;

/**
 * @covers \Liip\ImagineBundle\Utils\Composer\ConsoleIO
 * @covers \Liip\ImagineBundle\Utils\Composer\UpgradeNotice
 */
class UpgradeNoticeTest extends \PHPUnit_Framework_TestCase
{
    public static function setUpBeforeClass()
    {
        if (false === version_compare(PHP_VERSION, '5.4', '>=')
         || false === interface_exists('\Composer\IO\IOInterface')) {
            static::markTestSkipped('"PHP 5.4" or greater and "\Composer\IO\IOInterface" required to test this component.');
        }
    }

    public function testWrite()
    {
        $io = $this->getIOMock(true);
        $io
            ->expects($this->atLeastOnce())
            ->method('write');

        UpgradeNotice::doWrite($this->getEventMock($io, '1.7.0'));
        UpgradeNotice::doWrite($this->getEventMock($io = $this->getIOMock(), '1.7.0'));

        $lineBuffer = $io->getBuffer();
        $firstLine = array_shift($lineBuffer);

        $this->assertTrue(count($lineBuffer) > 0);
        $this->assertContains('Update Notice:', $firstLine);

        foreach ($lineBuffer as $b) {
            $this->assertContains('liip/imagine-bundle', $b);
        }
    }

    public function testWriteOnUnsupportedVersion()
    {
        $io = $this->getIOMock(true);
        $io
            ->expects($this->never())
            ->method('write');

        UpgradeNotice::doWrite($this->getEventMock($io, '100.100.100'));
        UpgradeNotice::doWrite($this->getEventMock($io = $this->getIOMock(), '100.100.100'));

        $this->assertCount(0, $io->getBuffer());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|IOInterface|BufferedIO
     */
    private function getIOMock($mockWrite = false)
    {
        $io = $this->getMockBuilder('Liip\ImagineBundle\Tests\Utils\Composer\BufferedIO');

        if ($mockWrite) {
            $io->setMethods(array('write'));
        }

        return $io->getMockForAbstractClass();
    }

    /**
     * @param IOInterface $io
     * @param string      $version
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Event
     */
    private function getEventMock(IOInterface $io, $version)
    {
        $composer = $this->getComposerMock($this->getPackageMock($version));

        $event = $this
            ->getMockBuilder('Composer\Script\Event')
            ->setMethods(array('getComposer', 'getIO'))
            ->disableOriginalConstructor()
            ->getMock();

        $event
            ->method('getComposer')
            ->willReturn($composer);

        $event
            ->method('getIO')
            ->willReturn($io);

        return $event;
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|RootPackageInterface
     */
    private function getPackageMock($version)
    {
        $package = $this
            ->getMockBuilder('Composer\Package\RootPackageInterface')
            ->setMethods(array('getVersion', 'getName'))
            ->getMockForAbstractClass();

        $package
            ->method('getVersion')
            ->willReturn($version);

        $package
            ->method('getName')
            ->willReturn('liip/imagine-bundle');

        return $package;
    }

    /**
     * @param RootPackageInterface $package
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Composer
     */
    private function getComposerMock(RootPackageInterface $package)
    {
        $composer = $this
            ->getMockBuilder('Composer\Composer')
            ->setMethods(array('getPackage'))
            ->getMock();

        $composer
            ->method('getPackage')
            ->willReturn($package);

        return $composer;
    }
}

