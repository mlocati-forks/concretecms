<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer\Traits;

use Concrete\Core\Api\Fractal\Transformer\BaseBlockTransformer;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Stack\Stack;
use Concrete\Core\Permission\Checker;

defined('C5_EXECUTE') or die('Access Denied.');

trait GetStackBlocksTrait
{
    /**
     * @var \Concrete\Core\Page\Page|false|null the page listing the stacks, FALSE when there is none,
     *                                          NULL when it has not been looked for yet
     */
    private $stacksListingPage;

    /**
     * Get what the API hands to its clients for the blocks of a stack, which live in the only area a
     * stack has.
     *
     * @return array<int,array<string,mixed>>|null NULL when the request may not read them
     */
    protected function getStackBlocks(Stack $stack): ?array
    {
        if (!$this->canReadStackContents($stack)) {
            return null;
        }
        $blockTransformer = $this->createBlockTransformer();
        $blocks = [];
        foreach ($stack->getBlocks(STACKS_AREA_NAME) as $block) {
            $blocks[] = $blockTransformer->transform($block);
        }

        return $blocks;
    }

    protected function createBlockTransformer(): BaseBlockTransformer
    {
        return new BaseBlockTransformer();
    }

    /**
     * May the user of the request read the contents of a stack?
     */
    protected function canReadStackContents(Stack $stack): bool
    {
        // the core asks the same of whoever opens a stack: the page listing them lets a user deal with
        // stacks at all, and the stack itself may be kept from them where permissions are advanced
        return $this->canViewPage($this->getStacksListingPage()) && $this->canViewPage($stack);
    }

    protected function getStacksListingPage(): ?Page
    {
        if ($this->stacksListingPage === null) {
            $page = Page::getByPath(STACKS_LISTING_PAGE_PATH);
            $this->stacksListingPage = $page instanceof Page && !$page->isError() ? $page : false;
        }

        return $this->stacksListingPage === false ? null : $this->stacksListingPage;
    }

    private function canViewPage(?Page $page): bool
    {
        // the checker answers through its magic call, which gives an integer
        return $page === null ? false : (bool) (new Checker($page))->canViewPage();
    }
}
