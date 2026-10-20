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
     * @OA\Property(title="Name", description="Name of the board, as the dashboard shows it")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(
     *     type="integer",
     *     format="int64",
     *     nullable=true,
     *     title="Site ID",
     *     description="ID of the site the board belongs to, NULL for a board that belongs to every site of this installation and whose instances are laid out site by site"
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
