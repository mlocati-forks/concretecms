<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Fractal\Transformer\TopicTreeTransformer;
use Concrete\Core\Api\Resources;
use Concrete\Core\Api\Tree\TreeNodes;
use Concrete\Core\Tree\Type\Topic as TopicTree;
use League\Fractal\Resource\Collection;

defined('C5_EXECUTE') or die('Access Denied.');

class TopicTrees extends ApiController
{
    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/topic_trees",
     *     tags={"topic_trees"},
     *     operationId="getTopicTrees",
     *     summary="List the trees of topics of this installation, with the nodes at the top of every one of them",
     *     security={
     *         {"clientCredentials": {"definitions:read"}},
     *         {"authorization": {"definitions:read"}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/TopicTree")
     *         ),
     *     ),
     * )
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function listTopicTrees()
    {
        $transformer = new TopicTreeTransformer($this->app->make(TreeNodes::class));

        return new Collection(TopicTree::getList(), $transformer, Resources::RESOURCE_TOPIC_TREES);
    }

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/topic_trees/{topicTreeID}",
     *     tags={"topic_trees"},
     *     operationId="getTopicTreeById",
     *     summary="Find a tree of topics by its ID, the one a topic_list block takes",
     *     security={
     *         {"clientCredentials": {"definitions:read"}},
     *         {"authorization": {"definitions:read"}}
     *     },
     *     @OA\Parameter(
     *         name="topicTreeID",
     *         in="path",
     *         description="ID of the tree to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/TopicTree"),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="This installation has no tree of topics of this ID",
     *     ),
     * )
     *
     * @param int|string $topicTreeID
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function read($topicTreeID)
    {
        $tree = TopicTree::getByID((int) $topicTreeID);
        if (!$tree instanceof TopicTree) {
            return $this->error(t('Topic tree not found.'), 404);
        }
        $transformer = new TopicTreeTransformer($this->app->make(TreeNodes::class));

        return $this->transform($tree, $transformer, Resources::RESOURCE_TOPIC_TREES);
    }
}
