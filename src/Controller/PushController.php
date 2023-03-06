<?php

declare(strict_types=1);

/*
 * This file is part of the Contao Rechtstexte für eRecht24 extension.
 *
 * (c) fenepedia
 *
 * @license LGPL-3.0-or-later
 */

namespace Fenepedia\ContaoErecht24Rechtstexte\Controller;

use Doctrine\DBAL\Connection;
use eRecht24\RechtstexteSDK\Helper\Helper;
use FOS\HttpCacheBundle\CacheManager;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

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

    public function __construct(Connection $db, AdapterInterface $legalTextCache, CacheManager $httpCacheManager)
    {
        $this->db = $db;
        $this->legalTextCache = $legalTextCache;
        $this->httpCacheManager = $httpCacheManager;
    }

    public function __invoke(Request $request): JsonResponse
    {
        $json = json_decode($request->getContent(), true);

        if (false === $json) {
            return new JsonResponse(['message' => 'Invalid request body.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $secret = $json[Helper::ERECHT24_PUSH_PARAM_SECRET];
        $type = $json[Helper::ERECHT24_PUSH_PARAM_TYPE];

        if (empty($secret)) {
            return new JsonResponse(['message' => 'No secret given.'], Response::HTTP_UNAUTHORIZED);
        }

        $rootPage = $this->db->fetchAssociative("SELECT * FROM tl_page WHERE type = 'root' AND er24ApiKey != '' AND er24Secret = ? AND er24ClientId != '' LIMIT 1", [$secret]);

        if (false === $rootPage) {
            return new JsonResponse(['message' => 'Invalid secret.'], Response::HTTP_UNAUTHORIZED);
        }

        if (empty($type) || !Helper::isValidPushType($type)) {
            return new JsonResponse(['message' => 'Invalid type.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if (Helper::PUSH_TYPE_PING === $type) {
            $pingResponse = Helper::getPingResponse();

            return new JsonResponse($pingResponse, $pingResponse['code']);
        }

        $tag = 'er24_legaltext_'.$type.'_root'.$rootPage['id'];

        if ($this->legalTextCache instanceof TagAwareCacheInterface) {
            $this->legalTextCache->invalidateTags([$tag]);
        }

        $this->httpCacheManager->invalidateTags([$tag]);

        return new JsonResponse(['message' => 'OK'], Response::HTTP_OK);
    }
}
