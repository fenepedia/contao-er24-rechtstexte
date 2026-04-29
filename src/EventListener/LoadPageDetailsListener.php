<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Rechtstexte für eRecht24 extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

namespace Fenepedia\ContaoErecht24Rechtstexte\EventListener;

use Contao\CoreBundle\DependencyInjection\Attribute\AsHook;
use Contao\PageModel;
use Doctrine\DBAL\Connection;

/**
 * Adds the eRecht24 API key to the page details.
 */
#[AsHook('loadPageDetails')]
class LoadPageDetailsListener
{
    /**
     * @var array<string, string>
     */
    private static array|null $apiKeys = null;

    public function __construct(private readonly Connection $db)
    {
    }

    public function __invoke(array $parents, PageModel $page): void
    {
        if (null === self::$apiKeys) {
            self::$apiKeys = $this->db->fetchAllKeyValue("SELECT dns, er24ApiKey FROM tl_page WHERE type = 'root' AND fallback = 1");
        }

        $page->er24ApiKey = self::$apiKeys[$page->domain] ?? '';
    }
}
