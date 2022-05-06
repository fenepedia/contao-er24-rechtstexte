<?php

declare(strict_types=1);

/*
 * This file is part of the Contao eRecht24 Rechtstexte extension.
 *
 * (c) inspiredminds
 * (c) Christian Feneberg
 *
 * @license LGPL-3.0-or-later
 */

namespace Fenepedia\ContaoErecht24Rechtstexte\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Contao\ManagerPlugin\Routing\RoutingPluginInterface;
use Fenepedia\ContaoErecht24Rechtstexte\ContaoErecht24RechtstexteBundle;
use Symfony\Component\Config\Loader\LoaderResolverInterface;
use Symfony\Component\HttpKernel\KernelInterface;

class Plugin implements BundlePluginInterface, RoutingPluginInterface
{
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(ContaoErecht24RechtstexteBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }

    public function getRouteCollection(LoaderResolverInterface $resolver, KernelInterface $kernel)
    {
        $file = __DIR__.'/../../config/routes.yaml';

        return $resolver->resolve($file)->load($file);
    }
}
