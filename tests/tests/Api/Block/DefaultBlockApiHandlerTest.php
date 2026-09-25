<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Block;

use Concrete\Core\Api\Block\BlockApiHandler;
use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Block\Image\Api as ImageApi;
use Concrete\TestHelpers\Database\ConcreteDatabaseTestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Tests what the API makes of a block type that keeps its settings in its own table.
 *
 * @see \Concrete\Core\Api\Block\DefaultBlockApiHandler
 */
class DefaultBlockApiHandlerTest extends ConcreteDatabaseTestCase
{
    protected $tables = [
        'BlockTypeSets',
    ];

    protected $entityClassNames = [
        \Concrete\Core\Entity\Block\BlockType\BlockType::class,
        \Concrete\Core\Entity\Package::class,
    ];

    public function testEveryBlockTypeHasAHandler(): void
    {
        static::assertInstanceOf(DefaultBlockApiHandler::class, $this->getHandler('image'));
    }

    public function testABlockTypeThatNeedsMoreComesWithItsOwnHandler(): void
    {
        static::assertInstanceOf(ImageApi::class, $this->getHandler('image'));
    }

    public function testAnOverriddenControllerKeepsTheHandlerOfTheCore(): void
    {
        $controller = new OverriddenImageController();

        static::assertInstanceOf(ImageApi::class, $controller->getApiHandler());
    }

    public function testTheHandlerIsBuiltJustOnce(): void
    {
        $controller = $this->getController('image');

        static::assertSame($controller->getApiHandler(), $controller->getApiHandler());
    }

    public function testTheColumnsOfTheBlockAreDescribed(): void
    {
        $schema = $this->getHandler('image')->getApiValueSchema();

        static::assertSame('object', $schema['type']);
        // the blocks share the bID column, so it isn't part of their value
        static::assertArrayNotHasKey('bID', $schema['properties']);
        static::assertArrayHasKey('altText', $schema['properties']);
    }

    public function testAColumnIsDescribedByItsComment(): void
    {
        $described = $this->getHandler('image')->getApiValueSchema()['properties']['sizingOption'];

        static::assertStringContainsString('thumbnails_default', $described['description']);
    }

    public function testTheTypeOfAColumnIsTheOneItDeclares(): void
    {
        $properties = $this->getHandler('image')->getApiValueSchema()['properties'];

        // a column that accepts a NULL says so
        static::assertSame(['string', 'null'], $properties['altText']['type']);
        static::assertSame(255, $properties['altText']['maxLength']);
        static::assertSame(['boolean', 'null'], $properties['cropImage']['type']);
        static::assertSame('boolean', $properties['openLinkInNewWindow']['type']);
        static::assertSame(['integer', 'null'], $properties['maxWidth']['type']);
        static::assertSame(0, $properties['maxWidth']['minimum']);
    }

    public function testTheDefaultOfAColumnIsDescribedWithItsOwnType(): void
    {
        $properties = $this->getHandler('image')->getApiValueSchema()['properties'];

        static::assertFalse($properties['openLinkInNewWindow']['default']);
        static::assertSame('thumbnails_default', $properties['sizingOption']['default']);
    }

    public function testAColumnHoldingAReferenceSaysSo(): void
    {
        $properties = $this->getHandler('image')->getApiValueSchema()['properties'];

        static::assertSame('file', $properties['fID']['x-concrete-reference']);
        static::assertSame('page', $properties['internalLinkCID']['x-concrete-reference']);
        static::assertArrayNotHasKey('x-concrete-reference', $properties['altText']);
    }

    private function getHandler(string $handle): BlockApiHandler
    {
        return $this->getController($handle)->getApiHandler();
    }

    private function getController(string $handle): \Concrete\Core\Block\BlockController
    {
        $blockType = BlockType::getByHandle($handle);
        if ($blockType === null) {
            BlockType::installBlockType($handle);
            $blockType = BlockType::getByHandle($handle);
        }

        return $blockType->getController();
    }
}

/**
 * An image block type whose controller is overridden by a package or by the application, which
 * keeps the class of the core beside it.
 */
class OverriddenImageController extends \Concrete\Block\Image\Controller
{
    protected $btHandle = 'image';
}
