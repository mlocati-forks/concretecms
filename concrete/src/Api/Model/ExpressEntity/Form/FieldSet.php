<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\ExpressEntity\Form;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="ExpressEntityFormFieldSet",
 *     title="ExpressEntityFormFieldSet",
 *     description="A group of fields a form of an Express entity shows together."
 * )
 */
class FieldSet implements \JsonSerializable
{
    /**
     * @OA\Property(
     *     title="Title",
     *     description="Title of the group, as the form shows it. Empty where the group is shown without one"
     * )
     *
     * @var string
     */
    public $title;

    /**
     * @OA\Property(title="Description", description="What the form says about the group, under its title")
     *
     * @var string
     */
    public $description;

    /**
     * @OA\Property(
     *     type="array",
     *     title="Controls",
     *     description="Controls of the group, in the order the form shows them",
     *     @OA\Items(ref="#/components/schemas/ExpressEntityFormControl")
     * )
     *
     * @var \Concrete\Core\Api\Model\ExpressEntity\Form\Control[]
     */
    public $controls;

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
