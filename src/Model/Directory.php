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

namespace SheGroup\DownloadBundle\Model;

class Directory
{
    /** @var string */
    private $remote;

    /** @var string */
    private $local;

    /** @var array */
    private $exclude;

    /**
     * @param string $remote
     * @param string $local
     */
    public function __construct(string $remote, string $local, array $exclude = [])
    {
        $this->remote = $remote;
        $this->local = $local;
        $this->exclude = $exclude;
    }

    /**
     * @return array
     */
    public function getExclude(): array
    {
        return $this->exclude;
    }

    /**
     * @return string
     */
    public function getLocal(): string
    {
        return $this->local;
    }

    /**
     * @return string
     */
    public function getRemote(): string
    {
        return $this->remote;
    }
}
