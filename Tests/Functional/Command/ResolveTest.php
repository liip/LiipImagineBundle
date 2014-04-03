<?php

namespace Liip\ImagineBundle\Tests\Functional\Command;

use Liip\ImagineBundle\Tests\Functional\WebTestCase;
use Liip\ImagineBundle\Command\ResolveCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ResolveTest extends WebTestCase
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
        $this->cacheRoot = $this->webRoot.'/'.self::$kernel->getContainer()->getParameter('liip_imagine.cache_prefix');

        $this->filesystem = new Filesystem;
        $this->filesystem->remove($this->cacheRoot);
    }


    public function testShouldResolveWithEmptyCache()
    {
        $this->assertFileNotExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');

        $output = $this->executeConsole(new ResolveCommand(), array(
            'path' => 'images/cats.jpeg',
            'filters' => array('thumbnail_web_path')
        ));

        $this->assertFileExists($this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg');
        $this->assertContains('http://localhost/media/cache/thumbnail_web_path/images/cats.jpeg', $output);
    }

    public function testShouldResolveWithCacheExists()
    {
        $this->filesystem->dumpFile(
            $this->cacheRoot.'/thumbnail_web_path/images/cats.jpeg',
            'anImageContent'
        );

        $output = $this->executeConsole(new ResolveCommand(), array(
            'path' => 'images/cats.jpeg',
            'filters' => array('thumbnail_web_path')
        ));

        $this->assertContains('http://localhost/media/cache/thumbnail_web_path/images/cats.jpeg', $output);
    }

    public function testShouldResolveWithFewFiltersInParams()
    {
        $output = $this->executeConsole(new ResolveCommand(), array(
            'path'    => 'images/cats.jpeg',
            'filters' => array('thumbnail_web_path', 'thumbnail_default')
        ));

        $this->assertContains('http://localhost/media/cache/thumbnail_web_path/images/cats.jpeg', $output);
        $this->assertContains('http://localhost/media/cache/thumbnail_default/images/cats.jpeg', $output);
    }

    public function testShouldResolveWithoutFiltersInParams()
    {
        $output = $this->executeConsole(new ResolveCommand(), array(
            'path' => 'images/cats.jpeg',
        ));

        $this->assertContains('http://localhost/media/cache/thumbnail_web_path/images/cats.jpeg', $output);
        $this->assertContains('http://localhost/media/cache/thumbnail_default/images/cats.jpeg', $output);
    }

    /**
     * Helper function return the result of command execution.
     *
     * @param Command $command
     * @param array $arguments
     * @param array $options
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
