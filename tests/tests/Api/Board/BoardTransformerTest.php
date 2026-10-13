<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Board;

use Concrete\Core\Api\Fractal\Transformer\BoardInstanceTransformer;
use Concrete\Core\Api\Fractal\Transformer\BoardTransformer;
use Concrete\Core\Entity\Board\Board;
use Concrete\Core\Entity\Board\Instance;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A board block shows one instance of a board, which a client names by ID: these are what it reads to
 * find the one it wants.
 *
 * @see \Concrete\Core\Api\Fractal\Transformer\BoardTransformer
 * @see \Concrete\Core\Api\Fractal\Transformer\BoardInstanceTransformer
 */
class BoardTransformerTest extends TestCase
{
    use SchemaFieldsTrait;

    public function testABoardSaysHowItIsNamed(): void
    {
        $board = $this->createMock(Board::class);
        $board->method('getBoardID')->willReturn(7);
        $board->method('getBoardName')->willReturn('Home page');

        $transformed = (new BoardTransformer())->transform($board);

        static::assertAnswerIs(['id' => 7, 'name' => 'Home page'], $transformed);
        $this->assertFieldsAre('Board', $transformed);
    }

    public function testAnInstanceSaysWhenItWasCreatedAndLaidOut(): void
    {
        $transformed = $this->transformInstance(new \DateTime('2026-10-01T09:00:00+02:00'));

        static::assertAnswerIs([
            'id' => 12,
            'name' => 'Home page, October',
            'date_created' => '2026-09-30T08:00:00+02:00',
            'date_last_generated' => '2026-10-01T09:00:00+02:00',
        ], $transformed);
        $this->assertFieldsAre('BoardInstance', $transformed);
    }

    /**
     * A board that has never been laid out keeps no date of it.
     */
    public function testAnInstanceNeverLaidOutSaysSo(): void
    {
        static::assertNull($this->transformInstance(null)['date_last_generated']);
    }

    /**
     * @return array<string,mixed> what a client reads of the instance
     */
    private function transformInstance(?\DateTime $lastGenerated): array
    {
        $instance = $this->createMock(Instance::class);
        $instance->method('getBoardInstanceID')->willReturn(12);
        $instance->method('getBoardInstanceName')->willReturn('Home page, October');
        $instance->method('getDateCreatedObject')->willReturn(new \DateTime('2026-09-30T08:00:00+02:00'));
        $instance->method('getDateLastGenerated')->willReturn($lastGenerated === null ? null : $lastGenerated->getTimestamp());
        $instance->method('getDateLastGeneratedObject')->willReturn($lastGenerated ?? new \DateTime());

        return (new BoardInstanceTransformer())->transform($instance);
    }
}
