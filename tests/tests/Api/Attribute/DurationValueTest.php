<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Attribute;

use Concrete\Attribute\Duration\Controller as DurationController;
use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Entity\Attribute\Key\Settings\DurationSettings;
use Concrete\Core\Entity\Attribute\Value\PageValue;
use Concrete\Core\Entity\Attribute\Value\Value\DurationValue;
use Concrete\Tests\TestCase;
use Doctrine\ORM\EntityManager;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A duration is a number of a unit the key names, and a client reads and writes that number: it is
 * the same on both sides, so reading a duration and writing it back leaves it as it was.
 *
 * @see \Concrete\Attribute\Duration\Api
 */
class DurationValueTest extends TestCase
{
    public function testTheValueIsCountedInTheUnitTheKeyNames(): void
    {
        static::assertSame(['value' => 2, 'unit' => 'hours'], $this->read(2, 'hours'));
    }

    public function testAKeyNamingNoUnitCountsInSeconds(): void
    {
        static::assertSame(['value' => 90, 'unit' => 'seconds'], $this->read(90, ''));
    }

    public function testAnAttributeWithNoDurationOfItsOwnCountsNone(): void
    {
        $handler = $this->createHandler('hours');

        static::assertSame(['value' => 0, 'unit' => 'hours'], $handler->getApiValue());
    }

    /**
     * Writing back what was read keeps the duration it was: the number travels as it is.
     */
    public function testWhatIsReadIsWhatIsWritten(): void
    {
        $read = $this->read(2, 'hours');

        $written = $this->createHandler('hours')->createApiValue($read['value']);

        static::assertInstanceOf(DurationValue::class, $written);
        static::assertSame(2, (int) $written->getValue());
    }

    /**
     * A read hands the number over along with the unit, and that is what a write takes back.
     */
    public function testTheDurationGoesBackAsAReadHandedItOver(): void
    {
        $read = $this->read(2, 'hours');

        $written = $this->createHandler('hours')->createApiValue($read);

        static::assertInstanceOf(DurationValue::class, $written);
        static::assertSame(2, (int) $written->getValue());
    }

    /**
     * @param int $number the duration the attribute keeps, in the unit of its key
     * @param string $unit the unit the key names, empty where it names none
     *
     * @return array<string,mixed> what a client reads of the value
     */
    private function read(int $number, string $unit): array
    {
        $duration = new DurationValue();
        $duration->setValue($number);
        $value = new PageValue();
        $value->setAttributeValueObject($duration);
        $controller = $this->createController($unit);
        $controller->setAttributeValue($value);

        return $controller->getApiHandler()->getApiValue();
    }

    private function createHandler(string $unit): AttributeApiHandler
    {
        return $this->createController($unit)->getApiHandler();
    }

    private function createController(string $unit): DurationController
    {
        $settings = $this->createMock(DurationSettings::class);
        $settings->method('getUnitType')->willReturn($unit);
        $key = $this->createMock(Key::class);
        $key->method('getAttributeKeySettings')->willReturn($settings);
        $controller = new DurationController($this->createMock(EntityManager::class));
        $controller->setAttributeKey($key);

        return $controller;
    }
}
