<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Rechtstexte für eRecht24 extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

namespace Fenepedia\ContaoErecht24Rechtstexte;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class ContaoErecht24RechtstexteBundle extends Bundle
{
    public const PLUGIN_KEY = 'hxsddw3ouZtcHT7WaE2W5urEyHvXV4g9ewPd7i4rY3CMN5iP9q3exHfkmhxLTgLo';

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
