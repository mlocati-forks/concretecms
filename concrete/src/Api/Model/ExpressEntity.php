<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="ExpressEntity model",
 *     description="An Express entity whose entries this API serves.",
 * )
 */
class ExpressEntity implements \JsonSerializable
{
    /**
     * @OA\Property(format="uuid", title="ID", description="The one that the blocks displaying these entries name")
     *
     * @var string
     */
    public $id;

    /**
     * @OA\Property(title="Handle of the entity")
     *
     * @var string
     */
    public $handle;

    /**
     * @OA\Property(title="Handle of the entity in the plural, which the endpoints of its entries are named after")
     *
     * @var string
     */
    public $plural_handle;

    /**
     * @OA\Property(title="Name of the entity")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(title="Description of the entity")
     *
     * @var string
     */
    public $description;

    /**
     * @OA\Property(type="array", title="Columns that a list of these entries can be made of", description="What the columns of an express_entry_list block name", @OA\Items(ref="#/components/schemas/ExpressEntityColumn"))
     *
     * @var \Concrete\Core\Api\Model\ExpressEntity\Column[]
     */
    public $columns;

    /**
     * @OA\Property(type="array", title="Filters that a list of these entries can be narrowed by", description="What the filters of an express_entry_list block name", @OA\Items(ref="#/components/schemas/ExpressEntityFilter"))
     *
     * @var \Concrete\Core\Api\Model\ExpressEntity\Filter[]
     */
    public $filters;

    /**
     * @OA\Property(type="array", title="Entities these entries are associated with", @OA\Items(ref="#/components/schemas/ExpressEntityAssociation"))
     *
     * @var \Concrete\Core\Api\Model\ExpressEntity\Association[]
     */
    public $associations;

    /**
     * @OA\Property(type="array", title="Forms that display an entry of this entity", @OA\Items(ref="#/components/schemas/ExpressEntityForm"))
     *
     * @var \Concrete\Core\Api\Model\ExpressEntity\Form[]
     */
    public $forms;

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
