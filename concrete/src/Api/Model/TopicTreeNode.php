<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="TopicTreeNode",
 *     type="object",
 *     title="What every answer about a node of a topic tree says",
 * )
 */
abstract class TopicTreeNode
{
    /**
     * @OA\Property(type="integer", format="int64", title="Topic Tree Node ID", description="What the topics of a page name, and what a page_list block filters by")
     *
     * @var int
     */
    private $id;

    /**
     * @OA\Property(type="string", title="Topic Tree Node Name")
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(type="string", title="Names of the nodes down to this one, separated by slashes")
     *
     * @var string
     */
    private $path;

    /**
     * @OA\Property(type="string", enum={"topic", "category"}, title="Whether a page can be filed under this one, or whether it only groups the ones it holds")
     *
     * @var string
     */
    private $type;
}
