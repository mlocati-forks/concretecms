<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Attribute;

use Concrete\Attribute\Boolean\Controller as BooleanController;
use Concrete\Attribute\Calendar\Controller as CalendarController;
use Concrete\Attribute\CalendarEvent\Controller as CalendarEventController;
use Concrete\Attribute\DateTime\Controller as DateTimeController;
use Concrete\Attribute\Express\Controller as ExpressController;
use Concrete\Attribute\Number\Controller as NumberController;
use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Attribute\Controller as AttributeTypeController;
use Concrete\Core\Attribute\DefaultController;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Entity\Attribute\Value\Value\BooleanValue;
use Concrete\Core\Entity\Attribute\Value\Value\NumberValue;
use Concrete\Tests\TestCase;
use Doctrine\ORM\EntityManager;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * What the API does with the values of an attribute type, which a type that needs more than the
 * defaults takes over with an Api class of its own.
 *
 * @see \Concrete\Core\Api\Attribute\AttributeApiHandler
 */
class AttributeApiHandlerTest extends TestCase
{
    public function testATypeNeedingNothingOfItsOwnGetsTheDefaults(): void
    {
        static::assertSame(AttributeApiHandler::class, get_class($this->createHandler(NumberController::class)));
    }

    public function testTheHandlerIsBuiltOnce(): void
    {
        $controller = $this->createController(BooleanController::class);

        static::assertSame($controller->getApiHandler(), $controller->getApiHandler());
    }

    public function testATypeKeepingTextDeclaresAString(): void
    {
        static::assertSame('string', $this->describeValue(DefaultController::class)['type']);
    }

    public function testADateIsWrittenAsAString(): void
    {
        static::assertSame('string', $this->describeValue(DateTimeController::class)['type']);
    }

    public function testACalendarAndAnEventAreNamedByID(): void
    {
        static::assertSame('integer', $this->describeValue(CalendarController::class)['type']);
        static::assertSame('integer', $this->describeValue(CalendarEventController::class)['type']);
    }

    /**
     * Naming a calendar that isn't there leaves the attribute with no value, the way naming none does.
     */
    public function testNoCalendarIsAValueOfNone(): void
    {
        $value = $this->createHandler(CalendarController::class)->createApiValue(null);

        static::assertInstanceOf(NumberValue::class, $value);
        static::assertNull($value->getValue());
    }

    public function testAnExpressEntryIsNamedByItsPublicIdentifier(): void
    {
        $described = $this->describeValue(ExpressController::class);

        static::assertSame('string', $described['type']);
        static::assertSame('uuid', $described['format']);
    }

    /**
     * A type out there keeps what it wants, and a client reading no type at all knows that only the
     * type settles what a value may be.
     */
    public function testNothingIsDeclaredAboutTheValuesOfAnUnknownType(): void
    {
        static::assertArrayNotHasKey('type', $this->describeValue(AttributeTypeController::class));
    }

    public function testATypeThatDescribesItsOwnValuesIsAsked(): void
    {
        static::assertSame('boolean', $this->describeValue(BooleanController::class)['type']);
    }

    public function testATypeThatBuildsItsOwnValueIsAsked(): void
    {
        // with no value of its own, that's what the boolean type hands over
        static::assertFalse($this->createHandler(BooleanController::class)->getApiValue());
    }

    public function testATypeThatReadsItsOwnValueIsAsked(): void
    {
        $value = $this->createHandler(BooleanController::class)->createApiValue(true);

        static::assertInstanceOf(BooleanValue::class, $value);
        static::assertTrue($value->getValue());
    }

    /**
     * @param string $class the class of the controller of an attribute type
     *
     * @return array<string,mixed> what the specification says about a value of a key of that type
     */
    private function describeValue(string $class): array
    {
        return $this->createHandler($class)->getApiSpecProperty($this->createKey())->jsonSerialize();
    }

    /**
     * @param string $class the class of the controller of an attribute type
     */
    private function createHandler(string $class): AttributeApiHandler
    {
        return $this->createController($class)->getApiHandler();
    }

    /**
     * @param string $class the class of the controller of an attribute type
     */
    private function createController(string $class): AttributeTypeController
    {
        return new $class($this->createMock(EntityManager::class));
    }

    /**
     * @return \Concrete\Core\Entity\Attribute\Key\Key&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createKey(): Key
    {
        $key = $this->createMock(Key::class);
        $key->method('getAttributeKeyID')->willReturn(30);
        $key->method('getAttributeKeyHandle')->willReturn('header_color');
        $key->method('getAttributeKeyDisplayName')->willReturn('Header Color');

        return $key;
    }
}
