<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="PageThemeImageBreakpoint model",
 * )
 */
class PageThemeImageBreakpoint implements \JsonSerializable
{
    /**
     * @OA\Property(title="Image Breakpoint Handle", description="What the keys of the thumbnails of an image block name")
     *
     * @var string
     */
    public $handle;

    /**
     * @OA\Property(title="Width the breakpoint starts at, as a CSS length", description="The theme shows the image of this breakpoint from this width upwards, as a min-width media query, and the breakpoints come in the order the theme declares them: the one starting at 0 is the fallback")
     *
     * @var string
     */
    public $minimum_width;

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
