<?php

/*
 * This file is part of the desarrolla2 download bundle package
 *
 * Copyright (c) 2017-2018 Daniel González
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author Daniel González <daniel@desarrolla2.com>
 */

namespace SheGroup\DownloadBundle\Command;

use SheGroup\DownloadBundle\Handler\DatabaseHandler;
use SheGroup\DownloadBundle\Handler\DirectoryHandler;
use SheGroup\DownloadBundle\Model\Directory;
use Desarrolla2\Timer\Formatter\Human;
use Desarrolla2\Timer\Timer;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Symfony\Component\Finder\Finder;

abstract class AbstractCommand extends Command implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    /** @var Timer */
    protected $timer;

    /** @var OutputInterface */
    protected $output;

    /**
     * @param array $configuration
     */
    protected function finalize(OutputInterface $output)
    {
        /** @var DatabaseHandler $databaseHandler */
        $databaseHandler = $this->container->get('shegroup.download.handler.database_handler');
        /** @var DirectoryHandler $handler */
        $directoryHandler = $this->container->get('shegroup.download.handler.directory_handler');
        $info = [['database size', $this->formatSize($databaseHandler->getFileSize())],];

        $directory = new Directory('', $databaseHandler->getDirectory());
        $info[] = [
            sprintf('dir %s size:', $this->truncateDirectoryName($directory->getLocal())),
            $this->formatSize($directoryHandler->getLocalSize($directory)),
        ];
        $directories = $directoryHandler->getDirectories();
        foreach ($directories as $directory) {
            $info[] = [
                sprintf('dir %s size:', $this->truncateDirectoryName($directory->getLocal())),
                $this->formatSize($directoryHandler->getLocalSize($directory)),
            ];
        }

        $table = new Table($output);
        $table->setHeaders(['name', 'value']);
        $table->setRows($info);
        $table->render();
    }

    /**
     * @param int $size
     * @return string
     */
    protected function formatSize(int $size)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
        $power = $size > 0 ? floor(log($size, 1024)) : 0;

        return number_format($size / pow(1024, $power), 2, '.', ',').' '.$units[$power];
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        parent::initialize($input, $output);
        $this->timer = new Timer(new Human());
        $this->output = $output;
    }

    /**
     * @param string $directoryName
     * @return string
     */
    private function truncateDirectoryName(string $directoryName): string
    {
        $maxLenght = 20;
        if (strlen($directoryName) > $maxLenght) {
            return sprintf('..%s', substr($directoryName, 2 - $maxLenght));
        }

        return $directoryName;
    }
}
