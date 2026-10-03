<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Fractal\Transformer\Traits\GetStackBlocksTrait;
use Concrete\Core\Api\Model\Stack as StackModel;
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
     * @return array<string,mixed>
     */
    public function transform(Stack $stack): array
    {
        $model = new StackModel();
        $model->id = (int) $stack->getCollectionID();
        $model->name = (string) $stack->getStackName();
        $model->folder = $this->getFolder($stack);
        if ($this->includeContents) {
            $model->blocks = $this->getStackBlocks($stack);
        }
        $model->localized = $this->getLocalized($stack);

        $values = $model->jsonSerialize();
        if (!$this->includeContents) {
            // the blocks are no part of the answer unless they were asked for
            unset($values['blocks']);
        }

        return $values;
    }

    /**
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
