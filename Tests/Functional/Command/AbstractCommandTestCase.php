<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Functional\Command;

use Liip\ImagineBundle\Tests\Functional\AbstractSetupWebTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @coversNothing
 */
class AbstractCommandTestCase extends AbstractSetupWebTestCase
{
    protected function executeConsole(string $commandName, array $arguments = [], &$return = null): string
    {
        $application = new Application($this->createClient()->getKernel());
        $command = $application->find($commandName);

        $arguments = array_replace(['command' => $command->getName()], $arguments);

        $commandTester = new CommandTester($command);
        $return = $commandTester->execute($arguments, ['--env' => 'test']);

        return $commandTester->getDisplay();
    }

    /**
     * @param string[] $images
     * @param string[] $filters
     */
    protected function assertImagesNotExist(array $images, array $filters): void
    {
        foreach ($images as $i) {
            foreach ($filters as $f) {
                $this->assertFileNotExists(sprintf('%s/%s/%s', $this->cacheRoot, $f, $i));
            }
        }
    }

    /**
     * @param string[] $images
     * @param string[] $filters
     */
    protected function assertImagesExist($images, $filters): void
    {
        foreach ($images as $i) {
            foreach ($filters as $f) {
                $this->assertFileExists(sprintf('%s/%s/%s', $this->cacheRoot, $f, $i));
            }
        }
    }

    protected function assertOutputContainsResolvedImages($output, array $images, array $filters): void
    {
        foreach ($images as $i) {
            foreach ($filters as $f) {
                $this->assertOutputContainsImage($output, $i, $f, 'resolved');
            }
        }
    }

    protected function assertOutputContainsCachedImages($output, array $images, array $filters): void
    {
        foreach ($images as $i) {
            foreach ($filters as $f) {
                $this->assertOutputContainsImage($output, $i, $f, 'cached');
            }
        }
    }

    protected function assertOutputContainsFailedImages($output, array $images, array $filters): void
    {
        foreach ($images as $i) {
            foreach ($filters as $f) {
                $this->assertContains(sprintf('%s[%s] (failed)', $i, $f), $output);
            }
        }
    }

    protected function assertOutputContainsImage($output, $image, $filter, $type): void
    {
        $expected = vsprintf('%s[%s] (%s) http://localhost/media/cache/%s/%s', [
            $image,
            $filter,
            $type,
            $filter,
            $image,
        ]);
        $this->assertContains($expected, $output);
    }

    /**
     * @param string[] $images
     * @param string[] $filters
     */
    protected function delResolvedImages(array $images, array $filters): void
    {
        foreach ($images as $i) {
            foreach ($filters as $f) {
                if (file_exists($f = sprintf('%s/%s/%s', $this->cacheRoot, $f, $i))) {
                    @unlink($f);
                }
            }
        }
    }

    /**
     * @param string[] $images
     * @param string[] $filters
     */
    protected function putResolvedImages(array $images, array $filters, string $content = 'anImageContent'): void
    {
        foreach ($images as $i) {
            foreach ($filters as $f) {
                $this->filesystem->dumpFile(sprintf('%s/%s/%s', $this->cacheRoot, $f, $i), $content);
            }
        }
    }
}
