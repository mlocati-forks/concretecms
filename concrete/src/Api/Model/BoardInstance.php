<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="BoardInstance",
 *     type="object",
 *     title="BoardInstance model",
 *     description="An instance of a board, the one a board block shows: a board is laid out anew every time it is generated, and each of those layouts is an instance."
 * )
 */
class BoardInstance implements \JsonSerializable
{
    /**
     * @OA\Property(
     *     type="integer",
     *     format="int64",
     *     title="ID of the instance",
     *     description="What the boardInstanceID of a board block names"
     * )
     *
     * @var int
     */
    public $id;

    /**
     * @OA\Property(title="Name of the instance, as the dashboard shows it")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(format="date", title="When the instance was created")
     *
     * @var string
     */
    public $date_created;

    /**
     * @OA\Property(
     *     format="date",
     *     nullable=true,
     *     title="When the instance was last laid out",
     *     description="NULL where it has not been laid out yet"
     * )
     *
     * @var string|null
     */
    public $date_last_generated;

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
