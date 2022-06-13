<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Rechtstexte für eRecht24 extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

namespace Fenepedia\ContaoErecht24Rechtstexte\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContaoErecht24RechtstexteExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        (new YamlFileLoader($container, new FileLocator(__DIR__.'/../../config')))->load('services.yaml');
    }
}
