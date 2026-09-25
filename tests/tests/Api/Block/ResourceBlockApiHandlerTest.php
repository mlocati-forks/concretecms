<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Block;

use Concrete\Core\Api\ApiResourceValueInterface;
use Concrete\Core\Api\Block\ResourceBlockApiHandler;
use Concrete\Core\Block\Block;
use Concrete\Core\Block\BlockController;
use Concrete\Tests\TestCase;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\ResourceInterface;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Tests the block types that build their own value with the interface that the API honoured before
 * the handlers existed: no block type of the core does, but it keeps working.
 *
 * @see \Concrete\Core\Api\Block\ResourceBlockApiHandler
 */
class ResourceBlockApiHandlerTest extends TestCase
{
    public function testSuchABlockTypeGetsTheHandlerOfItsOwn(): void
    {
        static::assertInstanceOf(ResourceBlockApiHandler::class, $this->createController()->getApiHandler());
    }

    public function testTheValueIsTheOneOfItsResource(): void
    {
        $handler = $this->createController()->getApiHandler();

        static::assertSame(['whatever' => ['it', 'wants']], $handler->getApiValue($this->createBlock()));
    }

    public function testWhatTheClientSendsIsWrittenUntouched(): void
    {
        $handler = $this->createController()->getApiHandler();

        static::assertSame(['whatever' => 'it wants'], $handler->getSaveArgumentsFromApiValue(['whatever' => 'it wants'], null));
    }

    public function testTheSchemaSaysThatItDescribesNothing(): void
    {
        $schema = $this->createController()->getApiHandler()->getApiValueSchema();

        static::assertSame('object', $schema['type']);
        static::assertTrue($schema['x-concrete-undescribed']);
    }

    private function createController(): BlockController
    {
        return new class extends BlockController implements ApiResourceValueInterface {
            public function getApiValueResource(): ?ResourceInterface
            {
                $transformer = new class extends TransformerAbstract {
                    /**
                     * @param array<string,mixed> $value
                     *
                     * @return array<string,mixed>
                     */
                    public function transform(array $value): array
                    {
                        return $value;
                    }
                };

                return new Item(['whatever' => ['it', 'wants']], $transformer);
            }
        };
    }

    private function createBlock(): Block
    {
        return new Block();
    }
}
