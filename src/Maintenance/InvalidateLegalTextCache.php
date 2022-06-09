<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Rechtstexte für eRecht24 extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

namespace Fenepedia\ContaoErecht24Rechtstexte\Maintenance;

use FOS\HttpCacheBundle\CacheManager;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;

/**
 * Purges the cache for the legal texts from within the back end.
 */
class InvalidateLegalTextCache
{
    private $cacheManager;
    private $legalTextCache;

    public function __construct(CacheManager $cacheManager, AdapterInterface $legalTextCache)
    {
        $this->cacheManager = $cacheManager;
        $this->legalTextCache = $legalTextCache;
    }

    public function __invoke(): void
    {
        $this->cacheManager->invalidateTags(['er24_legaltext']);

        if ($this->legalTextCache instanceof TagAwareAdapterInterface) {
            $this->legalTextCache->invalidateTags(['er24_legaltext']);
        }
    }
}
