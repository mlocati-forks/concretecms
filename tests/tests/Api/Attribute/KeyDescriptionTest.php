<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Attribute;

use Concrete\Attribute\Address\Controller as AddressController;
use Concrete\Attribute\Boolean\Controller as BooleanController;
use Concrete\Attribute\DateTime\Controller as DateTimeController;
use Concrete\Attribute\Duration\Controller as DurationController;
use Concrete\Attribute\Express\Controller as ExpressController;
use Concrete\Attribute\Select\Controller as SelectController;
use Concrete\Attribute\Textarea\Controller as TextareaController;
use Concrete\Attribute\Topics\Controller as TopicsController;
use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Entity\Attribute\Key\Settings\AddressSettings;
use Concrete\Core\Entity\Attribute\Key\Settings\BooleanSettings;
use Concrete\Core\Entity\Attribute\Key\Settings\DateTimeSettings;
use Concrete\Core\Entity\Attribute\Key\Settings\DurationSettings;
use Concrete\Core\Entity\Attribute\Key\Settings\ExpressSettings;
use Concrete\Core\Entity\Attribute\Key\Settings\SelectSettings;
use Concrete\Core\Entity\Attribute\Key\Settings\Settings;
use Concrete\Core\Entity\Attribute\Key\Settings\TextareaSettings;
use Concrete\Core\Entity\Attribute\Key\Settings\TopicsSettings;
use Concrete\Core\Entity\Express\Entity;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\Tests\TestCase;
use Doctrine\ORM\EntityManager;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * What a key of a type that keeps settings of its own tells a client, which is what it needs to
 * write a value the key accepts.
 *
 * @see \Concrete\Core\Api\Attribute\AttributeApiHandler::describeApiKey()
 */
class KeyDescriptionTest extends TestCase
{
    use SchemaFieldsTrait;

    public function testAnExpressKeyNamesTheEntityItsEntriesComeFrom(): void
    {
        $entity = $this->createMock(Entity::class);
        $entity->method('getId')->willReturn('7b2');
        $settings = $this->createMock(ExpressSettings::class);
        $settings->method('getEntity')->willReturn($entity);

        static::assertSame('7b2', $this->describe(ExpressController::class, $settings)['express_entity_id']);
    }

    public function testAnExpressKeyWhoseEntityIsGoneNamesNone(): void
    {
        static::assertSame('', $this->describe(ExpressController::class, null)['express_entity_id']);
    }

    public function testATopicsKeyNamesTheTreeAndWhatMayBePickedFromIt(): void
    {
        $settings = $this->createMock(TopicsSettings::class);
        $settings->method('getTopicTreeID')->willReturn(4);
        $settings->method('getParentNodeID')->willReturn(18);
        $settings->method('allowMultipleValues')->willReturn(false);

        $described = $this->describe(TopicsController::class, $settings);

        static::assertSame(4, $described['topic_tree_id']);
        static::assertSame(18, $described['parent_node_id']);
        static::assertFalse($described['allow_multiple_values']);
    }

    public function testADurationKeyNamesTheUnitItsValueIsWrittenIn(): void
    {
        $settings = $this->createMock(DurationSettings::class);
        $settings->method('getUnitType')->willReturn('hours');

        static::assertSame('hours', $this->describe(DurationController::class, $settings)['unit']);
    }

    /**
     * A key that names no unit counts in seconds, the way its controller does.
     */
    public function testADurationKeyNamingNoUnitCountsInSeconds(): void
    {
        $settings = $this->createMock(DurationSettings::class);
        $settings->method('getUnitType')->willReturn('');

        static::assertSame('seconds', $this->describe(DurationController::class, $settings)['unit']);
    }

    public function testABooleanKeySaysWhatAnObjectWithNoValueCountsAs(): void
    {
        $settings = $this->createMock(BooleanSettings::class);
        $settings->method('isCheckedByDefault')->willReturn(true);

        static::assertTrue($this->describe(BooleanController::class, $settings)['checked_by_default']);
    }

    public function testASelectKeySaysWhetherMoreThanOneOptionGoes(): void
    {
        $settings = $this->createMock(SelectSettings::class);
        $settings->method('getAllowMultipleValues')->willReturn(true);

        static::assertTrue($this->describe(SelectController::class, $settings)['allow_multiple_values']);
    }

    public function testASelectKeySaysWhetherAValueOfItsOwnGoes(): void
    {
        $settings = $this->createMock(SelectSettings::class);
        $settings->method('getAllowOtherValues')->willReturn(true);

        static::assertTrue($this->describe(SelectController::class, $settings)['allow_other_values']);
    }

