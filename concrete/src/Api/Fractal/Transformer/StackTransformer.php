<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Fractal\Transformer\Traits\GetStackBlocksTrait;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Stack\Stack;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class StackTransformer extends TransformerAbstract
{
    use GetStackBlocksTrait;

    /**
     * @var bool
     */
    protected $includeContents;

    /**
     * @param bool $includeContents whether the blocks of the stacks travel with them
     */
    public function __construct(bool $includeContents = false)
    {
        $this->includeContents = $includeContents;
    }

    /**
     * Get what the API hands to its clients for a stack.
     *
     * @return array<string,mixed>
     */
    public function transform(Stack $stack): array
    {
        $data = [
            'id' => (int) $stack->getCollectionID(),
            'name' => (string) $stack->getStackName(),
            'folder' => $this->getFolder($stack),
        ];
        if ($this->includeContents) {
            $data['blocks'] = $this->getStackBlocks($stack);
        }
        $data['localized'] = $this->getLocalized($stack);

        return $data;
    }

    /**
     * Get what the API hands to its clients for the versions of a stack that speak the language of a
     * section of the site.
     *
     * @return array<int,array<string,mixed>>
     */
    protected function getLocalized(Stack $stack): array
    {
        $localizedStackTransformer = $this->createLocalizedStackTransformer();
        $localized = [];
        foreach ($this->getLocalizedStacks($stack) as $localizedStack) {
            $localized[] = $localizedStackTransformer->transform($localizedStack);
        }

        return $localized;
    }

    protected function createLocalizedStackTransformer(): LocalizedStackTransformer
    {
        return new LocalizedStackTransformer($this->includeContents);
    }

    /**
     * Get the versions of a stack that speak the language of a section of the site.
     *
     * @return \Concrete\Core\Page\Stack\Stack[]
     */
    protected function getLocalizedStacks(Stack $stack): array
    {
        $localizedStacks = [];
        foreach (Section::getList() as $section) {
            $localizedStack = $stack->getLocalizedStack($section);
            if ($localizedStack !== null) {
                $localizedStacks[] = $localizedStack;
            }
        }

        return $localizedStacks;
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
