<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Rechtstexte für eRecht24 extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

use eRecht24\RechtstexteSDK\Model\LegalText;
use Fenepedia\ContaoErecht24Rechtstexte\Controller\ContentElement\LegalTextElementController;

$GLOBALS['TL_DCA']['tl_content']['fields']['er24Type'] = [
    'exclude' => true,
    'inputType' => 'radio',
    'eval' => ['mandatory' => true, 'tl_class' => 'clr'],
    'options' => [
        LegalText::TEXT_TYPE_IMPRINT,
        LegalText::TEXT_TYPE_PRIVACY_POLICY,
        LegalText::TEXT_TYPE_PRIVACY_POLICY_SOCIAL_MEDIA,
    ],
    'reference' => &$GLOBALS['TL_LANG']['tl_content']['er24TextTypes'],
    'sql' => ['type' => 'string', 'length' => 32, 'default' => '', 'customSchemaOptions' => ['collation' => 'ascii_bin']],
];

$GLOBALS['TL_DCA']['tl_content']['fields']['er24Html'] = [
    'sql' => ['type' => 'blob', 'notnull' => false],
];

$GLOBALS['TL_DCA']['tl_content']['palettes'][LegalTextElementController::TYPE] =
    '{type_legend},type;{erecht24_legend},er24Type;{template_legend:hide},customTpl;{protected_legend:hide},protected;{expert_legend:hide},guests,cssID;{invisible_legend:hide},invisible,start,stop'
;
