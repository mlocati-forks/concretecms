<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Model\ThumbnailType as ThumbnailTypeModel;
use Concrete\Core\Entity\File\Image\Thumbnail\Type\Type as ThumbnailType;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class ThumbnailTypeTransformer extends TransformerAbstract
{
    /**
     * @return array<string,mixed>
     */
    public function transform(ThumbnailType $thumbnailType): array
    {
        $width = $thumbnailType->getWidth();
        $height = $thumbnailType->getHeight();

        $model = new ThumbnailTypeModel();
        $model->id = (int) $thumbnailType->getID();
        $model->handle = (string) $thumbnailType->getHandle();
        $model->name = (string) $thumbnailType->getName();
        $model->width = $width === null ? null : (int) $width;
        $model->height = $height === null ? null : (int) $height;
        $model->sizing_mode = (string) $thumbnailType->getSizingMode();
        $model->upscaling_enabled = (bool) $thumbnailType->isUpscalingEnabled();
        $model->keep_animations = (bool) $thumbnailType->isKeepAnimations();

        return $model->jsonSerialize();
    }
}
