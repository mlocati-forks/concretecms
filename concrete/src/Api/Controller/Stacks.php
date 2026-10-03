<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Fractal\Transformer\StackTransformer;
use Concrete\Core\Api\Resources;
use Concrete\Core\Page\Stack\StackList;
use League\Fractal\Resource\Collection;

defined('C5_EXECUTE') or die('Access Denied.');

class Stacks extends ApiController
{
    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/stacks",
     *     tags={"stacks"},
     *     operationId="getStacks",
     *     summary="List the stacks of the site, the sets of blocks that a page shows where a core_stack_display block puts them",
     *     security={
     *         {"clientCredentials": {"stacks:read"}},
     *         {"authorization": {"stacks:read"}}
     *     },
     *     @OA\Parameter(
     *         name="include_contents",
     *         in="query",
     *         description="Whether the blocks of every stack and of its localized versions travel with them (default: false)",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Stack")
     *         ),
     *     ),
     * )
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function listStacks()
    {
        $list = new StackList();
        $list
            ->setIncludeStacks(true)
            ->setIncludeFolders(false)
            ->setIncludeGlobalAreas(false)
        ;

        $transformer = new StackTransformer($this->request->query->getBoolean('include_contents', false));

        return new Collection($list->getResults(), $transformer, Resources::RESOURCE_STACKS);
    }
}
