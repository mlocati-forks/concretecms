<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\File\SetAccess;
use Concrete\Core\Api\Fractal\Transformer\FileSetTransformer;
use Concrete\Core\Api\Resources;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

defined('C5_EXECUTE') or die('Access Denied.');

class FileSets extends ApiController
{
    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/file_sets",
     *     tags={"file_sets"},
     *     operationId="getFileSets",
     *     summary="List the file sets this request may use",
     *     description="The sets of the site come back along with the ones of the user the request carries, by name. The files of a set are read with GET /files, naming the set in its set parameter.",
     *     security={
     *         {"authorization": {"files:read"}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/FileSet")
     *             )
     *         ),
     *     ),
     * )
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function listFileSets()
    {
        return new Collection($this->getAccess()->getSets(), new FileSetTransformer(), Resources::RESOURCE_FILE_SETS);
    }

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/file_sets/{fileSetID}",
     *     tags={"file_sets"},
     *     operationId="getFileSetById",
     *     summary="Find a file set by its ID",
     *     security={
     *         {"authorization": {"files:read"}}
     *     },
     *     @OA\Parameter(
     *         name="fileSetID",
     *         in="path",
     *         description="ID of the file set to return",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/FileSet")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="This request has no file set of this ID",
     *     ),
     * )
     *
     * @param int|string $fileSetID
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function read($fileSetID)
    {
        $set = $this->getAccess()->getSet($fileSetID);
        if ($set === null) {
            return $this->error(t('File set not found.'), 404);
        }

        return new Item($set, new FileSetTransformer(), Resources::RESOURCE_FILE_SETS);
    }

    private function getAccess(): SetAccess
    {
        return $this->app->make(SetAccess::class);
    }
}
