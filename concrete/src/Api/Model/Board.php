<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="Board",
 *     type="object",
 *     title="Board",
 *     description="A board, which the site fills with the items its data sources bring in."
 * )
 */
class Board implements \JsonSerializable
{
    /**
     * @OA\Property(type="integer", format="int64", title="ID of the board")
     *
     * @var int
     */
    public $id;

    /**
     * @OA\Property(title="Name of the board, as the dashboard shows it")
     *
     * @var string
     */
    public $name;

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
