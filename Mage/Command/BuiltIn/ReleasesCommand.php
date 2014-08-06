<?php
/*
 * This file is part of the Magallanes package.
*
* (c) Andrés Montañez <andres@andresmontanez.com>
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Mage\Command\BuiltIn;

use Mage\Command\AbstractCommand;
use Mage\Command\RequiresEnvironment;
use Mage\Task\Factory;
use Mage\Console;

/**
 * Command for Managing the Releases
 *
 * @author Andrés Montañez <andres@andresmontanez.com>
 */
class ReleasesCommand extends AbstractCommand implements RequiresEnvironment
{
    /**
     * List the Releases, Rollback to a Release
     * @see \Mage\Command\AbstractCommand::run()
     */
    public function run()
    {
        $subCommand = $this->getConfig()->getArgument(1);

        // Run Tasks for Deployment
        $hosts = $this->getConfig()->getHosts();

        if (count($hosts) == 0) {
            Console::output(
                '<light_purple>Warning!</light_purple> <dark_gray>No hosts defined, unable to get releases.</dark_gray>',
                1, 3
            );

            return false;
        }

        foreach ($hosts as $host) {
            $this->getConfig()->setHost($host);

            switch ($subCommand) {
                case 'list':
                    $task = Factory::get('releases/list', $this->getConfig());
                    $task->init();
                    $result = $task->run();
                    break;

                case 'rollback':
                    if (!is_numeric($this->getConfig()->getParameter('release', ''))) {
                        Console::output('<red>Missing required releaseid.</red>', 1, 2);

                        return false;
                    }

                    $lockFile = getcwd() . '/.mage/' . $this->getConfig()->getEnvironment() . '.lock';
                    if (file_exists($lockFile)) {
                        Console::output('<red>This environment is locked!</red>', 1, 2);
                        echo file_get_contents($lockFile);

                        return false;
                    }

                    $releaseId = $this->getConfig()->getParameter('release', '');
                    $task = Factory::get('releases/rollback', $this->getConfig());
                    $task->init();
                    $task->setRelease($releaseId);
                    $result = $task->run();
                    break;
            }
        }

        return $result;
    }
}
