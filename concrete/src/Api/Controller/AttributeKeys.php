<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Attribute\Category;
use Concrete\Core\Api\Fractal\Transformer\AttributeKeyTransformer;
use Concrete\Core\Api\Resources;
use League\Fractal\Resource\Collection;

defined('C5_EXECUTE') or die('Access Denied.');

class AttributeKeys extends ApiController
{
    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/attribute_categories/{category}/keys",
     *     tags={"attribute_keys"},
     *     operationId="getAttributeKeys",
     *     summary="List the attribute keys of a set, with the options their values are picked out of",
     *     security={
     *         {"clientCredentials": {"definitions:read"}},
     *         {"authorization": {"definitions:read"}}
     *     },
     *     @OA\Parameter(
     *         name="category",
     *         in="path",
     *         description="Handle of the set of keys, as the attribute categories endpoint names it",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/AttributeKey")
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="This installation has no set of keys of this handle",
     *     ),
     * )
     *
     * @param string $category
     *
     * @return \League\Fractal\Resource\Collection|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function listAttributeKeys($category)
    {
        $keyCategory = app(Category\Service::class)->getCategory((string) $category);
        if ($keyCategory === null) {
            return $this->error(t('Attribute category not found.'), 404);
        }
        $keys = $keyCategory->getApiHandler()->getApiKeys($keyCategory);

        return new Collection($keys, new AttributeKeyTransformer(), Resources::RESOURCE_ATTRIBUTE_KEYS);
    }
}
