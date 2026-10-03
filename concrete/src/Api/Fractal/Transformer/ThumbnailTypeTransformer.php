<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Entity\File\Image\Thumbnail\Type\Type as ThumbnailType;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class ThumbnailTypeTransformer extends TransformerAbstract
{
    /**
     * Get what the API hands to its clients for a thumbnail type.
     *
     * @return array<string,mixed>
     */
    public function transform(ThumbnailType $thumbnailType): array
    {
        $width = $thumbnailType->getWidth();
        $height = $thumbnailType->getHeight();

        return [
            'id' => (int) $thumbnailType->getID(),
            'handle' => (string) $thumbnailType->getHandle(),
            'name' => (string) $thumbnailType->getName(),
            'width' => $width === null ? null : (int) $width,
            'height' => $height === null ? null : (int) $height,
            'sizing_mode' => (string) $thumbnailType->getSizingMode(),
            'upscaling_enabled' => (bool) $thumbnailType->isUpscalingEnabled(),
            'keep_animations' => (bool) $thumbnailType->isKeepAnimations(),
        ];
    }
}
