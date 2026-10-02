<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="Stack model",
 * )
 */
class Stack implements \JsonSerializable
{
    /**
     * @OA\Property(format="int64", title="ID of the page holding the stack", description="The blocks of a stack live in the Main area of that page, which the areas endpoints work with; it is also what the core_stack_display block type takes, which falls back to it when the page it sits in speaks a language the stack has no version of")
     *
     * @var int
     */
    public $id;

    /**
     * @OA\Property(title="Stack Name")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(title="Folders the stack is filed in, from the root of the stacks downwards, empty when it is filed in none")
     *
     * @var string
     */
    public $folder;

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
