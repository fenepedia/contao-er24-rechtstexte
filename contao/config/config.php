<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Rechtstexte für eRecht24 extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

use Fenepedia\ContaoErecht24Rechtstexte\Maintenance\InvalidateLegalTextCache;

$GLOBALS['TL_PURGE']['custom']['er24_legaltext'] = [
    'callback' => [InvalidateLegalTextCache::class, '__invoke'],
];
