<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Page;

use Concrete\Core\Api\Fractal\Transformer\PageFeedTransformer;
use Concrete\Core\Entity\Page\Feed;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A page_list block names the RSS feed it publishes through by its ID, so a client has to be able to read
 * them.
 *
 * @see \Concrete\Core\Api\Fractal\Transformer\PageFeedTransformer
 */
class PageFeedTransformerTest extends TestCase
{
    use SchemaFieldsTrait;

    public function testAFeedSaysWhichPagesItPublishesAndWhatOfThem(): void
    {
        $transformed = $this->transform($this->createFeed([
            'getID' => 7,
            'getHandle' => 'news',
            'getTitle' => 'The news',
            'getDescription' => 'What is new',
            'getFeedURL' => 'https://example.com/rss/news',
            'getParentID' => 42,
            'getIncludeAllDescendents' => true,
            'getDisplayFeaturedOnly' => true,
            'getDisplayAliases' => false,
            'getDisplaySystemPages' => false,
            'getTypeOfContentToDisplay' => 'S',
            'getAreaHandleToDisplay' => 'Main',
        ]), 'blog_entry');

        static::assertSame([
            'id' => 7,
            'handle' => 'news',
            'title' => 'The news',
            'description' => 'What is new',
            'url' => 'https://example.com/rss/news',
            'parent_page_id' => 42,
            'include_all_descendants' => true,
            'page_type' => 'blog_entry',
            'only_featured' => true,
            'include_aliases' => false,
            'include_system_pages' => false,
            'content' => 'description',
            'area' => '',
        ], $transformed);
    }

    /**
     * A feed publishing the whole site names no page whose children it takes.
     */
    public function testAFeedOfTheWholeSiteNamesNoParentPage(): void
    {
        $transformed = $this->transform($this->createFeed(['getParentID' => 0]), '');

        static::assertNull($transformed['parent_page_id']);
    }

    /**
     * Every item of a feed carries either the description of its page or the blocks of one of its areas.
     */
    public function testAFeedCarryingAnAreaNamesIt(): void
    {
        $transformed = $this->transform($this->createFeed([
            'getTypeOfContentToDisplay' => 'A',
            'getAreaHandleToDisplay' => 'Main',
        ]), '');

        static::assertSame('area', $transformed['content']);
        static::assertSame('Main', $transformed['area']);
    }

    public function testTheFieldsAreTheOnesTheSpecificationDescribes(): void
    {
        $this->assertFieldsAre('PageFeed', $this->transform($this->createFeed([]), ''));
    }

    /**
     * @return array<string,mixed> what a client reads of the feed
     */
    private function transform(Feed $feed, string $pageTypeHandle): array
    {
        $transformer = new class ($pageTypeHandle) extends PageFeedTransformer {
            /**
             * @var string
             */
            private $pageTypeHandle;

            public function __construct(string $pageTypeHandle)
            {
                $this->pageTypeHandle = $pageTypeHandle;
            }

            /**
             * {@inheritdoc}
             *
             * @see \Concrete\Core\Api\Fractal\Transformer\PageFeedTransformer::getPageTypeHandle()
             */
            protected function getPageTypeHandle(Feed $feed): string
            {
                return $this->pageTypeHandle;
            }
        };

        return $transformer->transform($feed);
    }

    /**
     * @param array<string,mixed> $answers what the feed answers, by the name of the method asking
     */
    private function createFeed(array $answers): Feed
    {
        $answers += [
            'getID' => 1,
            'getHandle' => 'news',
            'getTitle' => 'The news',
            'getDescription' => '',
            'getFeedURL' => 'https://example.com/rss/news',
            'getParentID' => 0,
            'getIncludeAllDescendents' => false,
            'getDisplayFeaturedOnly' => false,
            'getDisplayAliases' => false,
            'getDisplaySystemPages' => false,
            'getTypeOfContentToDisplay' => 'S',
            'getAreaHandleToDisplay' => '',
        ];
        $feed = $this->createMock(Feed::class);
        foreach ($answers as $method => $answer) {
            $feed->method($method)->willReturn($answer);
        }

        return $feed;
    }
}
