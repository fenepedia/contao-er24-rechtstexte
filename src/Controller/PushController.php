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

namespace Fenepedia\ContaoErecht24Rechtstexte\Controller;

use Doctrine\DBAL\Connection;
use eRecht24\RechtstexteSDK\Helper\Helper;
use FOS\HttpCacheBundle\CacheManager;
use Symfony\Component\Cache\Adapter\FilesystemTagAwareAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Handles push requests from eRecht24 and invalidates the appropriate cached legal texts.
 * 
 * @Route("/_er24/push", name=PushController::class, methods={"POST"}, defaults={"_token_check": false})
 */
class PushController
{
    private $db;
    private $legalTextCache;
    private $httpCacheManager;

    public function __construct(Connection $db, FilesystemTagAwareAdapter $legalTextCache, CacheManager $httpCacheManager)
    {
        $this->db = $db;
        $this->legalTextCache = $legalTextCache;
        $this->httpCacheManager = $httpCacheManager;
    }

    public function __invoke(Request $request): Response
    {
        $secret = $request->request->get(Helper::ERECHT24_PUSH_PARAM_SECRET);
        $type = $request->request->get(Helper::ERECHT24_PUSH_PARAM_TYPE);

        if (empty($secret) || empty($type) || !Helper::isValidPushType($type)) {
            throw new BadRequestHttpException('Invalid secret or type.');
        }

        $rootPage = $this->db->fetchAssociative("SELECT * FROM tl_page WHERE type = 'root' AND er24ApiKey != '' AND er24Secret = ? AND er24ClientId != '' LIMIT 1", [$secret]);

        if (false === $rootPage) {
            throw new NotFoundHttpException('Invalid secret.');
        }

        $tag = 'er24_legaltext_'.$type.'_root'.$rootPage['id'];

        $this->legalTextCache->invalidateTags([$tag]);
        $this->httpCacheManager->invalidateTags([$tag]);

        return new Response('', Response::HTTP_NO_CONTENT);
    }
}
