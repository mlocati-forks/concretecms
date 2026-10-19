<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="FileSet",
 *     type="object",
 *     title="FileSet",
 *     description="A set files of the file manager are gathered in."
 * )
 */
class FileSet implements \JsonSerializable
{
    /**
     * @OA\Property(
     *     type="integer",
     *     format="int64",
     *     title="ID of the set",
     *     description="What the addFilesToSetID of a document_library block names, and what the set parameter of the files endpoint takes"
     * )
     *
     * @var int
     */
    public $id;

    /**
     * @OA\Property(title="Name of the set, as the file manager shows it")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(
     *     title="Whether the set belongs to the site",
     *     description="A set that is not public belongs to the user the request carries"
     * )
     *
     * @var bool
     */
    public $public;

    /**
     * @OA\Property(
     *     title="Whether this is the set the file manager stars into",
     *     description="A user has one of these, holding the files they starred"
     * )
     *
     * @var bool
     */
    public $starred;

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
