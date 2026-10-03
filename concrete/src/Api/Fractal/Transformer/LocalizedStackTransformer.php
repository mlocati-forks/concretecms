<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Fractal\Transformer\Traits\GetStackBlocksTrait;
use Concrete\Core\Api\Model\LocalizedStack;
use Concrete\Core\Page\Stack\Stack;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class LocalizedStackTransformer extends TransformerAbstract
{
    use GetStackBlocksTrait;

    /**
     * @var bool
     */
    protected $includeContents;

    /**
     * @param bool $includeContents whether the blocks of the stack travel with it
     */
    public function __construct(bool $includeContents)
    {
        $this->includeContents = $includeContents;
    }

    /**
     * @return array<string,mixed>
     */
    public function transform(Stack $stack): array
    {
        $section = $stack->getMultilingualSection();

        $model = new LocalizedStack();
        $model->locale = $section === null ? '' : (string) $section->getLocale();
        $model->id = (int) $stack->getCollectionID();
        if ($this->includeContents) {
            $model->blocks = $this->getStackBlocks($stack);
        }

        $values = $model->jsonSerialize();
        if (!$this->includeContents) {
            // the blocks are no part of the answer unless they were asked for
            unset($values['blocks']);
        }

        return $values;
    }
}
