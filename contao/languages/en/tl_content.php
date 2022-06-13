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

$GLOBALS['TL_LANG']['tl_content']['erecht24_legend'] = 'eRecht24';
$GLOBALS['TL_LANG']['tl_content']['er24Type'] = ['Legal text type', 'The type of legal text to be fetched from the eRecht24 API.'];
$GLOBALS['TL_LANG']['tl_content']['er24TextTypes'] = [
    LegalText::TEXT_TYPE_IMPRINT => 'Imprint',
    LegalText::TEXT_TYPE_PRIVACY_POLICY => 'Privacy policy',
    LegalText::TEXT_TYPE_PRIVACY_POLICY_SOCIAL_MEDIA => 'Privacy policy for social media',
];
$GLOBALS['TL_LANG']['tl_content']['er24Html'] = ['Legal text fallback storage', 'Stores the last fetched version of the legal text in the database.'];
