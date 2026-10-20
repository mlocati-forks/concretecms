<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Fractal\Transformer\Traits\GetStackBlocksTrait;
use Concrete\Core\Api\Model\Stack as StackModel;
use Concrete\Core\Entity\Site\Site;
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
     * @var \Concrete\Core\Entity\Site\Site|null
     */
    protected $site;

    /**
     * @param bool $includeContents whether the blocks of the stacks travel with them
     * @param \Concrete\Core\Entity\Site\Site|null $site the site whose languages the localized versions are
     *     limited to, NULL for every language a version was made for
     */
    public function __construct(bool $includeContents = false, ?Site $site = null)
    {
        $this->includeContents = $includeContents;
        $this->site = $site;
    }

    /**
     * @return array<string,mixed>
     */
    public function transform(Stack $stack): array
    {
        return $this->describe($stack)->jsonSerialize();
    }

    /**
     * Get what a client reads of a stack.
     */
    public function describe(Stack $stack): StackModel
    {
        $model = new StackModel();
        $model->id = (int) $stack->getCollectionID();
        $model->name = (string) $stack->getStackName();
        $model->folder = $this->getFolder($stack);
        if ($this->includeContents) {
            $model->blocks = $this->getStackBlocks($stack);
        } else {
            // the blocks are no part of the answer unless they were asked for
            unset($model->blocks);
        }
        $model->localized = $this->describeLocalized($stack);

        return $model;
    }

    /**
     * @return \Concrete\Core\Api\Model\LocalizedStack[]
     */
    protected function describeLocalized(Stack $stack): array
    {
        $localizedStackTransformer = $this->createLocalizedStackTransformer();
        $localized = [];
        foreach ($this->getLocalizedStacks($stack) as $localizedStack) {
            $localized[] = $localizedStackTransformer->describe($localizedStack);
        }

        return $localized;
    }

    protected function createLocalizedStackTransformer(): LocalizedStackTransformer
    {
        return new LocalizedStackTransformer($this->includeContents);
    }

    /**
     * A stack belongs to every site of the installation, and so do its localized versions: they are read
     * from the stack itself, because going through the languages of one site leaves out the versions made
     * for the languages of another.
     *
     * @return \Concrete\Core\Page\Stack\Stack[]
     */
    protected function getLocalizedStacks(Stack $stack): array
    {
        $localizedStacks = $stack->getLocalizedStacks();
        if ($this->site === null) {
            return $localizedStacks;
        }
        // the languages of a site are the multilingual sections its pages hang from
        $sectionIDs = array_map('intval', Section::getIDList($this->site));

        return array_values(array_filter($localizedStacks, static function (Stack $localized) use ($sectionIDs): bool {
            return in_array((int) $localized->getMultilingualSectionID(), $sectionIDs, true);
        }));
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
