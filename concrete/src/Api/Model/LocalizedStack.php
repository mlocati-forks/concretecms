<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="LocalizedStack",
 * )
 */
class LocalizedStack implements \JsonSerializable
{
    /**
     * @OA\Property(title="Locale", description="Locale of the section of the site this version of the stack speaks the language of")
     *
     * @var string
     */
    public $locale;

    /**
     * @OA\Property(format="int64", title="Page ID", description="ID of the page holding the blocks of this version of the stack")
     *
     * @var int
     */
    public $id;

    /**
     * @OA\Property(type="array", nullable=true, title="Blocks", description="Blocks of this version of the stack, answered only where include_contents is on. NULL when the request may not read them, and for no other reason: either it carries no user, as a token of the client credentials flow does, or that user may not view the page listing the stacks and this version of the stack", @OA\Items(ref="#/components/schemas/Block"))
     *
     * @var array<string,mixed>[]|null
     */
    public $blocks;

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
