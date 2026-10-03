<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Stack;

use Concrete\Core\Api\Fractal\Transformer\BaseBlockTransformer;
use Concrete\Core\Api\Fractal\Transformer\LocalizedStackTransformer;
use Concrete\Core\Api\Fractal\Transformer\StackTransformer;
use Concrete\Core\Block\Block;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Stack\Stack;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \Concrete\Core\Api\Fractal\Transformer\StackTransformer
 */
class StackTransformerTest extends TestCase
{
    use SchemaFieldsTrait;

    public function testAStackIsDescribedWithThePageHoldingItsBlocks(): void
    {
        $stack = $this->createStack(211, 'Contact Details');

        $transformed = $this->createTransformer()->transform($stack);

        static::assertSame([
            'id' => 211,
            'name' => 'Contact Details',
            'folder' => '',
            'localized' => [],
        ], $transformed);
    }

    public function testAStackSaysWhichFolderHoldsIt(): void
    {
        $stack = $this->createStack(211, 'Contact Details');

        $transformed = $this->createTransformer(false, true, [], [
            211 => $this->createFolder('Footers'),
            // the root of the stacks is a page of its own, of no page type
            'Footers' => $this->createPage(),
        ])->transform($stack);

        static::assertSame('Footers', $transformed['folder']);
    }

    public function testTheFoldersHoldingAStackAreListedFromTheRootDownwards(): void
    {
        $stack = $this->createStack(211, 'Contact Details');

        $transformed = $this->createTransformer(false, true, [], [
            211 => $this->createFolder('Columns'),
            'Columns' => $this->createFolder('Footers'),
            'Footers' => $this->createFolder('Marketing'),
            'Marketing' => $this->createPage(),
        ])->transform($stack);

        static::assertSame('Marketing/Footers/Columns', $transformed['folder']);
    }

    public function testTheBlocksOfAStackComeFromItsOnlyAreaWhenAsked(): void
    {
        $stack = $this->createStack(211, 'Contact Details');
        $stack->method('getBlocks')->with(STACKS_AREA_NAME)->willReturn([$this->createBlock(184, 'content')]);

        $transformed = $this->createTransformer(true)->transform($stack);

        $this->assertFieldsAre('Stack', $transformed);
        static::assertCount(1, $transformed['blocks']);
        static::assertSame(184, $transformed['blocks'][0]['id']);
        static::assertSame('content', $transformed['blocks'][0]['type']);
    }

    public function testTheBlocksOfAStackTheRequestMayNotReadAreNull(): void
    {
        $stack = $this->createStack(211, 'Contact Details');
        $stack->expects(static::never())->method('getBlocks');

        $transformed = $this->createTransformer(true, false)->transform($stack);

        static::assertNull($transformed['blocks']);
    }

    public function testTheLocalizedVersionsOfAStackTravelWithIt(): void
    {
        $stack = $this->createStack(211, 'Contact Details');
        $localized = $this->createStack(212, 'Dettagli di contatto');
        $localized->method('getMultilingualSection')->willReturn($this->createSection('it_IT'));

        $transformed = $this->createTransformer(false, true, [$localized])->transform($stack);

        static::assertSame([
            ['locale' => 'it_IT', 'id' => 212],
        ], $transformed['localized']);
    }

    public function testTheBlocksOfTheLocalizedVersionsComeWithTheOnesOfTheStack(): void
    {
        $stack = $this->createStack(211, 'Contact Details');
        $stack->method('getBlocks')->willReturn([]);
        $localized = $this->createStack(212, 'Dettagli di contatto');
        $localized->method('getMultilingualSection')->willReturn($this->createSection('it_IT'));
        $localized->method('getBlocks')->with(STACKS_AREA_NAME)->willReturn([$this->createBlock(185, 'content')]);

        $transformed = $this->createTransformer(true, true, [$localized])->transform($stack);

        static::assertSame([], $transformed['blocks']);
        $this->assertFieldsAre('LocalizedStack', $transformed['localized'][0]);
        static::assertCount(1, $transformed['localized'][0]['blocks']);
        static::assertSame(185, $transformed['localized'][0]['blocks'][0]['id']);
    }

