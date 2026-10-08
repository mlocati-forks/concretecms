<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\ExpressEntity;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="ExpressEntityFilter",
 *     title="ExpressEntityFilter model",
 *     description="A filter that the entries of an Express entity can be narrowed by.",
 * )
 */
class Filter implements \JsonSerializable
{
    /**
     * @OA\Property(title="Identifier of the filter", description="What the filterFields of an express_entry_list block name")
     *
     * @var string
     */
    public $key;

    /**
     * @OA\Property(title="Name of the filter, as the dashboard shows it")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(title="Name of the group the dashboard shows the filter in")
     *
     * @var string
     */
    public $group;

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
