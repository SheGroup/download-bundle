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

namespace Desarrolla2\DownloadBundle\Handler;

use Desarrolla2\DownloadBundle\Model\Database;

class DatabaseHandler extends AbstractHandler
{
    /** @var Database */
    private $remote;

    /** @var Database */
    private $local;

    /** @var string */
    private $directory;

    /** @var array */
    private $onlyStructureTables;

    public function __construct(
        string $user,
        string $host,
        Database $remote,
        Database $local,
        string $directory,
        array $onlyStructureTables = []
    ) {
        $this->user = $user;
        $this->host = $host;
        $this->remote = $remote;
        $this->local = $local;
        $this->directory = $directory;
        $this->onlyStructureTables = $onlyStructureTables;
    }

    public function download()
    {
        $databaseFile = $this->getFileName();
        $databaseFileWithTime = $this->getFileNameWithDateTime();
        $temporalFile = $this->getTemporalFileName();
        $sql = sprintf(
            'mysqldump -h%s -u%s -p\'%s\' --port %s --single-transaction --create-options --databases %s %s > %s',
            $this->remote->getHost(),
            $this->remote->getUser(),
            $this->remote->getPassword(),
            $this->remote->getPort(),
            $this->remote->getName(),
            $this->getSkipTables(),
            $temporalFile
        );
        $this->remote($sql);

        if (count($this->onlyStructureTables)) {
            $sql = sprintf(
                'mysqldump -h%s -u%s -p\'%s\' --port %s --create-options %s %s --no-data >> %s',
                $this->remote->getHost(),
                $this->remote->getUser(),
                $this->remote->getPassword(),
                $this->remote->getPort(),
                $this->remote->getName(),
                implode(' ', $this->onlyStructureTables),
                $temporalFile
            );
            $this->remote($sql);
        }

        $this->local(sprintf('scp %s@%s:%s %s', $this->user, $this->host, $temporalFile, $databaseFileWithTime));
        $this->local(sprintf('cp %s %s', $databaseFileWithTime, $databaseFile));
        $this->remote(sprintf('rm %s', $temporalFile));

        if ($this->remote->getName() == $this->local->getName()) {
            return;
        }
        $this->local(
            sprintf(
                'sed \'s/%s/%s/g\' %s > %s',
                $this->remote->getName(),
                $this->local->getName(),
                $databaseFile,
                $temporalFile
            )
        );

        $this->local(sprintf('mv %s %s', $temporalFile, $databaseFile));
    }

    public function getDateTimeFormat(): string
    {
        return 'Ymd_His';
    }

    public function getDirectory(): string
    {
        if (!is_dir($this->directory)) {
            $this->local(sprintf('mkdir -p %s', $this->directory));
        }

        return $this->directory;
    }

    public function getFileName(): string
    {
        $directory = $this->getDirectory();

        return sprintf('%s/current.sql', $directory);
    }

    public function getFileSize(): int
    {
        return filesize($this->getFileName());
    }

    public function load()
    {
        $this->local(
            sprintf(
                'mysql -h %s -u %s -p\'%s\' --port %s -e \'DROP DATABASE IF EXISTS %s;\'',
                $this->local->getHost(),
                $this->local->getUser(),
                $this->local->getPassword(),
                $this->local->getPort(),
                $this->local->getName()
            )
        );

        $this->local(
            sprintf(
                'mysql -h %s -u %s -p\'%s\' --port %s < %s',
                $this->local->getHost(),
                $this->local->getUser(),
                $this->local->getPassword(),
                $this->local->getPort(),
                $this->getFileName()
            )
        );
    }

    private function getFileNameWithDateTime(): string
    {
        $directory = $this->getDirectory();

        return sprintf('%s/%s.sql', $directory, (new \DateTime())->format($this->getDateTimeFormat()));
    }

    private function getSkipTables(): string
    {
        $sql = '';
        foreach ($this->onlyStructureTables as $table) {
            $sql .= sprintf(' --ignore-table=%s.%s', $this->remote->getName(), $table);
        }

        return trim($sql);
    }

    private function getTemporalFileName(): string
    {
        return sprintf('/tmp/%s.sql', uniqid('downloader_', true));
    }
}