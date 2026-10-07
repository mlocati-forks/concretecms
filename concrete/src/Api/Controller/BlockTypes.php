<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Block\BlockTypeCatalog;
use Concrete\Core\Api\Fractal\Transformer\BlockTypeTransformer;
use Concrete\Core\Api\Resources;
use League\Fractal\Resource\Collection;

defined('C5_EXECUTE') or die('Access Denied.');

class BlockTypes extends ApiController
{
    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/block_types",
     *     tags={"block_types"},
     *     operationId="getBlockTypes",
     *     summary="List the block types to work with, with the schema of the value they accept",
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
     *                 @OA\Items(ref="#/components/schemas/BlockType")
     *             )
     *         ),
     *     ),
     * )
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function listBlockTypes()
    {
        $blockTypes = $this->app->make(BlockTypeCatalog::class)->getList();

        return new Collection($blockTypes, new BlockTypeTransformer(), Resources::RESOURCE_BLOCK_TYPES);
    }

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/block_types/{blockTypeHandle}",
     *     tags={"block_types"},
     *     operationId="getBlockTypeByHandle",
     *     summary="Find a block type by its handle, with the schema of the value it accepts",
     *     security={
     *         {"clientCredentials": {"definitions:read"}},
     *         {"authorization": {"definitions:read"}}
     *     },
     *     @OA\Parameter(
     *         name="blockTypeHandle",
     *         in="path",
     *         description="Handle of the block type to return",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/BlockType")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="This installation has no such block type, or the CMS writes the blocks of this one by itself"
     *     ),
     * )
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function read($blockTypeHandle)
    {
        $blockType = $this->app->make(BlockTypeCatalog::class)->getByHandle((string) $blockTypeHandle);
        if ($blockType === null) {
            return $this->error(t('Block type not found'), 404);
        }

        return $this->transform($blockType, new BlockTypeTransformer(), Resources::RESOURCE_BLOCK_TYPES);
    }
}
