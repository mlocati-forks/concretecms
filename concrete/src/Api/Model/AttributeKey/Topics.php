<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeKey;

use Concrete\Core\Api\Model\AttributeKey;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeKeyTopics",
 *     type="object",
 *     title="A key whose value names the topics of a tree",
 *     allOf={@OA\Schema(ref="#/components/schemas/AttributeKey")}
 * )
 */
class Topics extends AttributeKey
{
    /**
     * @OA\Property(
     *     format="int64",
     *     title="ID of the topic tree a value of this key picks from",
     *     description="What the topic trees endpoint names a tree by"
     * )
     *
     * @var int
     */
    public $topic_tree_id;

    /**
     * @OA\Property(
     *     format="int64",
     *     title="ID of the node a value of this key picks from, 0 for the whole tree"
     * )
     *
     * @var int
     */
    public $parent_node_id;

    /**
     * @OA\Property(title="Whether a value of this key may name more than one topic")
     *
     * @var bool
     */
    public $allow_multiple_values;
}
