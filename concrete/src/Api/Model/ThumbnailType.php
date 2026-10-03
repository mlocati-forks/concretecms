<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="ThumbnailType model",
 * )
 */
class ThumbnailType implements \JsonSerializable
{
    /**
     * @OA\Property(format="int64", title="Thumbnail Type ID", description="What the thumbnails of an image block take")
     *
     * @var int
     */
    public $id;

    /**
     * @OA\Property(title="Thumbnail Type Handle", description="The name a type keeps across installations, while the ID this API works with is local to this one")
     *
     * @var string
     */
    public $handle;

    /**
     * @OA\Property(title="Thumbnail Type Name")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(title="Width of the thumbnails in pixels, the maximum one where the sizing is proportional", description="NULL where the type sizes by height alone")
     *
     * @var int|null
     */
    public $width;

    /**
     * @OA\Property(title="Height of the thumbnails in pixels, the maximum one where the sizing is proportional", description="NULL where the type sizes by width alone")
     *
     * @var int|null
     */
    public $height;

    /**
     * @OA\Property(enum={"proportional", "exact"}, title="How an image is fitted in the width and the height", description="Proportional keeps the proportions of the image within them, exact crops it to them")
     *
     * @var string
     */
    public $sizing_mode;

    /**
     * @OA\Property(title="Whether an image smaller than these sizes is enlarged to them")
     *
     * @var bool
     */
    public $upscaling_enabled;

    /**
     * @OA\Property(title="Whether the thumbnails of an animated image animate too")
     *
     * @var bool
     */
    public $keep_animations;

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
