<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Attribute\Category;
use Concrete\Core\Api\Fractal\Transformer\AttributeCategoryTransformer;
use Concrete\Core\Api\Resources;
use League\Fractal\Resource\Collection;

defined('C5_EXECUTE') or die('Access Denied.');

class AttributeCategories extends ApiController
{
    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/attribute_categories",
     *     tags={"attribute_categories"},
     *     operationId="getAttributeCategories",
     *     summary="List the sets of attribute keys of this installation, which the attribute keys endpoint takes",
     *     security={
     *         {"clientCredentials": {"definitions:read"}},
     *         {"authorization": {"definitions:read"}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation. A set of keys may carry fields beyond these, which the kind of set it is settles: read the ones you know and leave the others alone.",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/AttributeCategory")
     *             )
     *         ),
     *     ),
     * )
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function listAttributeCategories()
    {
        $categories = app(Category\Service::class)->getCategories();

        return new Collection($categories, new AttributeCategoryTransformer(), Resources::RESOURCE_ATTRIBUTE_CATEGORIES);
    }
}
