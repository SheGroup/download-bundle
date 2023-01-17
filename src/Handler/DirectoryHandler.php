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

namespace SheGroup\DownloadBundle\Handler;

use SheGroup\DownloadBundle\Model\Directory;

class DirectoryHandler extends AbstractHandler
{
    /** @var Directory[] */
    private $directories;

    /**
     * @param array $directories
     */
    public function __construct(string $user, string $host, int $port, array $directories)
    {
        $this->user = $user;
        $this->host = $host;
        $this->port = $port;
        $this->directories = $directories;
    }

    public function download()
    {
        foreach ($this->directories as $directory) {

            $exclude = '';
            foreach ($directory->getExclude() as $path) {
                $exclude .= sprintf('--exclude="%s" ', $path);
            }
            $this->local(
                sprintf(
                    'rsync -e "ssh -p %d" -rzad %s %s@%s:%s %s',
                    $this->port,
                    trim($exclude),
                    $this->user,
                    $this->host,
                    $directory->getRemote(),
                    $directory->getLocal()
                )
            );
        }
    }

    /**
     * @return Directory[]
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }

    /**
     * @param Directory $directory
     * @return int
     */
    public function getLocalSize(Directory $directory): int
    {

        return (int)$this->local(sprintf('du -s -B1 %s | awk \'{print $1}\'', $directory->getLocal()));
    }
}
