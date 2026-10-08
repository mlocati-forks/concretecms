<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Express\EntityAccess;
use Concrete\Core\Api\Fractal\Transformer\ExpressEntityTransformer;
use Concrete\Core\Api\Resources;
use League\Fractal\Resource\Collection;

defined('C5_EXECUTE') or die('Access Denied.');

class ExpressEntities extends ApiController
{
    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/express_entities",
     *     tags={"express_entities"},
     *     operationId="getExpressEntities",
     *     summary="List the Express entities whose entries this API serves",
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
     *                 @OA\Items(ref="#/components/schemas/ExpressEntity")
     *             )
     *         ),
     *     ),
     * )
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function listExpressEntities()
    {
        $entities = app(EntityAccess::class)->getEntities();

        return new Collection($entities, new ExpressEntityTransformer(), Resources::RESOURCE_EXPRESS_ENTITIES);
    }

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/express_entities/{entityID}",
     *     tags={"express_entities"},
     *     operationId="getExpressEntityById",
     *     summary="Find an Express entity by its ID, the one a block displaying its entries names",
     *     security={
     *         {"clientCredentials": {"definitions:read"}},
     *         {"authorization": {"definitions:read"}}
     *     },
     *     @OA\Parameter(
     *         name="entityID",
     *         in="path",
     *         description="ID of the entity to return",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/ExpressEntity")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="You do not have access to the entries of this entity",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="This API serves no entity of this ID",
     *     ),
     * )
     *
     * @param string $entityID
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function read($entityID)
    {
        $access = app(EntityAccess::class);
        $entity = $access->getEntity((string) $entityID);
        if ($entity === null || !$access->isServed($entity)) {
            return $this->error(t('Entity not found.'), 404);
        }
        if (!$access->canViewEntries($entity)) {
            return $this->error(t('You do not have access to the entries of %s.', $entity->getName()), 403);
        }

        return $this->transform($entity, new ExpressEntityTransformer(), Resources::RESOURCE_EXPRESS_ENTITIES);
    }
}
