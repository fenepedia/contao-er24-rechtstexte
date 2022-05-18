<?php

declare(strict_types=1);

/*
 * This file is part of the Contao eRecht24 Rechtstexte extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

use eRecht24\RechtstexteSDK\Model\LegalText;

$GLOBALS['TL_LANG']['tl_content']['erecht24_legend'] = 'eRecht24';
$GLOBALS['TL_LANG']['tl_content']['er24Type'] = ['Rechtstext-Typ', 'Der Typ des Rechtstext welcher von der eRecht24 API geholt werden soll.'];
$GLOBALS['TL_LANG']['tl_content']['er24TextTypes'] = [
    LegalText::TEXT_TYPE_IMPRINT => 'Impressum',
    LegalText::TEXT_TYPE_PRIVACY_POLICY => 'Datenschutzerklärung',
    LegalText::TEXT_TYPE_PRIVACY_POLICY_SOCIAL_MEDIA => 'Datenschutzerklärung für Social-Media',
];
$GLOBALS['TL_LANG']['tl_content']['er24Html'] = ['Rechtstext-Fallback Speicher', 'Speichert die zuletzt verfügbare Version des Textes in der Datenbank.'];
