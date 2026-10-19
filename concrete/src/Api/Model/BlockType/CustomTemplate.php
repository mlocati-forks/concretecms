<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\BlockType;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="BlockTypeCustomTemplate",
 *     type="object",
 *     title="BlockTypeCustomTemplate",
 *     description="A custom template that a block of a block type can be shown with, instead of the default view of its type."
 * )
 */
class CustomTemplate implements \JsonSerializable
{
    /**
     * @OA\Property(
     *     title="Handle of the template",
     *     description="The name of a file, or of a directory, with no path in front of it"
     * )
     *
     * @var string
     */
    public $handle;

    /**
     * @OA\Property(title="Name of the template, built out of its handle")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(
     *     type="array",
     *     title="Handles of the themes that give this template",
     *     description="Empty when every page can be shown with it, whatever its theme: otherwise one of these has to be the theme of the page, which GET /pages/{pageID} gives",
     *     @OA\Items(type="string")
     * )
     *
     * @var string[]
     */
    public $only_for_themes = [];

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
