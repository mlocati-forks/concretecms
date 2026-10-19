<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\ExpressEntity\Form;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="ExpressEntityFormControl",
 *     title="ExpressEntityFormControl",
 *     description="A field a form of an Express entity is made of."
 * )
 */
class Control implements \JsonSerializable
{
    /**
     * @OA\Property(
     *     format="uuid",
     *     title="ID of the control",
     *     description="What the replyToEmailControlID of an express_form block names"
     * )
     *
     * @var string
     */
    public $id;

    /**
     * @OA\Property(
     *     enum={"entity_property", "attribute_key", "association"},
     *     title="Type",
     *     description="What the control asks for. A property of the entry itself, an attribute of it, or the entries it is associated with"
     * )
     *
     * @var string
     */
    public $type;

    /**
     * @OA\Property(title="Label", description="Label of the control, as the form shows it")
     *
     * @var string
     */
    public $label;

    /**
     * @OA\Property(title="Required?", description="Whether the form asks for it before an entry goes in")
     *
     * @var bool
     */
    public $required;

    /**
     * @OA\Property(
     *     title="Attribute key",
     *     description="Handle of the attribute the control asks for. Empty where the control asks for something else"
     * )
     *
     * @var string
     */
    public $attribute_key;

    /**
     * @OA\Property(
     *     title="Association",
     *     description="Name of the property the association the control asks for fills. Empty where the control asks for something else"
     * )
     *
     * @var string
     */
    public $association;

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
