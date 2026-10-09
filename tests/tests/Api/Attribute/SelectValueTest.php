<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Attribute;

use Concrete\Attribute\Select\Controller as SelectController;
use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Entity\Attribute\Key\Settings\SelectSettings;
use Concrete\Core\Entity\Attribute\Value\Value\SelectValue;
use Concrete\Core\Entity\Attribute\Value\Value\SelectValueOption;
use Concrete\Core\Entity\Attribute\Value\Value\SelectValueOptionList;
use Concrete\TestHelpers\Database\ConcreteDatabaseTestCase;
use Doctrine\ORM\EntityManagerInterface;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A client picks the options of a select by ID, and one the key hasn't got by the text of it, which
 * the key gains where it allows a value of its own.
 *
 * @see \Concrete\Attribute\Select\Api::createApiValue()
 */
class SelectValueTest extends ConcreteDatabaseTestCase
{
    /**
     * @var \Concrete\Core\Entity\Attribute\Value\Value\SelectValueOptionList|null
     */
    private $list;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Database\ConcreteDatabaseTestCase::getEntityClassNames()
     */
    protected function getEntityClassNames(): array
    {
        return [
            SelectValueOptionList::class,
            SelectValueOption::class,
        ];
    }

    public function testAnOptionIsPickedByID(): void
    {
        $handler = $this->createHandler(true, false);
        $id = (int) $this->getOption('Red')->getSelectAttributeOptionID();

        static::assertSame(['Red'], $this->getValues($handler->createApiValue([$id])));
    }

    public function testAnIDThatNamesNoOptionOfTheKeyIsIgnored(): void
    {
        $handler = $this->createHandler(true, false);

        static::assertSame([], $this->getValues($handler->createApiValue([99999])));
    }

    /**
     * The text of a value names an option of the key as well as its ID does.
     */
    public function testAnOptionIsPickedByTheTextOfIt(): void
    {
        $handler = $this->createHandler(true, false);

        static::assertSame(['Green'], $this->getValues($handler->createApiValue(['Green'])));
    }

    public function testAKeyThatAllowsAValueOfItsOwnGainsIt(): void
    {
        $handler = $this->createHandler(true, true);

        $value = $handler->createApiValue(['Red', 'Purple']);

        static::assertSame(['Red', 'Purple'], $this->getValues($value));
        $added = $value->getSelectedOptions()[1];
        static::assertNull($added->getSelectAttributeOptionID());
        static::assertTrue((bool) $added->isEndUserAdded());
        static::assertSame($this->list, $added->getOptionList());
    }

    public function testAKeyThatTakesItsOptionsAsTheyAreIgnoresANewValue(): void
    {
        $handler = $this->createHandler(true, false);

        static::assertSame(['Red'], $this->getValues($handler->createApiValue(['Red', 'Purple'])));
    }

    public function testAKeyThatTakesOneOptionKeepsTheFirst(): void
    {
        $handler = $this->createHandler(false, false);

        static::assertSame(['Red'], $this->getValues($handler->createApiValue(['Red', 'Green'])));
    }

    /**
     * @param bool $multiple whether a value of the key may name more than one option
     * @param bool $other whether a value of the key may name an option the key hasn't got
     */
    private function createHandler(bool $multiple, bool $other): AttributeApiHandler
    {
        $entityManager = app(EntityManagerInterface::class);
        $this->list = new SelectValueOptionList();
        $entityManager->persist($this->list);
        foreach (['Red', 'Green'] as $index => $text) {
            $option = new SelectValueOption();
            $option->setOptionList($this->list);
            $option->setDisplayOrder($index);
            $option->setSelectAttributeOptionValue($text);
            $this->list->getOptions()->add($option);
            $entityManager->persist($option);
        }
        $entityManager->flush();
        $settings = new SelectSettings();
        $settings->setAllowMultipleValues($multiple);
        $settings->setAllowOtherValues($other);
        $settings->setOptionList($this->list);
        $key = $this->createMock(Key::class);
        $key->method('getAttributeKeySettings')->willReturn($settings);
        $controller = new SelectController($entityManager);
        $controller->setAttributeKey($key);

        return $controller->getApiHandler();
    }

    private function getOption(string $text): SelectValueOption
    {
        foreach ($this->list->getOptions() as $option) {
            if ($option->getSelectAttributeOptionValue() === $text) {
                return $option;
            }
        }

        throw new \RuntimeException('The list has no option named ' . $text);
    }

    /**
     * @param mixed $value what the handler built
     *
     * @return string[] the text of the options the value picked
     */
    private function getValues($value): array
    {
        static::assertInstanceOf(SelectValue::class, $value);
        $values = [];
        foreach ($value->getSelectedOptions() as $option) {
            $values[] = (string) $option->getSelectAttributeOptionValue();
        }

        return $values;
    }
}
