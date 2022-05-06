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

namespace Fenepedia\ContaoErecht24Rechtstexte\Controller\ContentElement;

use Contao\ArticleModel;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\PageModel;
use Contao\Template;
use eRecht24\RechtstexteSDK\LegalTextHandler;
use Fenepedia\ContaoErecht24Rechtstexte\ContaoErecht24RechtstexteBundle;
use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Fetches the selected legal text from the eRecht24 API, puts them into the cache and displays the text.
 * 
 * @ContentElement(type=LegalTextElementController::TYPE, category="includes")
 */
class LegalTextElementController extends AbstractContentElementController
{
    public const TYPE = 'er24_legal_text';

    private $cache;

    public function __construct(FilesystemTagAwareAdapter $legalTextCache)
    {
        $this->cache = $legalTextCache;
    }

    protected function getResponse(Template $template, ContentModel $model, Request $request): ?Response
    {
        $page = $this->getPageModel();

        // For the back end
        if (null === $page && 'tl_article' === $model->ptable) {
            $page = PageModel::findByPk(ArticleModel::findById($model->pid)->pid);
        }

        if (null === $page) {
            return new Response('');
        }

        $page->loadDetails();

        $cacheKey = implode('.', ['legaltext', $page->rootId, $model->er24Type]);
        $tags = ['er24_legaltext', 'er24_legaltext_'.$model->er24Type.'_root'.$page->rootId];

        $cacheItem = $this->cache->getItem($cacheKey);

        if (!$cacheItem->isHit()) {
            if (empty($page->er24ApiKey)) {
                return new Response('');
            }

            $handler = new LegalTextHandler($page->er24ApiKey, $model->er24Type, ContaoErecht24RechtstexteBundle::PLUGIN_KEY);
            $document = $handler->importDocument();

            if (null === $document) {
                return new Response('');
            }

            $cacheItem->set($document->getHtml('de' === $page->language ? 'de' : 'en'));
            $cacheItem->tag($tags);

            $this->cache->save($cacheItem);
        }

        $this->tagResponse($tags);

        $template->document = $cacheItem->get();

        return new Response($template->parse());
    }
}
