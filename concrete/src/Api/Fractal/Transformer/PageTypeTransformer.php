<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Model\PageType as PageTypeModel;
use Concrete\Core\Entity\Page\Template;
use Concrete\Core\Page\Type\Type as PageType;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class PageTypeTransformer extends TransformerAbstract
{
    /**
     * @return array<string,mixed>
     */
    public function transform(PageType $pageType): array
    {
        $defaultTemplate = $pageType->getPageTypeDefaultPageTemplateObject();
        $templates = [];
        foreach ($pageType->getPageTypePageTemplateObjects() as $template) {
            $templates[] = (string) $template->getPageTemplateHandle();
        }

        $model = new PageTypeModel();
        $model->handle = (string) $pageType->getPageTypeHandle();
        $model->name = (string) $pageType->getPageTypeName();
        $model->default_template = $defaultTemplate instanceof Template ? (string) $defaultTemplate->getPageTemplateHandle() : '';
        $model->templates = $templates;
        $model->package = (string) $pageType->getPackageHandle();

        return $model->jsonSerialize();
    }
}
