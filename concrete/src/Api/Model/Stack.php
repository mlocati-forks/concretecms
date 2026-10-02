<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="Stack model",
 * )
 */
class Stack
{
    /**
     * @OA\Property(type="integer", format="int64", title="ID of the page holding the stack", description="The blocks of a stack live in the Main area of that page, which the areas endpoints work with; it is also what the core_stack_display block type takes, which falls back to it when the page it sits in speaks a language the stack has no version of")
     *
     * @var int
     */
    private $id;

    /**
     * @OA\Property(type="string", title="Stack Name")
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(type="string", title="Folders the stack is filed in, from the root of the stacks downwards, empty when it is filed in none")
     *
     * @var string
     */
    private $folder;
}
