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
abstract class TopicTreeNode implements \JsonSerializable
{
    /**
     * @OA\Property(format="int64", title="Topic Tree Node ID", description="What the topics of a page name, and what a page_list block filters by")
     *
     * @var int
     */
    public $id;

    /**
     * @OA\Property(title="Topic Tree Node Name")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(title="Names of the nodes down to this one, separated by slashes")
     *
     * @var string
     */
    public $path;

    /**
     * @OA\Property(enum={"topic", "category"}, title="Whether a page can be filed under this one, or whether it only groups the ones it holds")
     *
     * @var string
     */
    public $type;

    /**
     * {@inheritdoc}
     *
     * @see \JsonSerializable::jsonSerialize()
     *
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return (array) $this;
    }
}
