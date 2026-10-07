<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Block;

use Concrete\Core\Block\BlockType\BlockType as BlockTypeService;
use Concrete\Core\Block\BlockType\BlockTypeList;
use Concrete\Core\Entity\Block\BlockType\BlockType;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Left out are the internal block types the CMS writes out of something a client has no hand in: a slot of a
 * board, the output of a composer control, the documentation of a theme.
 */
class BlockTypeCatalog
{
    /**
     * The handles of the internal block types the catalog holds.
     *
     * @var string[]
     */
    private const DESCRIBED_INTERNAL_HANDLES = [
        BLOCK_HANDLE_LAYOUT_PROXY,
        BLOCK_HANDLE_CONTAINER_PROXY,
        BLOCK_HANDLE_STACK_PROXY,
        BLOCK_HANDLE_SCRAPBOOK_PROXY,
    ];

    /**
     * @return \Concrete\Core\Entity\Block\BlockType\BlockType[]
     */
    public function getList(): array
    {
        $list = new BlockTypeList();
        $list->includeInternalBlockTypes();
        $blockTypes = array_filter($list->get(), function (BlockType $blockType): bool {
            return $this->contains($blockType);
        });

        return array_values($blockTypes);
    }

    /**
     * @return \Concrete\Core\Entity\Block\BlockType\BlockType|null NULL when this installation has no such block type, or the API doesn't describe it
     */
    public function getByHandle(string $handle): ?BlockType
    {
        $blockType = BlockTypeService::getByHandle($handle);

        return $blockType === null || !$this->contains($blockType) ? null : $blockType;
    }

    public function contains(BlockType $blockType): bool
    {
        return !$blockType->isBlockTypeInternal() || in_array((string) $blockType->getBlockTypeHandle(), self::DESCRIBED_INTERNAL_HANDLES, true);
    }
}
