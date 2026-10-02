<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Entity\Page\Template;
use Concrete\Core\Page\Type\Type as PageType;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class PageTypeTransformer extends TransformerAbstract
{
    /**
     * Get what the API hands to its clients for a page type.
     *
     * @return array<string,mixed>
     */
    public function transform(PageType $pageType): array
    {
        $defaultTemplate = $pageType->getPageTypeDefaultPageTemplateObject();
        $templates = [];
        foreach ($pageType->getPageTypePageTemplateObjects() as $template) {
            $templates[] = (string) $template->getPageTemplateHandle();
        }

        return [
            'handle' => (string) $pageType->getPageTypeHandle(),
            'name' => (string) $pageType->getPageTypeName(),
            'default_template' => $defaultTemplate instanceof Template ? (string) $defaultTemplate->getPageTemplateHandle() : '',
            'templates' => $templates,
            'package' => (string) $pageType->getPackageHandle(),
        ];
    }
}
