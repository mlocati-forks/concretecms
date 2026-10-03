<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\File;

use Concrete\Core\Api\Fractal\Transformer\FileFolderTransformer;
use Concrete\Core\Api\Tree\TreeNodes;
use Concrete\Core\Tree\Node\Node;
use Concrete\Core\Tree\Node\Type\FileFolder;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Tests what the file folders endpoints hand to their clients.
 *
 * @see \Concrete\Core\Api\Fractal\Transformer\FileFolderTransformer
 */
class FileFolderTransformerTest extends TestCase
{
    public function testAFolderSaysWhatItHoldsWithoutHandingItOver(): void
    {
        $folder = $this->createFolder(12, 'Invoices', '/Accounting/Invoices');
        $treeNodes = $this->createTreeNodes([FileFolderTransformer::NODE_TYPE_FILE], []);

        static::assertSame([
            'id' => 12,
            'name' => 'Invoices',
            'path' => '/Accounting/Invoices',
            'has_files' => true,
            'has_sub_folders' => false,
        ], (new FileFolderTransformer($treeNodes))->transform($folder));
    }

    public function testTheFoldersOfAFolderComeWithItWhereTheyAreAskedFor(): void
    {
        $folder = $this->createFolder(12, 'Invoices', '/Accounting/Invoices');
        $subFolder = $this->createFolder(13, '2026', '/Accounting/Invoices/2026');
        $treeNodes = $this->createTreeNodes([FileFolderTransformer::NODE_TYPE_FOLDER], [$subFolder]);

        $transformed = (new FileFolderTransformer($treeNodes, true))->transform($folder);

        // the folders handed over say it themselves, so the folder answering doesn't repeat it
        static::assertArrayNotHasKey('has_sub_folders', $transformed);
        static::assertSame(7, $transformed['parent_id']);
        static::assertSame([
            // they say what they hold, and nothing of what is below them
            ['id' => 13, 'name' => '2026', 'path' => '/Accounting/Invoices/2026', 'has_files' => false, 'has_sub_folders' => true],
        ], $transformed['sub_folders']);
    }

    /**
     * @param string[] $holdsTypeHandles the node types the folders are to say they hold
     * @param \Concrete\Core\Tree\Node\Node[] $children the folders a folder is to hand over
     *
     * @return \Concrete\Core\Api\Tree\TreeNodes&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createTreeNodes(array $holdsTypeHandles, array $children): TreeNodes
    {
        $treeNodes = $this->createMock(TreeNodes::class);
        $treeNodes->method('getParentID')->willReturn(7);
        $treeNodes->method('holdsChildren')->willReturnCallback(static function (Node $node, string $typeHandle) use ($holdsTypeHandles): bool {
            return in_array($typeHandle, $holdsTypeHandles, true);
        });
        $treeNodes->method('getChildren')->willReturn($children);

        return $treeNodes;
    }

    private function createFolder(int $id, string $name, string $path): FileFolder
    {
        $folder = $this->createMock(FileFolder::class);
        $folder->method('getTreeNodeID')->willReturn($id);
        $folder->method('getTreeNodeName')->willReturn($name);
        $folder->method('getTreeNodeDisplayPath')->willReturn($path);

        return $folder;
    }
}
