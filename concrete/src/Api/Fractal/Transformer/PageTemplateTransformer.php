<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Entity\Page\Template;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class PageTemplateTransformer extends TransformerAbstract
{
    /**
     * Get what the API hands to its clients for a page template.
     *
     * @return array<string,mixed>
     */
    public function transform(Template $template): array
    {
        return [
            'handle' => (string) $template->getPageTemplateHandle(),
            'name' => (string) $template->getPageTemplateName(),
            'package' => (string) $template->getPackageHandle(),
        ];
    }
}
