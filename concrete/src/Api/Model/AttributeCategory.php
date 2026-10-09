<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="AttributeCategory model",
 *     description="A set of attribute keys, named as the API names the objects holding them.",
 * )
 */
class AttributeCategory implements \JsonSerializable
{
    /**
     * @OA\Property(
     *     title="Handle of the set of keys",
     *     description="What the attribute keys endpoint takes: a category of this installation, or express@ followed by the ID of an Express entity"
     * )
     *
     * @var string
     */
    public $handle;

    /**
     * @OA\Property(
     *     title="What the keys of this set belong to",
     *     description="Empty for a category that a package of this installation brought"
     * )
     *
     * @var string
     */
    public $description;

    /**
     * @OA\Property(
     *     title="Handle of the package that defined the category",
     *     description="Empty for the categories of the core itself"
     * )
     *
     * @var string
     */
    public $package;

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
