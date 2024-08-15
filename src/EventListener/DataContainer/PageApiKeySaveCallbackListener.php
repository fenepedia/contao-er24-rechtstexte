<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Rechtstexte für eRecht24 extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

namespace Fenepedia\ContaoErecht24Rechtstexte\EventListener\DataContainer;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\CoreBundle\DependencyInjection\Attribute\AsCallback;
use Contao\DataContainer;
use Contao\PageModel;
use eRecht24\RechtstexteSDK\ApiHandler;
use eRecht24\RechtstexteSDK\Model\Client;
use Fenepedia\ContaoErecht24Rechtstexte\ContaoErecht24RechtstexteBundle;
use Fenepedia\ContaoErecht24Rechtstexte\Controller\PushController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Registers or updates an eRecht24 push client via the API key on save.
 */
#[AsCallback('tl_page', 'fields.er24ApiKey.save')]
class PageApiKeySaveCallbackListener
{
    private $requestStack;
    private $urlGenerator;

    public function __construct(RequestStack $requestStack, UrlGeneratorInterface $urlGenerator)
    {
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
    }

    public function __invoke(string $apiKey, DataContainer $dc): string
    {
        if (empty($apiKey)) {
            return $apiKey;
        }

        $page = PageModel::findById($dc->id);
        $apiHandler = new ApiHandler($apiKey, ContaoErecht24RechtstexteBundle::PLUGIN_KEY);

        $client = (new Client())
            ->setPushUri($this->urlGenerator->generate(PushController::class, [], UrlGeneratorInterface::ABSOLUTE_URL))
            ->setPushMethod('POST')
            ->setCms('Contao')
            ->setCmsVersion(ContaoCoreBundle::getVersion())
            ->setPluginName('fenepedia/contao-er24-rechtstexte')
        ;

        if (!empty($page->er24ClientId)) {
            $client->setClientId((int) $page->er24ClientId);
            $apiHandler->updateClient($client);

            if ($apiHandler->isLastResponseSuccess()) {
                $page->er24Secret = $client->getSecret();
                $page->save();

                return $apiKey;
            }
        }

        $newClient = $apiHandler->createClient($client);

        if ($apiHandler->isLastResponseSuccess()) {
            $page->er24Secret = $newClient->getSecret();
            $page->er24ClientId = $newClient->getClientId();
            $page->save();
        } else {
            $request = $this->requestStack->getCurrentRequest();
            $lang = 'de' === $request->getLocale() ? 'de' : 'en';
            $error = $apiHandler->getLastErrorMessage($lang);
            throw new \Exception($error);
        }

        return $apiKey;
    }
}
