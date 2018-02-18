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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @coversNothing
 */
class AbstractCommandTestCase extends AbstractSetupWebTestCase
{
    /**
     * @param Command $command
     * @param array   $arguments
     * @param array   $options
     *
     * @return string
     */
    protected function executeConsole(Command $command, array $arguments = [], array $options = [])
    {
        $options = array_replace(['--env' => 'test'], $options);

        $commandTester = new CommandTester($command);
        $commandTester->execute($arguments, $options);

        return $commandTester->getDisplay();
    }
}
