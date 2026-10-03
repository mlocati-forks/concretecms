<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="ThumbnailType model",
 * )
 */
class ThumbnailType
{
    /**
     * @OA\Property(type="integer", format="int64", title="Thumbnail Type ID", description="What the thumbnails of an image block take")
     *
     * @var int
     */
    private $id;

    /**
     * @OA\Property(type="string", title="Thumbnail Type Handle", description="The name a type keeps across installations, while the ID this API works with is local to this one")
     *
     * @var string
     */
    private $handle;

    /**
     * @OA\Property(type="string", title="Thumbnail Type Name")
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(type="integer", nullable=true, title="Width of the thumbnails in pixels, the maximum one where the sizing is proportional", description="NULL where the type sizes by height alone")
     *
     * @var int|null
     */
    private $width;

    /**
     * @OA\Property(type="integer", nullable=true, title="Height of the thumbnails in pixels, the maximum one where the sizing is proportional", description="NULL where the type sizes by width alone")
     *
     * @var int|null
     */
    private $height;

    /**
     * @OA\Property(type="string", enum={"proportional", "exact"}, title="How an image is fitted in the width and the height", description="Proportional keeps the proportions of the image within them, exact crops it to them")
     *
     * @var string
     */
    private $sizing_mode;

    /**
     * @OA\Property(type="boolean", title="Whether an image smaller than these sizes is enlarged to them")
     *
     * @var bool
     */
    private $upscaling_enabled;

    /**
     * @OA\Property(type="boolean", title="Whether the thumbnails of an animated image animate too")
     *
     * @var bool
     */
    private $keep_animations;
}
