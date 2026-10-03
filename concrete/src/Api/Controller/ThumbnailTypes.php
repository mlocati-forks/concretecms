<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Fractal\Transformer\ThumbnailTypeTransformer;
use Concrete\Core\Api\Resources;
use Concrete\Core\File\Image\Thumbnail\Type\Type as ThumbnailTypeService;
use League\Fractal\Resource\Collection;

defined('C5_EXECUTE') or die('Access Denied.');

class ThumbnailTypes extends ApiController
{
    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/thumbnail_types",
     *     tags={"thumbnail_types"},
     *     operationId="getThumbnailTypes",
     *     summary="List the thumbnail types of this installation, the sizes it keeps every image at",
     *     security={
     *         {"clientCredentials": {"definitions:read"}},
     *         {"authorization": {"definitions:read"}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/ThumbnailType")
     *         ),
     *     ),
     * )
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function listThumbnailTypes()
    {
        return new Collection(ThumbnailTypeService::getList(), new ThumbnailTypeTransformer(), Resources::RESOURCE_THUMBNAIL_TYPES);
    }

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/thumbnail_types/{thumbnailTypeID}",
     *     tags={"thumbnail_types"},
     *     operationId="getThumbnailTypeById",
     *     summary="Find a thumbnail type by its ID, the one the thumbnails of an image block are given",
     *     security={
     *         {"clientCredentials": {"definitions:read"}},
     *         {"authorization": {"definitions:read"}}
     *     },
     *     @OA\Parameter(
     *         name="thumbnailTypeID",
     *         in="path",
     *         description="ID of the thumbnail type to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/ThumbnailType"),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="This installation has no thumbnail type of this ID",
     *     ),
     * )
     *
     * @param int|string $thumbnailTypeID
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function read($thumbnailTypeID)
    {
        $thumbnailType = ThumbnailTypeService::getByID((int) $thumbnailTypeID);
        if ($thumbnailType === null) {
            return $this->error(t('Thumbnail type not found.'), 404);
        }

        return $this->transform($thumbnailType, new ThumbnailTypeTransformer(), Resources::RESOURCE_THUMBNAIL_TYPES);
    }
}
