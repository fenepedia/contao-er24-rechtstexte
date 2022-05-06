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

namespace Fenepedia\ContaoErecht24Rechtstexte\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\PageModel;

/**
 * Adds the eRecht24 API key to the page details.
 * 
 * @Hook("loadPageDetails")
 */
class LoadPageDetailsListener
{
    public function __invoke(array $parents, PageModel $page): void
    {
        if (empty($parents)) {
            return;
        }

        $root = end($parents);

        $page->er24ApiKey = $root->er24ApiKey;
    }
}
