<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Fractal\Transformer\PageTemplateTransformer;
use Concrete\Core\Api\Resources;
use Concrete\Core\Page\Template as PageTemplate;
use League\Fractal\Resource\Collection;

defined('C5_EXECUTE') or die('Access Denied.');

class PageTemplates extends ApiController
{
    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/page_templates",
     *     tags={"page_templates"},
     *     operationId="getPageTemplates",
     *     summary="List the page templates that a page can be given, which the page types endpoint tells apart",
     *     security={
     *         {"clientCredentials": {"definitions:read"}},
     *         {"authorization": {"definitions:read"}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/PageTemplate")
     *         ),
     *     ),
     * )
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function listPageTemplates()
    {
        // the internal page templates are the ones that no page of the site is ever given
        return new Collection(PageTemplate::getList(), new PageTemplateTransformer(), Resources::RESOURCE_PAGE_TEMPLATES);
    }
}
