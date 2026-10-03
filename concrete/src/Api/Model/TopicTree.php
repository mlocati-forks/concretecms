<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="TopicTree model",
 * )
 */
class TopicTree
{
    /**
     * @OA\Property(type="integer", format="int64", title="Topic Tree ID", description="What a topic_list block takes, and what the topics attribute of a page is tied to")
     *
     * @var int
     */
    private $id;

    /**
     * @OA\Property(type="string", title="Topic Tree Name")
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(type="array", title="Nodes at the top of the tree, the topics and the categories grouping them", description="Going further down takes a request per topic", @OA\Items(ref="#/components/schemas/TopicTreeNodeSummary"))
     *
     * @var \Concrete\Core\Api\Model\TopicTreeNode\Summary[]
     */
    private $nodes;
}
