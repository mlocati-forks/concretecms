<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\ExpressEntity;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="ExpressEntityColumn",
 *     title="ExpressEntityColumn model",
 *     description="A column that a list of the entries of an Express entity can be made of.",
 * )
 */
class Column implements \JsonSerializable
{
    /**
     * @OA\Property(title="Identifier of the column", description="What the columns and the defaultSortColumn of an express_entry_list block name")
     *
     * @var string
     */
    public $key;

    /**
     * @OA\Property(title="Name of the column, as the dashboard shows it")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(title="Whether the entries can be sorted by this column")
     *
     * @var bool
     */
    public $sortable;

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
