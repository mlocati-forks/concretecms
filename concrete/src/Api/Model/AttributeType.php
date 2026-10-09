<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="AttributeType model",
 *     description="A type of attribute this installation has, which settles what the keys of that type carry and what a value of them looks like.",
 * )
 */
class AttributeType implements \JsonSerializable
{
    /**
     * @OA\Property(
     *     title="Handle of the type",
     *     description="What the type of an attribute key is named by"
     * )
     *
     * @var string
     */
    public $handle;

    /**
     * @OA\Property(title="Name of the type, as the dashboard shows it")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(
     *     title="Handle of the package that brought the type",
     *     description="Empty for the types of the core itself"
     * )
     *
     * @var string
     */
    public $package;

    /**
     * @OA\Property(
     *     type="array",
     *     title="Handles of the categories whose keys may be of this type",
     *     description="A set of keys of an Express entity uses the express category, whatever the attribute categories endpoint names the set",
     *     @OA\Items(type="string")
     * )
     *
     * @var string[]
     */
    public $categories;

    /**
     * @OA\Property(
     *     title="Name of the schema that describes a key of this type",
     *     description="AttributeKey for a type whose keys carry nothing of their own"
     * )
     *
     * @var string
     */
    public $key_schema;

    /**
     * @OA\Property(
     *     title="Name of the schema a value of this type is read with",
     *     description="Empty where a value is read as a plain string, number or boolean, and where its shape follows the key, as an Express one does. What a write takes is declared key by key in the request schemas, and is often shorter: a user_selector is read as the user and written as its ID; an object that carries one is taken as that ID, so what a read handed over goes back as it came"
     * )
     *
     * @var string
     */
    public $value_schema;

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