    public function testAnAddressKeyNamesTheCountriesItIsMeantFor(): void
    {
        $settings = $this->createMock(AddressSettings::class);
        $settings->method('getDefaultCountry')->willReturn('IT');
        $settings->method('hasCustomCountries')->willReturn(true);
        $settings->method('getCustomCountries')->willReturn(['IT', 'FR']);

        $described = $this->describe(AddressController::class, $settings);

        static::assertSame('IT', $described['default_country']);
        static::assertSame(['IT', 'FR'], $described['countries']);
    }

    /**
     * A key that offers every country of the site names none.
     */
    public function testAnAddressKeyWithoutCountriesOfItsOwnNamesNone(): void
    {
        $settings = $this->createMock(AddressSettings::class);
        $settings->method('hasCustomCountries')->willReturn(false);
        $settings->method('getCustomCountries')->willReturn(['IT']);

        static::assertSame([], $this->describe(AddressController::class, $settings)['countries']);
    }

    public function testADateKeySaysWhetherItIsMeantToKeepATime(): void
    {
        $settings = $this->createMock(DateTimeSettings::class);
        $settings->method('getMode')->willReturn('date');

        static::assertSame('date', $this->describe(DateTimeController::class, $settings)['mode']);
    }

    public function testADateKeyNamingNoModeKeepsADateAndATime(): void
    {
        $settings = $this->createMock(DateTimeSettings::class);
        $settings->method('getMode')->willReturn('');

        static::assertSame('date_time', $this->describe(DateTimeController::class, $settings)['mode']);
    }

    public function testATextareaKeySaysWhetherItTakesHtml(): void
    {
        $settings = $this->createMock(TextareaSettings::class);
        $settings->method('getMode')->willReturn('rich_text');

        static::assertSame('rich_text', $this->describe(TextareaController::class, $settings)['mode']);
    }

    public function testATextareaKeyNamingNoModeTakesPlainText(): void
    {
        $settings = $this->createMock(TextareaSettings::class);
        $settings->method('getMode')->willReturn('');

        static::assertSame('text', $this->describe(TextareaController::class, $settings)['mode']);
    }

    /**
     * @dataProvider provideTypesWithSettingsOfTheirOwn
     *
     * @param string $controllerClass the class of the controller of an attribute type
     * @param string $schema the schema the type says describes its keys
     */
    public function testTheFieldsAreTheOnesTheSchemaOfTheTypeDescribes(string $controllerClass, string $schema): void
    {
        $handler = $this->createHandler($controllerClass);

        static::assertSame($schema, $handler->getApiKeySchema());
        $this->assertFieldsAre($schema, $handler->describeApiKey($this->createKey(null))->jsonSerialize());
    }

    /**
     * @return array<string,array{string,string}>
     */
    public static function provideTypesWithSettingsOfTheirOwn(): array
    {
        return [
            'express' => [ExpressController::class, 'AttributeKeyExpress'],
            'topics' => [TopicsController::class, 'AttributeKeyTopics'],
            'duration' => [DurationController::class, 'AttributeKeyDuration'],
            'boolean' => [BooleanController::class, 'AttributeKeyBoolean'],
            'select' => [SelectController::class, 'AttributeKeySelect'],
            'address' => [AddressController::class, 'AttributeKeyAddress'],
            'date_time' => [DateTimeController::class, 'AttributeKeyDateTime'],
            'textarea' => [TextareaController::class, 'AttributeKeyTextarea'],
        ];
    }

    /**
     * @param \Concrete\Core\Entity\Attribute\Key\Settings\Settings|null $settings the settings of the key
     *
     * @return array<string,mixed> what a client reads of the key
     */
    private function describe(string $controllerClass, ?Settings $settings): array
    {
        return $this->createHandler($controllerClass)->describeApiKey($this->createKey($settings))->jsonSerialize();
    }

    private function createHandler(string $controllerClass): AttributeApiHandler
    {
        $controller = new $controllerClass($this->createMock(EntityManager::class));

        return $controller->getApiHandler();
    }

    /**
     * @param \Concrete\Core\Entity\Attribute\Key\Settings\Settings|null $settings the settings of the key
     *
     * @return \Concrete\Core\Entity\Attribute\Key\Key&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createKey(?Settings $settings): Key
    {
        $key = $this->createMock(Key::class);
        $key->method('getAttributeKeyID')->willReturn(12);
        $key->method('getAttributeKeyHandle')->willReturn('whatever');
        $key->method('getAttributeKeyDisplayName')->willReturn('Whatever');
        $key->method('getAttributeKeySettings')->willReturn($settings);

        return $key;
    }
}
