<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="LocalizedStack model",
 * )
 */
class LocalizedStack implements \JsonSerializable
{
    /**
     * @OA\Property(title="Locale of the section of the site this version of the stack speaks the language of")
     *
     * @var string
     */
    public $locale;

    /**
     * @OA\Property(format="int64", title="ID of the page holding the blocks of this version of the stack")
     *
     * @var int
     */
    public $id;

    /**
     * @OA\Property(type="array", nullable=true, title="Blocks of this version of the stack, answered only where include_contents is on", description="NULL when the request may not read them, and for no other reason: either it carries no user, as a token of the client credentials flow does, or that user may not view the page listing the stacks and this version of the stack", @OA\Items(ref="#/components/schemas/Block"))
     *
     * @var \Concrete\Core\Api\Model\Block[]|null
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
