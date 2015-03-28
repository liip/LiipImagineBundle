<?php

namespace Liip\ImagineBundle\Tests\Functional\Command;

use Liip\ImagineBundle\Tests\Functional\WebTestCase;
use Liip\ImagineBundle\Command\RemoveCacheCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @covers Liip\ImagineBundle\Command\RemoveCacheCommand
 */
class RemoveCacheTest extends WebTestCase
{
    protected $client;

    protected $webRoot;

    protected $filesystem;

    protected $cacheRoot;

    public function setUp()
    {
        parent::setUp();

        $this->client = $this->createClient();

        $this->webRoot = self::$kernel->getContainer()->getParameter('kernel.root_dir').'/web';
        $this->cacheRoot = $this->webRoot.'/media/cache';

        $this->filesystem = new Filesystem();
        $this->filesystem->remove($this->cacheRoot);
    }

    public function testExecuteSuccessfullyWithEmptyCacheAndWithoutParameters()
    {
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');

        $this->executeConsole(new RemoveCacheCommand());
    }

    public function testExecuteSuccessfullyWithEmptyCacheAndOnePathAndOneFilter()
    {
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');

        $this->executeConsole(
            new RemoveCacheCommand(),
            array(
                'paths' => array('images/cats.jpeg'),
                '--filters' => array('thumbnail_web_path'),
        ));
    }

    public function testExecuteSuccessfullyWithEmptyCacheAndMultiplePaths()
    {
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');

        $this->executeConsole(
            new RemoveCacheCommand(),
            array('paths' => array('images/cats.jpeg', 'images/cats2.jpeg'))
        );
    }

    public function testExecuteSuccessfullyWithEmptyCacheAndMultipleFilters()
    {
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');

        $this->executeConsole(
            new RemoveCacheCommand(),
            array('--filters' => array('thumbnail_web_path', 'thumbnail_default'))
        );
    }

    public function testShouldRemoveAllCacheIfParametersDoesNotPassed()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg',
            'anImageContent2'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats.jpeg',
            'anImageContent'
        );

        $this->executeConsole(new RemoveCacheCommand());

        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_default/images/cats.jpeg');
    }

    public function testShouldRemoveCacheBySinglePath()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg',
            'anImageContent2'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats2.jpeg',
            'anImageContent2'
        );

        $this->executeConsole(
            new RemoveCacheCommand(),
            array('paths' => array('images/cats.jpeg'))
        );

        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_default/images/cats.jpeg');
        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg');
        $this->assertFileExists($this->cacheRoot.'/thumbnail_default/images/cats2.jpeg');
    }

    public function testShouldRemoveCacheByMultiplePaths()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg',
            'anImageContent2'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats2.jpeg',
            'anImageContent2'
        );

        $this->executeConsole(
            new RemoveCacheCommand(),
            array('paths' => array('images/cats.jpeg', 'images/cats2.jpeg'))
        );

        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_default/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_default/images/cats2.jpeg');
    }

    public function testShouldRemoveCacheBySingleFilter()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg',
            'anImageContent2'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats2.jpeg',
            'anImageContent2'
        );

        $this->executeConsole(
            new RemoveCacheCommand(),
            array('--filters' => array('thumbnail_default'))
        );

        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_default/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_default/images/cats2.jpeg');
        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg');
    }

    public function testShouldRemoveCacheByMultipleFilters()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg',
            'anImageContent2'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats2.jpeg',
            'anImageContent2'
        );

        $this->executeConsole(
            new RemoveCacheCommand(),
            array('--filters' => array('thumbnail_default', 'thumbnail_web_path'))
        );

        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_default/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_default/images/cats2.jpeg');
    }

    public function testShouldRemoveCacheByOnePathAndMultipleFilters()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg',
            'anImageContent2'
        );

        $this->executeConsole(
            new RemoveCacheCommand(),
            array(
                'paths' => array('images/cats.jpeg'),
                '--filters' => array('thumbnail_default', 'thumbnail_web_path'), )
        );

        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_default/images/cats.jpeg');
        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg');
    }

    public function testShouldRemoveCacheByMultiplePathsAndSingleFilter()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_default/images/cats.jpeg',
            'anImageContent'
        );
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg',
            'anImageContent2'
        );

        $this->executeConsole(
            new RemoveCacheCommand(),
            array(
                'paths' => array('images/cats.jpeg', 'images/cats2.jpeg'),
                '--filters' => array('thumbnail_web_path'), )
        );

        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats2.jpeg');
        $this->assertFileExists($this->cacheRoot.'/thumbnail_default/images/cats.jpeg');
    }

    /**
     * Helper function return the result of command execution.
     *
     * @param Command $command
     * @param array   $arguments
     * @param array   $options
     *
     * @return string
     */
    protected function executeConsole(Command $command, array $arguments = array(), array $options = array())
    {
        $command->setApplication(new Application($this->createClient()->getKernel()));
        if ($command instanceof ContainerAwareCommand) {
            $command->setContainer($this->createClient()->getContainer());
        }

        $arguments = array_replace(array('command' => $command->getName()), $arguments);
        $options = array_replace(array('--env' => 'test'), $options);

        $commandTester = new CommandTester($command);
        $commandTester->execute($arguments, $options);

        return $commandTester->getDisplay();
    }
}
