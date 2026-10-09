<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Fractal\Transformer\AttributeTypeTransformer;
use Concrete\Core\Api\Resources;
use Concrete\Core\Attribute\TypeFactory;
use Concrete\Core\Entity\Attribute\Type;
use League\Fractal\Resource\Collection;

defined('C5_EXECUTE') or die('Access Denied.');

class AttributeTypes extends ApiController
{
    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/attribute_types",
     *     tags={"attribute_types"},
     *     operationId="getAttributeTypes",
     *     summary="List the types an attribute key of this installation can be of",
     *     security={
     *         {"clientCredentials": {"definitions:read"}},
     *         {"authorization": {"definitions:read"}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/AttributeType")
     *             )
     *         ),
     *     ),
     * )
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function listAttributeTypes()
    {
        $types = app(TypeFactory::class)->getList();
        usort($types, static function (Type $a, Type $b) {
            return strcasecmp((string) $a->getAttributeTypeHandle(), (string) $b->getAttributeTypeHandle());
        });

        return new Collection($types, new AttributeTypeTransformer(), Resources::RESOURCE_ATTRIBUTE_TYPES);
    }
}
