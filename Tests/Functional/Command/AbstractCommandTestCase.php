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
}
