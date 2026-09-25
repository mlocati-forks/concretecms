<?php

namespace Concrete\Core\Block\Command;

use Concrete\Core\Area\Area;
use Concrete\Core\Entity\Block\BlockType\BlockType;
use Concrete\Core\Block\Controller\SaveMode;
use Concrete\Core\Foundation\Command\Command;
use Concrete\Core\Page\Page;

class AddBlockToPageCommand extends Command
{
    /**
     * @var string
     */
    protected $saveMode = SaveMode::SAVE_MODE_REQUEST;

    /**
     * @var BlockType
     */
    protected $blockType;

    /**
     * @var Page
     */
    protected $page;

    /**
     * @var Area
     */
    protected $area;

    /**
     * @var array
     */
    protected $data;

    /**
     * @return BlockType
     */
    public function getBlockType(): BlockType
    {
        return $this->blockType;
    }

    /**
     * @param BlockType $blockType
     */
    public function setBlockType(BlockType $blockType): void
    {
        $this->blockType = $blockType;
    }

    /**
     * @return Page
     */
    public function getPage(): Page
    {
        return $this->page;
    }

    /**
     * @param Page $page
     */
    public function setPage(Page $page): void
    {
        $this->page = $page;
    }

    /**
     * @return Area
     */
    public function getArea(): Area
    {
        return $this->area;
    }

    /**
     * @param Area $area
     */
    public function setArea(Area $area): void
    {
        $this->area = $area;
    }

    /**
     * Get the way the data is to be read: one of the SaveMode constants.
     */
    public function getSaveMode(): string
    {
        return $this->saveMode;
    }

    /**
     * @param string $saveMode one of the SaveMode constants
     */
    public function setSaveMode(string $saveMode): void
    {
        $this->saveMode = $saveMode;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }

    
}
