<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Rechtstexte für eRecht24 extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_DCA']['tl_page']['fields']['er24ApiKey'] = [
    'inputType' => 'text',
    'exclude' => true,
    'eval' => ['maxlength' => 128, 'tl_class' => 'w50', 'rgxp' => 'alnum'],
    'sql' => ['type' => 'string', 'length' => 128, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['er24Secret'] = [
    'inputType' => 'text',
    'exclude' => true,
    'eval' => ['maxlength' => 255, 'tl_class' => 'w50 clr', 'readonly' => true],
    'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
];

$GLOBALS['TL_DCA']['tl_page']['fields']['er24ClientId'] = [
    'inputType' => 'text',
    'exclude' => true,
    'eval' => ['maxlength' => 64, 'tl_class' => 'w50', 'readonly' => true],
    'sql' => ['type' => 'string', 'length' => 64, 'default' => ''],
];

PaletteManipulator::create()
    ->addLegend('erecht24_legend', 'publish_legend', PaletteManipulator::POSITION_BEFORE, true)
    ->addField('er24ApiKey', 'erecht24_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('er24Secret', 'erecht24_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('er24ClientId', 'erecht24_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('rootfallback', 'tl_page')
;
