<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Fractal\Transformer\PageTypeTransformer;
use Concrete\Core\Api\Resources;
use Concrete\Core\Page\Type\Type as PageType;
use League\Fractal\Resource\Collection;

defined('C5_EXECUTE') or die('Access Denied.');

class PageTypes extends ApiController
{
    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/page_types",
     *     tags={"page_types"},
     *     operationId="getPageTypes",
     *     summary="List the page types that a new page can be given, with the page templates each of them allows",
     *     security={
     *         {"clientCredentials": {"definitions:read"}},
     *         {"authorization": {"definitions:read"}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/PageType")
     *         ),
     *     ),
     * )
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function listPageTypes()
    {
        // the internal page types are the ones that no page of the site is ever given
        return new Collection(PageType::getList(), new PageTypeTransformer(), Resources::RESOURCE_PAGE_TYPES);
    }
}
