<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Block;

use Concrete\Block\Image\Api as ImageApi;
use Concrete\Core\Api\Block\BlockApiHandler;
use Concrete\Core\Api\Block\DefaultBlockApiHandler;
use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Reference\ReferenceKind;
use Concrete\TestHelpers\Database\ConcreteDatabaseTestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
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

    /**
     * @dataProvider provideColumnsNamingSomethingOfTheSite
     */
    public function testAColumnNamingSomethingOfTheSiteSaysWhatItNames(string $handle, string $column, string $reference, string $sentence): void
    {
        $described = $this->getHandler($handle)->getApiValueSchema()['properties'][$column];

        static::assertSame($reference, $described['x-concrete-reference']);
        static::assertStringContainsString($sentence, $described['description']);
    }

    /**
     * @return array<array{string,string,string,string}>
     */
    public static function provideColumnsNamingSomethingOfTheSite(): array
    {
        return [
            ['board', 'boardInstanceID', ReferenceKind::BOARD_INSTANCE, 'GET /boards/{boardID}/instances'],
            ['calendar', 'caID', ReferenceKind::CALENDAR, 'GET /calendars'],
            ['calendar', 'filterByTopicAttributeKeyID', ReferenceKind::ATTRIBUTE_KEY, 'GET /attribute_categories/{categoryHandle}/keys'],
            ['calendar', 'filterByTopicID', ReferenceKind::TOPIC, 'GET /topic_trees'],
            ['calendar_event', 'calendarID', ReferenceKind::CALENDAR, 'GET /calendars'],
            ['calendar_event', 'eventID', ReferenceKind::CALENDAR_EVENT, 'GET /calendars/{calendarID}/events'],
            ['core_conversation', 'reviewAggregateAttributeKey', ReferenceKind::ATTRIBUTE_KEY, 'GET /attribute_categories/{categoryHandle}/keys'],
            ['core_stack_display', 'stID', ReferenceKind::STACK, 'GET /stacks'],
            ['document_library', 'addFilesToSetID', ReferenceKind::FILE_SET, 'GET /file_sets'],
            ['express_entry_detail', 'exEntityID', ReferenceKind::EXPRESS_ENTITY, 'GET /express_entities'],
            ['express_entry_detail', 'exFormID', ReferenceKind::EXPRESS_FORM, 'GET /express_entities'],
            ['express_entry_detail', 'exSpecificEntryID', ReferenceKind::EXPRESS_ENTRY, 'named by UUID'],
            ['express_entry_list', 'exEntityID', ReferenceKind::EXPRESS_ENTITY, 'GET /express_entities'],
            ['express_form', 'addFilesToSet', ReferenceKind::FILE_SET, 'GET /file_sets'],
            ['express_form', 'exFormID', ReferenceKind::EXPRESS_FORM, 'GET /express_entities'],
            ['express_form', 'replyToEmailControlID', ReferenceKind::EXPRESS_FORM_CONTROL, 'the field sets of its form carry'],
            ['page_list', 'customTopicTreeNodeID', ReferenceKind::TOPIC, 'GET /topic_trees'],
            ['topic_list', 'topicTreeID', ReferenceKind::TOPIC_TREE, 'GET /topic_trees'],
        ];
    }

    /**
     * A handler building a property of its own puts the kind of reference where the identifier is.
     */
    public function testAPropertyOfAHandlerSaysWhatItNames(): void
    {
        $calendars = $this->getHandler('event_list')->getApiValueSchema()['properties']['caID'];
        $breakpoints = $this->getHandler('image')->getApiValueSchema()['properties']['breakpoints'];
        $links = $this->getHandler('social_links')->getApiValueSchema()['properties']['links'];

        static::assertSame(ReferenceKind::CALENDAR, $calendars['items']['x-concrete-reference']);
        static::assertSame(ReferenceKind::THUMBNAIL_TYPE, $breakpoints['additionalProperties']['x-concrete-reference']);
        static::assertSame(ReferenceKind::SOCIAL_LINK, $links['items']['x-concrete-reference']);
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
