<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Fractal\Transformer\PageThemeTransformer;
use Concrete\Core\Api\Resources;
use Concrete\Core\Page\Theme\Theme as PageTheme;
use League\Fractal\Resource\Collection;

defined('C5_EXECUTE') or die('Access Denied.');

class PageThemes extends ApiController
{
    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/page_themes",
     *     tags={"page_themes"},
     *     operationId="getPageThemes",
     *     summary="List the page themes installed in this site, with the grid framework that sizes the columns of their layouts",
     *     security={
     *         {"clientCredentials": {"definitions:read"}},
     *         {"authorization": {"definitions:read"}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/PageTheme")
     *         ),
     *     ),
     * )
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function listPageThemes()
    {
        $transformer = $this->app->make(PageThemeTransformer::class);

        return new Collection(PageTheme::getList(), $transformer, Resources::RESOURCE_PAGE_THEMES);
    }

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/page_themes/{pageThemeHandle}",
     *     tags={"page_themes"},
     *     operationId="getPageThemeByHandle",
     *     summary="Find a page theme by its handle, the one a page answers with",
     *     security={
     *         {"clientCredentials": {"definitions:read"}},
     *         {"authorization": {"definitions:read"}}
     *     },
     *     @OA\Parameter(
     *         name="pageThemeHandle",
     *         in="path",
     *         description="Handle of the page theme to return",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/PageTheme"),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No page theme of this handle is installed in this site",
     *     ),
     * )
     *
     * @param string $pageThemeHandle
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function read($pageThemeHandle)
    {
        $theme = PageTheme::getByHandle((string) $pageThemeHandle);
        if ($theme === null) {
            return $this->error(t('Page theme not found.'), 404);
        }

        return $this->transform($theme, $this->app->make(PageThemeTransformer::class), Resources::RESOURCE_PAGE_THEMES);
    }
}
