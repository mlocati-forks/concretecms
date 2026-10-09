<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Attribute;

use Concrete\Attribute\Express\Api as ExpressApi;
use Concrete\Attribute\Express\Controller as ExpressController;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * An Express entry is named by its public identifier, which is no number: a value of this type names
 * the entry with it, and takes it back from what a read handed over.
 *
 * @see \Concrete\Attribute\Express\Api::createApiValue()
 */
class ExpressValueTest extends TestCase
{
    private const IDENTIFIER = '5b1f1cf0-1f3b-4a32-bd1d-7e1b1a4a0f2c';

    public function testAnEntryIsNamedByItsPublicIdentifier(): void
    {
        static::assertSame(self::IDENTIFIER, $this->write(self::IDENTIFIER));
    }

    /**
     * A read hands the entry over whole, in a list wrapped in a data property, and its identifier is
     * among the fields of it.
     */
    public function testTheEntryGoesBackAsAReadHandedItOver(): void
    {
        $read = ['data' => [['id' => self::IDENTIFIER, 'label' => 'An entry', 'url' => 'http://www.example.com']]];

        static::assertSame(self::IDENTIFIER, $this->write($read));
    }

    /**
     * @param mixed $json what a client writes
     *
     * @return mixed what the controller of the type is asked to keep
     */
    private function write($json)
    {
        $written = null;
        $controller = $this->createMock(ExpressController::class);
        $controller->method('createAttributeValueFromNormalizedJson')->willReturnCallback(
            static function ($identifier) use (&$written) {
                $written = $identifier;
            }
        );

        (new ExpressApi($controller))->createApiValue($json);

        return $written;
    }
}
