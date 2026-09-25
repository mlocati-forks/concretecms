<?php

declare(strict_types=1);

namespace Concrete\TestHelpers\Block;

use Concrete\Core\Api\Block\BlockApiHandler;
use Concrete\Core\Area\Area;
use Concrete\Core\Block\Block;
use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Block\Controller\SaveMode;
use Concrete\Core\Entity\Block\BlockType\BlockType as BlockTypeEntity;
use Concrete\Core\Entity\File\File as FileEntity;
use Concrete\Core\File\Filesystem;
use Concrete\Core\File\Import\FileImporter;
use Concrete\Core\File\StorageLocation\StorageLocation;
use Concrete\Core\File\StorageLocation\Type\Type as StorageLocationType;
use Concrete\Core\Page\Page;
use Concrete\TestHelpers\Page\PageTestCase;
use Illuminate\Filesystem\Filesystem as LocalFilesystem;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Base test case for the blocks that the API reads and writes: it puts a block in a page, and does
 * with it what the API does.
 */
abstract class BlockApiTestCase extends PageTestCase
{
    /**
     * The file that the tests can refer to.
     *
     * @var \Concrete\Core\Entity\File\File|null
     */
    private $file;

    public function setUp(): void
    {
        parent::setUp();
        $this->file = null;
        $this->deleteStorageDirectory();
    }

    public function tearDown(): void
    {
        $this->deleteStorageDirectory();
        parent::tearDown();
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Database\ConcreteDatabaseTestCase::getTables()
     */
    protected function getTables()
    {
        return array_merge(parent::getTables(), [
            'Blocks',
            'BlockTypeSets',
            'CollectionVersionBlocksOutputCache',
            'TreeTypes',
            'TreeNodeTypes',
            'TreeNodes',
            'TreeFileFolderNodes',
            'TreeFileNodes',
            'TreeNodePermissionAssignments',
            'Trees',
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Database\ConcreteDatabaseTestCase::getEntityClassNames()
     */
    protected function getEntityClassNames(): array
    {
        return array_merge(parent::getEntityClassNames(), [
            BlockTypeEntity::class,
            FileEntity::class,
            'Concrete\Core\Entity\File\Version',
            'Concrete\Core\Entity\File\StorageLocation\StorageLocation',
            'Concrete\Core\Entity\File\StorageLocation\Type\Type',
            'Concrete\Core\Entity\File\Image\Thumbnail\Type\Type',
            'Concrete\Core\Entity\Attribute\Value\FileValue',
            'Concrete\Core\Entity\Attribute\Key\FileKey',
            'Concrete\Core\Entity\Statistics\UsageTracker\FileUsageRecord',
            'Concrete\Core\Entity\StyleCustomizer\Inline\StyleSet',
        ]);
    }

    /**
     * Put a block of a block type in a page, giving its save() method what its own form would send.
     *
     * @param array<string,mixed> $saveData
     */
    protected function addBlock(string $blockTypeHandle, array $saveData, ?Page $page = null, string $saveMode = SaveMode::SAVE_MODE_REQUEST): Block
    {
        $page = $page ?? self::createPage('Page with a ' . $blockTypeHandle . ' block');
        $page->addBlock(
            $this->getBlockType($blockTypeHandle),
            // that's what the API does: it gives the block the area object, not its handle
            Area::getOrCreate($page, 'Main'),
            $saveData,
            $saveMode
        );

        return $this->getBlock($page);
    }

    /**
     * Get a block type, installing it the first time it's asked for.
     */
    protected function getBlockType(string $blockTypeHandle): BlockTypeEntity
    {
        $blockType = BlockType::getByHandle($blockTypeHandle);
        if ($blockType === null) {
            BlockType::installBlockType($blockTypeHandle);
            $blockType = BlockType::getByHandle($blockTypeHandle);
        }

        return $blockType;
    }

    /**
     * Put a block of a block type in a page the way the API does, with a value received by a client.
     *
     * @param array<string,mixed> $value
     */
    protected function addBlockFromApiValue(string $blockTypeHandle, array $value, ?Page $page = null): Block
    {
        $handler = $this->getBlockType($blockTypeHandle)->getController()->getApiHandler();

        return $this->addBlock($blockTypeHandle, $handler->getSaveArgumentsFromApiValue($value, null), $page, SaveMode::SAVE_MODE_IMPORT);
    }

    /**
     * Get the value that the API hands to its clients for a block.
     *
     * @return array<string,mixed>
     */
    protected function getApiValue(Block $block): array
    {
        return $this->getHandler($block)->getApiValue($block);
    }

    /**
     * Write to a block a value received by the API.
     *
     * @param array<string,mixed> $value
     */
    protected function updateBlock(Block $block, array $value): void
    {
        // that's what the areas API controller does
        $block->update($this->getHandler($block)->getSaveArgumentsFromApiValue($value, $block), SaveMode::SAVE_MODE_IMPORT);
    }

    /**
     * Get the first block of the Main area of a page, freshly read from the database.
     */
    protected function getBlock(Page $page): Block
    {
        $page = Page::getByID($page->getCollectionID(), 'RECENT');
        $blocks = $page->getBlocks('Main');

        return array_shift($blocks);
    }

    protected function getHandler(Block $block): BlockApiHandler
    {
        return $block->getController()->getApiHandler();
    }

    /**
     * Get a file that the tests can refer to (it's added to the site the first time it's asked for).
     *
     * @return \Concrete\Core\Entity\File\File
     */
    protected function getFile()
    {
        if ($this->file === null) {
            app(Filesystem::class)->create();
            $storageLocationType = StorageLocationType::add('local', t('Local Storage'));
            $configuration = $storageLocationType->getConfigurationObject();
            $configuration->setRootPath($this->getStorageDirectory());
            $configuration->setWebRootRelativePath('/application/files');
            StorageLocation::add($configuration, 'Default', true);
            $version = app(FileImporter::class)->importLocalFile(DIR_TESTS . '/assets/File/StorageLocation/tiny.png', 'tiny.png');
            $this->file = $version->getFile();
        }

        return $this->file;
    }

    /**
     * Get the directory where the files added by the tests are stored.
     */
    protected function getStorageDirectory(): string
    {
        return str_replace(DIRECTORY_SEPARATOR, '/', __DIR__) . '/files';
    }

    private function deleteStorageDirectory(): void
    {
        $filesystem = new LocalFilesystem();
        if ($filesystem->isDirectory($this->getStorageDirectory())) {
            $filesystem->deleteDirectory($this->getStorageDirectory());
        }
    }
}
