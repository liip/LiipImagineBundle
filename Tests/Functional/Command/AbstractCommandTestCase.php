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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class AbstractCommandTestCase extends AbstractSetupWebTestCase
{
    /**
     * @param Command $command
     * @param array   $arguments
     * @param int     $return
     *
     * @return string
     */
    protected function executeConsole(Command $command, array $arguments = array(), &$return = null)
    {
        $command->setApplication(new Application($this->createClient()->getKernel()));

        if ($command instanceof ContainerAwareCommand) {
            $command->setContainer($this->createClient()->getContainer());
        }

        $arguments = array_replace(array('command' => $command->getName()), $arguments);

        $commandTester = new CommandTester($command);
        $return = $commandTester->execute($arguments, array('--env' => 'test'));

        return $commandTester->getDisplay();
    }

    /**
     * @param string[] $images
     * @param string[] $filters
     */
    protected function assertImagesNotExist($images, $filters)
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
    protected function assertImagesExist($images, $filters)
    {
        foreach ($images as $i) {
            foreach ($filters as $f) {
                $this->assertFileExists(sprintf('%s/%s/%s', $this->cacheRoot, $f, $i));
            }
        }
    }

    /**
     * @param string $output
     * @param array  $images
     * @param array  $filters
     */
    protected function assertOutputContainsResolvedImages($output, array $images, array $filters)
    {
        foreach ($images as $i) {
            foreach ($filters as $f) {
                $this->assertOutputContainsImage($output, $i, $f, 'resolved');
            }
        }
    }

    /**
     * @param string $output
     * @param array  $images
     * @param array  $filters
     */
    protected function assertOutputContainsCachedImages($output, array $images, array $filters)
    {
        foreach ($images as $i) {
            foreach ($filters as $f) {
                $this->assertOutputContainsImage($output, $i, $f, 'cached');
            }
        }
    }

    /**
     * @param string $output
     * @param array  $images
     * @param array  $filters
     */
    protected function assertOutputContainsFailedImages($output, array $images, array $filters)
    {
        foreach ($images as $i) {
            foreach ($filters as $f) {
                $this->assertContains(sprintf('%s[%s] (failed)', $i, $f), $output);
            }
        }
    }

    /**
     * @param string $output
     * @param string $image
     * @param string $filter
     * @param string $type
     */
    protected function assertOutputContainsImage($output, $image, $filter, $type)
    {
        $expected = vsprintf('%s[%s] (%s) http://localhost/media/cache/%s/%s', array(
            $image,
            $filter,
            $type,
            $filter,
            $image,
        ));
        $this->assertContains($expected, $output);
    }

    /**
     * @param string[] $images
     * @param string[] $filters
     */
    protected function delResolvedImages(array $images, array $filters)
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
     * @param string   $content
     */
    protected function putResolvedImages(array $images, array $filters, $content = 'anImageContent')
    {
        foreach ($images as $i) {
            foreach ($filters as $f) {
                $this->filesystem->dumpFile(sprintf('%s/%s/%s', $this->cacheRoot, $f, $i), $content);
            }
        }
    }
}
