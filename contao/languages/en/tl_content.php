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

use eRecht24\RechtstexteSDK\Model\LegalText;

$GLOBALS['TL_LANG']['tl_content']['erecht24_legend'] = 'eRecht24';
$GLOBALS['TL_LANG']['tl_content']['er24Type'] = ['Legal Text Type', 'The type of legal text to be fetched from the eRecht24 API.'];
$GLOBALS['TL_LANG']['tl_content']['er24TextTypes'] = [
    LegalText::TEXT_TYPE_IMPRINT => 'Imprint',
    LegalText::TEXT_TYPE_PRIVACY_POLICY => 'Privacy policy',
    LegalText::TEXT_TYPE_PRIVACY_POLICY_SOCIAL_MEDIA => 'Privacy policy for social media',
];
