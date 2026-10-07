<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Fractal\Transformer\PageFeedTransformer;
use Concrete\Core\Api\Resources;
use Concrete\Core\Page\Feed as PageFeedService;
use League\Fractal\Resource\Collection;

defined('C5_EXECUTE') or die('Access Denied.');

class PageFeeds extends ApiController
{
    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/page_feeds",
     *     tags={"page_feeds"},
     *     operationId="getPageFeeds",
     *     summary="List the RSS feeds of this installation, the ones a page_list block publishes its pages through",
     *     security={
     *         {"clientCredentials": {"definitions:read"}},
     *         {"authorization": {"definitions:read"}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/PageFeed")
     *             )
     *         ),
     *     ),
     * )
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function listPageFeeds()
    {
        return new Collection(PageFeedService::getList(), new PageFeedTransformer(), Resources::RESOURCE_PAGE_FEEDS);
    }

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/page_feeds/{pageFeedID}",
     *     tags={"page_feeds"},
     *     operationId="getPageFeedById",
     *     summary="Find an RSS feed by its ID, the one a page_list block names",
     *     security={
     *         {"clientCredentials": {"definitions:read"}},
     *         {"authorization": {"definitions:read"}}
     *     },
     *     @OA\Parameter(
     *         name="pageFeedID",
     *         in="path",
     *         description="ID of the feed to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/PageFeed")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="This installation has no feed of this ID",
     *     ),
     * )
     *
     * @param int|string $pageFeedID
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function read($pageFeedID)
    {
        $feed = PageFeedService::getByID((int) $pageFeedID);
        if ($feed === null) {
            return $this->error(t('Feed not found.'), 404);
        }

        return $this->transform($feed, new PageFeedTransformer(), Resources::RESOURCE_PAGE_FEEDS);
    }
}
