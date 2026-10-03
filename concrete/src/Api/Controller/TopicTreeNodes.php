<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Fractal\Transformer\TopicTreeNodeTransformer;
use Concrete\Core\Api\Resources;
use Concrete\Core\Api\Tree\TreeNodes;
use Concrete\Core\Tree\Node\Node;

defined('C5_EXECUTE') or die('Access Denied.');

class TopicTreeNodes extends ApiController
{
    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/topic_tree_nodes/{topicTreeNodeID}",
     *     tags={"topic_trees"},
     *     operationId="getTopicTreeNodeById",
     *     summary="Find a node of a topic tree by its ID, a topic or a category of them, with the nodes it holds",
     *     security={
     *         {"clientCredentials": {"definitions:read"}},
     *         {"authorization": {"definitions:read"}}
     *     },
     *     @OA\Parameter(
     *         name="topicTreeNodeID",
     *         in="path",
     *         description="ID of the node to return",
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
     *             @OA\Property(property="data", ref="#/components/schemas/TopicTreeNodeDetail")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="The request may not view this node",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No node of a topic tree of this installation has this ID",
     *     ),
     * )
     *
     * @param int|string $topicTreeNodeID
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function read($topicTreeNodeID)
    {
        $topic = Node::getByID((int) $topicTreeNodeID);
        $types = [TopicTreeNodeTransformer::NODE_TYPE_TOPIC, TopicTreeNodeTransformer::NODE_TYPE_CATEGORY];
        $treeNodes = $this->app->make(TreeNodes::class);
        if ($topic === null
            || !in_array($topic->getTreeNodeTypeHandle(), $types, true)
            // the root of a topic tree is a category of its own type, and the tree endpoints answer for it
            || $treeNodes->isRoot($topic)
        ) {
            return $this->error(t('Topic not found.'), 404);
        }
        if (!$treeNodes->canView($topic)) {
            return $this->error(t('You do not have access to read properties about this topic.'), 403);
        }

        return $this->transform($topic, new TopicTreeNodeTransformer(true, $treeNodes), Resources::RESOURCE_TOPIC_TREE_NODES);
    }
}
