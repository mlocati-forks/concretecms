<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Block;

use Concrete\Core\Api\Fractal\Transformer\BaseBlockTransformer;
use Concrete\Core\User\User;
use Concrete\TestHelpers\Api\TokenScopesTrait;
use Concrete\TestHelpers\Block\BlockApiTestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Of a block that the request may not view a client reads the ID and nothing else, because it takes
 * the ID of every block of an area to place one among them or to sort them.
 *
 * @see \Concrete\Core\Api\Fractal\Transformer\BaseBlockTransformer::isVisible()
 */
class BlockScopeTest extends BlockApiTestCase
{
    use TokenScopesTrait;

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Block\BlockApiTestCase::setUp()
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->giveTheTokenScopes(['blocks:read']);
        // with no permission key registered, only the superuser is allowed anything
        $superUser = $this->createMock(User::class);
        $superUser->method('isSuperUser')->willReturn(true);
        app()->instance(User::class, $superUser);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Block\BlockApiTestCase::tearDown()
     */
    public function tearDown(): void
    {
        $this->takeTheTokenBack();
        app()->forgetInstance(User::class);
        parent::tearDown();
    }

    public function testAVisibleBlockIsHandedOverWhole(): void
    {
        $block = $this->addBlock('image', ['fID' => 0, 'sizingOption' => 'full_size']);

        $transformed = (new BaseBlockTransformer())->transform($block);

        static::assertSame((int) $block->getBlockID(), (int) $transformed['id']);
        static::assertSame('image', $transformed['type']);
        static::assertArrayHasKey('value', $transformed);
    }

    public function testABlockIsNamedAndNothingElseWithoutTheScopeOfTheBlocks(): void
    {
        $block = $this->addBlock('image', ['fID' => 0, 'sizingOption' => 'full_size']);
        $this->giveTheTokenScopes(['pages:read']);

        $transformed = (new BaseBlockTransformer())->transform($block);

        static::assertSame(['id' => $block->getBlockID()], $transformed);
    }
}
