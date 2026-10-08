<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\ExpressEntity;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="ExpressEntityAssociation",
 *     title="ExpressEntityAssociation model",
 *     description="Entries of another Express entity that an entry is associated with.",
 * )
 */
class Association implements \JsonSerializable
{
    /**
     * @OA\Property(format="uuid", title="ID of the association", description="What the searchAssociations of an express_entry_list block name")
     *
     * @var string
     */
    public $id;

    /**
     * @OA\Property(title="Name of the property of an entry that holds the associated entries", description="What an entry is written and asked for with")
     *
     * @var string
     */
    public $property;

    /**
     * @OA\Property(enum={"one", "many"}, title="Whether an entry is associated with one entry of the other entity or with several")
     *
     * @var string
     */
    public $type;

    /**
     * @OA\Property(format="uuid", title="ID of the entity the associated entries belong to")
     *
     * @var string
     */
    public $target_entity_id;

    /**
     * @OA\Property(title="Handle of the entity the associated entries belong to")
     *
     * @var string
     */
    public $target_entity_handle;

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
