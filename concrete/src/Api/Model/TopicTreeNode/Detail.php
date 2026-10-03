<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\TopicTreeNode;

use Concrete\Core\Api\Model\TopicTreeNode;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="TopicTreeNodeDetail",
 *     type="object",
 *     title="A node of a topic tree asked for by itself, a topic or a category grouping topics",
 *     allOf={@OA\Schema(ref="#/components/schemas/TopicTreeNode")}
 * )
 */
class Detail extends TopicTreeNode
{
    /**
     * @OA\Property(format="int64", title="ID of the tree holding this node")
     *
     * @var int|null
     */
    public $topic_tree_id;

    /**
     * @OA\Property(format="int64", title="ID of the node holding this one, NULL for a node at the top of its tree")
     *
     * @var int|null
     */
    public $parent_id;

    /**
     * @OA\Property(type="array", title="Nodes this one holds directly", description="Going further down takes a request per node", @OA\Items(ref="#/components/schemas/TopicTreeNodeSummary"))
     *
     * @var \Concrete\Core\Api\Model\TopicTreeNode\Summary[]
     */
    public $nodes;
}
