<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Model\PageTemplate as PageTemplateModel;
use Concrete\Core\Entity\Page\Template;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class PageTemplateTransformer extends TransformerAbstract
{
    /**
     * @return array<string,mixed>
     */
    public function transform(Template $template): array
    {
        $model = new PageTemplateModel();
        $model->handle = (string) $template->getPageTemplateHandle();
        $model->name = (string) $template->getPageTemplateName();
        $model->package = (string) $template->getPackageHandle();

        return $model->jsonSerialize();
    }
}
