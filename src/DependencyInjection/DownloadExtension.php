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

namespace SheGroup\DownloadBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * This is the class that loads and manages your bundle configuration.
 *
 * @link http://symfony.com/doc/current/cookbook/bundles/extension.html
 */
class DownloadExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('shegroup.download.user', $config['user']);
        $container->setParameter('shegroup.download.host', $config['host']);
        $container->setParameter('shegroup.download.port', $config['port']);
        $container->setParameter('shegroup.download.database.directory', $config['database']['directory']);
        $container->setParameter('shegroup.download.database.remote', $config['database']['remote']);
        $container->setParameter('shegroup.download.database.local', $config['database']['local']);
        $container->setParameter('shegroup.download.database.max_local_db', $config['database']['max_local_db']);
        $container->setParameter('shegroup.download.database.only_structure', $config['database']['only_structure']);
        $container->setParameter('shegroup.download.directories', $config['directories']);
        $container->setParameter('shegroup.download.timeout', 300);

        if ($config['timeout']) {
            $container->setParameter('shegroup.download.timeout', $config['timeout']);
        }

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}