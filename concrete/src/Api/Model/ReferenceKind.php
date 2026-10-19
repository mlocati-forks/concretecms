<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="ReferenceKind",
 *     type="object",
 *     title="ReferenceKind",
 *     description="A kind of thing that a field of a value names instead of holding a value of its own."
 * )
 */
class ReferenceKind implements \JsonSerializable
{
    /**
     * @OA\Property(
     *     title="Handle of the kind",
     *     description="What the x-concrete-reference of a field naming a thing of this kind carries"
     * )
     *
     * @var string
     */
    public $handle;

    /**
     * @OA\Property(
     *     title="How a field names a thing of this kind",
     *     description="The same sentence that the description of such a field carries"
     * )
     *
     * @var string
     */
    public $description;

    /**
     * @OA\Property(
     *     title="Path of the endpoint handing over the things of this kind",
     *     description="Without the /ccm/api/1.0 prefix, and empty where they come inside another answer: the entries of an Express entity are read through a path named after the entity, and the description says where the others are"
     * )
     *
     * @var string
     */
    public $endpoint;

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
