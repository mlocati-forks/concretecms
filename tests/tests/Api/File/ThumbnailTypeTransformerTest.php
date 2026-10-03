<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\File;

use Concrete\Core\Api\Fractal\Transformer\ThumbnailTypeTransformer;
use Concrete\Core\Entity\File\Image\Thumbnail\Type\Type as ThumbnailType;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \Concrete\Core\Api\Fractal\Transformer\ThumbnailTypeTransformer
 */
class ThumbnailTypeTransformerTest extends TestCase
{
    use SchemaFieldsTrait;

    public function testAThumbnailTypeIsDescribedWithTheSizesItKeeps(): void
    {
        $thumbnailType = new ThumbnailType();
        $thumbnailType->setHandle('stripe_column');
        $thumbnailType->setName('Stripe Column Image');
        $thumbnailType->setWidth(850);
        $thumbnailType->setHeight(650);
        $thumbnailType->setSizingMode(ThumbnailType::RESIZE_EXACT);
        $thumbnailType->setIsUpscalingEnabled(true);
        $thumbnailType->setKeepAnimations(true);

        $transformed = (new ThumbnailTypeTransformer())->transform($thumbnailType);

        static::assertSame([
            // the ID comes from the database, which a type built here has never seen
            'id' => 0,
            'handle' => 'stripe_column',
            'name' => 'Stripe Column Image',
            'width' => 850,
            'height' => 650,
            'sizing_mode' => 'exact',
            'upscaling_enabled' => true,
            'keep_animations' => true,
        ], $transformed);
        $this->assertFieldsAre('ThumbnailType', $transformed);
    }

    public function testAThumbnailTypeSizingByWidthAloneHasNoHeight(): void
    {
        $thumbnailType = new ThumbnailType();
        $thumbnailType->setHandle('large');
        $thumbnailType->setName('Large Image');
        $thumbnailType->setWidth(1140);
        $thumbnailType->setHeight(null);

        $transformed = (new ThumbnailTypeTransformer())->transform($thumbnailType);

        static::assertSame(1140, $transformed['width']);
        static::assertNull($transformed['height']);
        // the entity answers with the mode it defaults to
        static::assertSame('proportional', $transformed['sizing_mode']);
    }
}
