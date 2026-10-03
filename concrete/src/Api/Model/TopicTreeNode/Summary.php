<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\TopicTreeNode;

use Concrete\Core\Api\Model\TopicTreeNode;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="TopicTreeNodeSummary",
 *     type="object",
 *     title="A node of a topic tree named in the answer about its tree, or about another node",
 *     allOf={@OA\Schema(ref="#/components/schemas/TopicTreeNode")}
 * )
 */
class Summary extends TopicTreeNode
{
    /**
     * @OA\Property(title="Whether topics, or categories of them, are held by this one", description="Says what the node holds, not what the request may view of it: asking for this node by itself hands those nodes over")
     *
     * @var bool
     */
    public $has_children;
}
