<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Rechtstexte für eRecht24 extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

namespace Fenepedia\ContaoErecht24Rechtstexte\Controller\ContentElement;

use Contao\ArticleModel;
use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\FrontendTemplate;
use Contao\PageModel;
use Contao\Template;
use Doctrine\DBAL\Connection;
use eRecht24\RechtstexteSDK\Exceptions\Exception;
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
 * @ContentElement(type=LegalTextElementController::TYPE, category="includes", template="ce_er24_legal_text")
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
    private $db;
    private $lastErecht24Error;

    public function __construct(AdapterInterface $legalTextCache, TranslatorInterface $translator, ScopeMatcher $scopeMatcher, Connection $db)
    {
        $this->cache = $legalTextCache;
        $this->translator = $translator;
        $this->scopeMatcher = $scopeMatcher;
        $this->db = $db;
    }

    protected function getResponse(Template $template, ContentModel $model, Request $request): Response
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
        $tags = ['er24_legaltext', 'er24_legaltext_'.$pushType.'_root'.$page->rootId];

        // Fetch the HTML content of the legal text (live, cached or database)
        $html = $this->getHtml($page, $model, $tags);

        // Tag this response for the HTTP cache
        $this->tagResponse($tags);

        if (empty($html)) {
            // Show a message in the back end if no data for this legal text is available
            if ($this->scopeMatcher->isBackendRequest($request)) {
                if($this->lastErecht24Error) {
                    $errorTemplate = new BackendTemplate('be_er24_error');
                    $errorTemplate->message = $this->lastErecht24Error;
                    return $errorTemplate->getResponse();
                } else {
                    return new Response($this->translator->trans('data_not_available', ['%type%' => $model->er24Type], 'ContaoErecht24Rechtstexte'));
                }
            }

            return new Response();
        }

        $template->document = $html;

        return $template->getResponse();
    }

    private function getHtml(PageModel $page, ContentModel $model, array $tags): ?string
    {
        $this->lastErecht24Error = null;
        $cacheKey = implode('.', ['legaltext', $page->rootId, $model->er24Type]);

        // Retrieve cached item
        $cacheItem = $this->cache->getItem($cacheKey);

        if (!$cacheItem->isHit() && !empty($page->er24ApiKey)) {
            $handler = new LegalTextHandler($page->er24ApiKey, $model->er24Type, ContaoErecht24RechtstexteBundle::PLUGIN_KEY);
            $document = $handler->importDocument();
            $language = strtolower(substr($page->language, 0, 2));

            // Fetch the HTML content of the legal text
            if (null !== $document && null !== ($html = $document->getHtml($language))) {
                // replace emails with contao spambot safe links
                // try to get it working with not normalized domain names
                // please use idn syntax: https://de.wikipedia.org/wiki/Internationalisierter_Domainname
                $mailRegex = '/((?:[^\x00-\x20\x22\x40\x7F]{1,64}|\x22[^\x00-\x1F\x7F]{1,64}?\x22)@(?:\[(?:IPv)?[a-f0-9.:]{1,47}]|[\w.-]{1,252}\.[a-z]{2,63}\b))/u';
                $html = preg_replace($mailRegex, "{{email::$1}}", $html);
                
                // Store in cache item
                $cacheItem->set($html);

                // Tag the cache item
                if ($this->cache instanceof TagAwareAdapterInterface) {
                    $cacheItem->tag($tags);
                }

                // Store cache item in cache storage
                $this->cache->save($cacheItem);

                // Store non-empty version in database perpetually
                if (!empty($html)) {
                    $this->db->update('tl_content', ['er24Html' => $html], ['id' => (int) $model->id]);
                }
            } else {
                $this->lastErecht24Error = $handler->getLastErrorMessage($language);
            }
        }

        // Return either the cached item or the database fallback
        return $cacheItem->get() ?: $model->er24Html;
    }
}
