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

    /**
     * @OA\Property(type="array", nullable=true, title="Blocks of the stack, answered only where include_contents is on", description="NULL when the request may not read them, and for no other reason: either it carries no user, as a token of the client credentials flow does, or that user may not view the page listing the stacks and this stack", @OA\Items(ref="#/components/schemas/Block"))
     *
     * @var \Concrete\Core\Api\Model\Block[]|null
     */
    private $blocks;

    /**
     * @OA\Property(type="array", title="Versions of the stack speaking the language of a section of the site", description="Empty where the site speaks one language only", @OA\Items(ref="#/components/schemas/LocalizedStack"))
     *
     * @var \Concrete\Core\Api\Model\LocalizedStack[]
     */
    private $localized;
}
