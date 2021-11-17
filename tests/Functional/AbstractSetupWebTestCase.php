<?php

/*
 * This file is part of the `liip/LiipImagineBundle` project.
 *
 * (c) https://github.com/liip/LiipImagineBundle/graphs/contributors
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace Liip\ImagineBundle\Tests\Functional;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @coversNothing
 */
class AbstractSetupWebTestCase extends AbstractWebTestCase
{
    protected KernelBrowser $client;

    protected Filesystem $filesystem;

    protected string $webRoot;

    protected string $cacheRoot;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = $this->createClient();
        $this->client->catchExceptions(false);
        $this->webRoot = sprintf('%s/public', self::$kernel->getContainer()->getParameter('kernel.project_dir'));
        $this->cacheRoot = $this->webRoot.'/media/cache';
        $this->filesystem = new Filesystem();
        $this->filesystem->remove($this->cacheRoot);
    }
}
