<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\ExpressEntity;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="ExpressEntityForm",
 *     title="ExpressEntityForm",
 *     description="A form that displays an entry of an Express entity.",
 * )
 */
class Form implements \JsonSerializable
{
    /**
     * @OA\Property(format="uuid", title="ID of the form", description="What an express_form block, and an express_entry_detail one, name")
     *
     * @var string
     */
    public $id;

    /**
     * @OA\Property(title="Name of the form")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(
     *     type="array",
     *     title="Field sets",
     *     description="Groups of fields the form is made of, in the order it shows them",
     *     @OA\Items(ref="#/components/schemas/ExpressEntityFormFieldSet")
     * )
     *
     * @var \Concrete\Core\Api\Model\ExpressEntity\Form\FieldSet[]
     */
    public $field_sets;

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
