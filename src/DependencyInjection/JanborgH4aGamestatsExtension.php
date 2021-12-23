<?php

declare(strict_types=1);

/*
 * This file is part of contao-h4a_gamestats.
 *
 * (c) Jan Lünborg
 *
 * @license MIT
 */

namespace Janborg\H4aGamestats\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class JanborgH4aGamestatsExtension extends Extension
{
    /**
     * Loads configuration.
     *
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $fileLocator = new FileLocator(__DIR__.'/../Resources/config');
        $loader = new YamlFileLoader($container, $fileLocator);

        $loader->load('commands.yml');
        $loader->load('services.yml');
    }
}
