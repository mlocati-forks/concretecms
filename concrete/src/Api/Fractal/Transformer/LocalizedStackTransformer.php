<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Fractal\Transformer\Traits\GetStackBlocksTrait;
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
     * Get what the API hands to its clients for the version of a stack that speaks the language of a
     * section of the site.
     *
     * @return array<string,mixed>
     */
    public function transform(Stack $stack): array
    {
        $section = $stack->getMultilingualSection();
        $data = [
            'locale' => $section === null ? '' : (string) $section->getLocale(),
            'id' => (int) $stack->getCollectionID(),
        ];
        if ($this->includeContents) {
            $data['blocks'] = $this->getStackBlocks($stack);
        }

        return $data;
    }
}
