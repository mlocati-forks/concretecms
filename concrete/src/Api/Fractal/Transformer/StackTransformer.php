<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Page\Page;
use Concrete\Core\Page\Stack\Stack;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class StackTransformer extends TransformerAbstract
{
    /**
     * Get what the API hands to its clients for a stack.
     *
     * @return array<string,mixed>
     */
    public function transform(Stack $stack): array
    {
        return [
            'id' => (int) $stack->getCollectionID(),
            'name' => (string) $stack->getStackName(),
            'folder' => $this->getFolder($stack),
        ];
    }

    /**
     * Get the folders a stack is filed in, from the root of the stacks downwards.
     *
     * @return string an empty string when the stack is filed in no folder
     */
    private function getFolder(Stack $stack): string
    {
        $names = [];
        $page = $this->getParentPage($stack);
        while ($page !== null && $page->getPageTypeHandle() === STACK_CATEGORY_PAGE_TYPE) {
            array_unshift($names, (string) $page->getCollectionName());
            $page = $this->getParentPage($page);
        }

        return implode('/', $names);
    }

    protected function getParentPage(Page $page): ?Page
    {
        $parentID = (int) $page->getCollectionParentID();
        $parent = $parentID === 0 ? null : Page::getByID($parentID);

        return $parent instanceof Page && !$parent->isError() ? $parent : null;
    }
}
