<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="BoardInstance",
 *     type="object",
 *     title="BoardInstance",
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
     * @OA\Property(title="Name", description="Name of the instance, as the dashboard shows it")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(format="date", title="Created", description="When the instance was created")
     *
     * @var string
     */
    public $date_created;

    /**
     * @OA\Property(
     *     format="date",
     *     nullable=true,
     *     title="Last laid out",
     *     description="When the instance was last laid out. NULL where it has not been laid out yet"
     * )
     *
     * @var string|null
     */
    public $date_last_generated;

    /**
     * @OA\Property(
     *     type="integer",
     *     format="int64",
     *     nullable=true,
     *     title="Site ID",
     *     description="ID of the site the instance was laid out for, NULL where it was laid out for every site of this installation"
     * )
     *
     * @var int|null
     */
    public $site_id;

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