    /**
     * @param \Concrete\Core\Page\Stack\Stack[] $localizedStacks the versions of the stack speaking the
     *                                                           language of a section of the site
     * @param array<int|string,\Concrete\Core\Page\Page|null> $parents the page above the stack, keyed
     *                                                                 by its ID, and the ones above it,
     *                                                                 keyed by the name of the page
     *                                                                 below
     */
    private function createTransformer(bool $includeContents = false, bool $mayReadContents = true, array $localizedStacks = [], array $parents = []): StackTransformer
    {
        // the real one asks a block for its API value, which only a block of a real page can answer
        $blockTransformer = new class extends BaseBlockTransformer {
            /**
             * {@inheritdoc}
             *
             * @see \Concrete\Core\Api\Fractal\Transformer\BaseBlockTransformer::transform()
             */
            public function transform(Block $block)
            {
                return ['id' => $block->getBlockID(), 'type' => $block->getBlockTypeHandle()];
            }
        };

        return new class ($includeContents, $mayReadContents, $localizedStacks, $parents, $blockTransformer) extends StackTransformer {
            /**
             * @var bool
             */
            private $mayReadContents;

            /**
             * @var \Concrete\Core\Page\Stack\Stack[]
             */
            private $localizedStacks;

            /**
             * @var array<int|string,\Concrete\Core\Page\Page|null>
             */
            private $parents;

            /**
             * @var \Concrete\Core\Api\Fractal\Transformer\BaseBlockTransformer
             */
            private $blockTransformer;

            /**
             * @param \Concrete\Core\Page\Stack\Stack[] $localizedStacks
             * @param array<int|string,\Concrete\Core\Page\Page|null> $parents
             */
            public function __construct(bool $includeContents, bool $mayReadContents, array $localizedStacks, array $parents, BaseBlockTransformer $blockTransformer)
            {
                parent::__construct($includeContents);
                $this->mayReadContents = $mayReadContents;
                $this->localizedStacks = $localizedStacks;
                $this->parents = $parents;
                $this->blockTransformer = $blockTransformer;
            }

            /**
             * {@inheritdoc}
             *
             * @see \Concrete\Core\Api\Fractal\Transformer\Traits\GetStackBlocksTrait::canReadStackContents()
             */
            protected function canReadStackContents(Stack $stack): bool
            {
                return $this->mayReadContents;
            }

            /**
             * {@inheritdoc}
             *
             * @see \Concrete\Core\Api\Fractal\Transformer\Traits\GetStackBlocksTrait::createBlockTransformer()
             */
            protected function createBlockTransformer(): BaseBlockTransformer
            {
                return $this->blockTransformer;
            }

            /**
             * {@inheritdoc}
             *
             * @see \Concrete\Core\Api\Fractal\Transformer\StackTransformer::getLocalizedStacks()
             */
            protected function getLocalizedStacks(Stack $stack): array
            {
                return $this->localizedStacks;
            }

            /**
             * {@inheritdoc}
             *
             * @see \Concrete\Core\Api\Fractal\Transformer\StackTransformer::createLocalizedStackTransformer()
             */
            protected function createLocalizedStackTransformer(): LocalizedStackTransformer
            {
                return new class ($this->includeContents, $this->mayReadContents, $this->blockTransformer) extends LocalizedStackTransformer {
                    /**
                     * @var bool
                     */
                    private $mayReadContents;

                    /**
                     * @var \Concrete\Core\Api\Fractal\Transformer\BaseBlockTransformer
                     */
                    private $blockTransformer;

                    public function __construct(bool $includeContents, bool $mayReadContents, BaseBlockTransformer $blockTransformer)
                    {
                        parent::__construct($includeContents);
                        $this->mayReadContents = $mayReadContents;
                        $this->blockTransformer = $blockTransformer;
                    }

                    /**
                     * {@inheritdoc}
                     *
                     * @see \Concrete\Core\Api\Fractal\Transformer\Traits\GetStackBlocksTrait::canReadStackContents()
                     */
                    protected function canReadStackContents(Stack $stack): bool
                    {
                        return $this->mayReadContents;
                    }

                    /**
                     * {@inheritdoc}
                     *
                     * @see \Concrete\Core\Api\Fractal\Transformer\Traits\GetStackBlocksTrait::createBlockTransformer()
                     */
                    protected function createBlockTransformer(): BaseBlockTransformer
                    {
                        return $this->blockTransformer;
                    }
                };
            }

            /**
             * {@inheritdoc}
             *
             * @see \Concrete\Core\Api\Fractal\Transformer\StackTransformer::getParentPage()
             */
            protected function getParentPage(Page $page): ?Page
            {
                $key = $page instanceof Stack ? $page->getCollectionID() : $page->getCollectionName();

                return $this->parents[$key] ?? null;
            }
        };
    }

    /**
     * @return \Concrete\Core\Page\Stack\Stack&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createStack(int $id, string $name): Stack
    {
        $stack = $this->createMock(Stack::class);
        $stack->method('getCollectionID')->willReturn($id);
        $stack->method('getStackName')->willReturn($name);

        return $stack;
    }

    private function createFolder(string $name): Page
    {
        $folder = $this->createMock(Page::class);
        $folder->method('getCollectionName')->willReturn($name);
        $folder->method('getPageTypeHandle')->willReturn(STACK_CATEGORY_PAGE_TYPE);

        return $folder;
    }

    private function createPage(): Page
    {
        $page = $this->createMock(Page::class);
        $page->method('getPageTypeHandle')->willReturn('');

        return $page;
    }

    private function createBlock(int $id, string $type): Block
    {
        $block = $this->createMock(Block::class);
        $block->method('getBlockID')->willReturn($id);
        $block->method('getBlockTypeHandle')->willReturn($type);

        return $block;
    }

    private function createSection(string $locale): Section
    {
        $section = $this->createMock(Section::class);
        $section->method('getLocale')->willReturn($locale);

        return $section;
    }
}
