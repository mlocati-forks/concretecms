<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Stack;

use Concrete\Core\Api\Fractal\Transformer\StackTransformer;
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

        $transformed = $this->transform($stack, []);

        static::assertSame(['id' => 211, 'name' => 'Contact Details', 'folder' => ''], $transformed);
        static::assertSame($this->getSchemaFields('Stack'), array_keys($transformed));
    }

    public function testAStackSaysWhichFolderHoldsIt(): void
    {
        $stack = $this->createStack(211, 'Contact Details');

        $transformed = $this->transform($stack, [
            211 => $this->createFolder('Footers'),
            // the root of the stacks is a page of its own, of no page type
            'Footers' => $this->createPage(),
        ]);

        static::assertSame('Footers', $transformed['folder']);
    }

    public function testTheFoldersHoldingAStackAreListedFromTheRootDownwards(): void
    {
        $stack = $this->createStack(211, 'Contact Details');

        $transformed = $this->transform($stack, [
            211 => $this->createFolder('Columns'),
            'Columns' => $this->createFolder('Footers'),
            'Footers' => $this->createFolder('Marketing'),
            'Marketing' => $this->createPage(),
        ]);

        static::assertSame('Marketing/Footers/Columns', $transformed['folder']);
    }

    /**
     * Transform a stack, with the pages above it given by the name of their parent.
     *
     * @param array<int|string,\Concrete\Core\Page\Page|null> $parents the page above the stack,
     *                                                                 keyed by its ID, and the
     *                                                                 ones above it, keyed by
     *                                                                 the name of the page below
     *
     * @return array<string,mixed>
     */
    private function transform(Stack $stack, array $parents): array
    {
        $transformer = new class ($parents) extends StackTransformer {
            /**
             * @var array<int|string,\Concrete\Core\Page\Page|null>
             */
            private $parents;

            /**
             * @param array<int|string,\Concrete\Core\Page\Page|null> $parents
             */
            public function __construct(array $parents)
            {
                $this->parents = $parents;
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

        return $transformer->transform($stack);
    }

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
}
