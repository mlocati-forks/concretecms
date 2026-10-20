<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Board;

use Concrete\Core\Api\Fractal\Transformer\BoardInstanceTransformer;
use Concrete\Core\Api\Fractal\Transformer\BoardTransformer;
use Concrete\Core\Entity\Board\Board;
use Concrete\Core\Entity\Board\Instance;
use Concrete\Core\Entity\Site\Site;
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

    public function testABoardSaysHowItIsNamedAndWhichSiteItBelongsTo(): void
    {
        $transformed = $this->transformBoard($this->createSite(3));

        static::assertAnswerIs(['id' => 7, 'name' => 'Home page', 'site_id' => 3], $transformed);
        $this->assertFieldsAre('Board', $transformed);
    }

    /**
     * A board of no site is shown by every site of the installation, and its instances are laid out
     * site by site.
     */
    public function testABoardOfNoSiteSaysSo(): void
    {
        static::assertNull($this->transformBoard(null)['site_id']);
    }

    public function testAnInstanceSaysWhenItWasCreatedAndLaidOut(): void
    {
        $transformed = $this->transformInstance(new \DateTime('2026-10-01T09:00:00+02:00'), $this->createSite(5));

        static::assertAnswerIs([
            'id' => 12,
            'name' => 'Home page, October',
            'date_created' => '2026-09-30T08:00:00+02:00',
            'date_last_generated' => '2026-10-01T09:00:00+02:00',
            'site_id' => 5,
        ], $transformed);
        $this->assertFieldsAre('BoardInstance', $transformed);
    }

    /**
     * A board that has never been laid out keeps no date of it.
     */
    public function testAnInstanceNeverLaidOutSaysSo(): void
    {
        static::assertNull($this->transformInstance(null, $this->createSite(5))['date_last_generated']);
    }

    /**
     * The instances of a board that every site shows are laid out site by site, and one of no site is
     * the one that every site shows.
     */
    public function testAnInstanceOfNoSiteSaysSo(): void
    {
        static::assertNull($this->transformInstance(null, null)['site_id']);
    }

    /**
     * @return array<string,mixed> what a client reads of the board
     */
    private function transformBoard(?Site $site): array
    {
        $board = $this->createMock(Board::class);
        $board->method('getBoardID')->willReturn(7);
        $board->method('getBoardName')->willReturn('Home page');
        $board->method('getSite')->willReturn($site);

        return (new BoardTransformer())->transform($board);
    }

    private function createSite(int $siteID): Site
    {
        $site = $this->createMock(Site::class);
        $site->method('getSiteID')->willReturn($siteID);

        return $site;
    }

    /**
     * @return array<string,mixed> what a client reads of the instance
     */
    private function transformInstance(?\DateTime $lastGenerated, ?Site $site): array
    {
        $instance = $this->createMock(Instance::class);
        $instance->method('getSite')->willReturn($site);
        $instance->method('getBoardInstanceID')->willReturn(12);
        $instance->method('getBoardInstanceName')->willReturn('Home page, October');
        $instance->method('getDateCreatedObject')->willReturn(new \DateTime('2026-09-30T08:00:00+02:00'));
        $instance->method('getDateLastGenerated')->willReturn($lastGenerated === null ? null : $lastGenerated->getTimestamp());
        $instance->method('getDateLastGeneratedObject')->willReturn($lastGenerated ?? new \DateTime());

        return (new BoardInstanceTransformer())->transform($instance);
    }
}
