<?php

declare(strict_types=1);

/*
 * This file is part of the Contao eRecht24 Rechtstexte extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

namespace Fenepedia\ContaoErecht24Rechtstexte\Controller\ContentElement;

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\PageModel;
use Contao\Template;
use eRecht24\RechtstexteSDK\Helper\Helper;
use eRecht24\RechtstexteSDK\LegalTextHandler;
use eRecht24\RechtstexteSDK\Model\LegalText;
use Fenepedia\ContaoErecht24Rechtstexte\ContaoErecht24RechtstexteBundle;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\Adapter\TagAwareAdapterInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Fetches the selected legal text from the eRecht24 API, puts them into the cache and displays the text.
 *
 * @ContentElement(type=LegalTextElementController::TYPE, category="includes")
 */
class LegalTextElementController extends AbstractContentElementController
{
    public const TYPE = 'er24_legal_text';

    private static $pushTypeMap = [
        LegalText::TEXT_TYPE_IMPRINT => Helper::PUSH_TYPE_IMPRINT,
        LegalText::TEXT_TYPE_PRIVACY_POLICY => Helper::PUSH_TYPE_PRIVACY_POLICY,
        LegalText::TEXT_TYPE_PRIVACY_POLICY_SOCIAL_MEDIA => Helper::PUSH_TYPE_PRIVACY_POLICY_SOCIAL_MEDIA,
    ];

    private $cache;
    private $translator;
    private $scopeMatcher;

    public function __construct(AdapterInterface $legalTextCache, TranslatorInterface $translator, ScopeMatcher $scopeMatcher)
    {
        $this->cache = $legalTextCache;
        $this->translator = $translator;
        $this->scopeMatcher = $scopeMatcher;
    }

    protected function getResponse(Template $template, ContentModel $model, Request $request): ?Response
    {
        $page = $this->getPageModel();

        // For the back end
        if (null === $page && 'tl_article' === $model->ptable) {
            $page = PageModel::findByPk(ArticleModel::findById($model->pid)->pid);
        }

        if (null === $page || !isset(self::$pushTypeMap[$model->er24Type])) {
            return new Response();
        }

        $page->loadDetails();

        $pushType = self::$pushTypeMap[$model->er24Type];
        $cacheKey = implode('.', ['legaltext', $page->rootId, $model->er24Type]);
        $tags = ['er24_legaltext', 'er24_legaltext_'.$pushType.'_root'.$page->rootId];

        $cacheItem = $this->cache->getItem($cacheKey);

        if (!$cacheItem->isHit()) {
            if (empty($page->er24ApiKey)) {
                return new Response();
            }

            $handler = new LegalTextHandler($page->er24ApiKey, $model->er24Type, ContaoErecht24RechtstexteBundle::PLUGIN_KEY);
            $document = $handler->importDocument();

            if (null === $document) {
                return new Response();
            }

            $cacheItem->set($document->getHtml('de' === substr($page->language, 0, 2) ? 'de' : 'en'));

            if ($this->cache instanceof TagAwareAdapterInterface) {
                $cacheItem->tag($tags);
            }

            $this->cache->save($cacheItem);
        }

        $this->tagResponse($tags);

        $html = $cacheItem->get();

        if (empty($html)) {
            if ($this->scopeMatcher->isBackendRequest($request)) {
                return new Response($this->translator->trans('data_not_available', ['%type%' => $model->er24Type], 'ContaoErecht24Rechtstexte'));
            }

            return new Response();
        }

        $template->document = $html;

        return new Response($template->parse());
    }
}
