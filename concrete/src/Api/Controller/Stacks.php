<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Fractal\Transformer\StackTransformer;
use Concrete\Core\Api\Resources;
use Concrete\Core\Page\Stack\Stack;
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
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Stack")
     *             )
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

        return new Collection($list->getResults(), $this->createTransformer(), Resources::RESOURCE_STACKS);
    }

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/stacks/{stackID}",
     *     tags={"stacks"},
     *     operationId="getStackById",
     *     summary="Find a stack by the ID of the page holding it",
     *     security={
     *         {"clientCredentials": {"stacks:read"}},
     *         {"authorization": {"stacks:read"}}
     *     },
     *     @OA\Parameter(
     *         name="stackID",
     *         in="path",
     *         description="ID of the stack to return, or of one of its localized versions, which answers with the stack those belong to",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="include_contents",
     *         in="query",
     *         description="Whether the blocks of the stack and of its localized versions travel with it (default: false)",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", ref="#/components/schemas/Stack")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="No stack of this installation has this ID",
     *     ),
     * )
     *
     * @param int|string $stackID
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function read($stackID)
    {
        $stack = Stack::getByID((int) $stackID);
        if (!$stack instanceof Stack || $stack->isError()) {
            return $this->error(t('Stack not found.'), 404);
        }
        if (!$stack->isNeutralStack()) {
            // a localized version belongs to the stack it was made from, which is what a client works with
            $stack = $stack->getNeutralStack();
            if ($stack === null) {
                return $this->error(t('Stack not found.'), 404);
            }
        }
        // the stack type comes from the database as a string
        if ((int) $stack->getStackType() === Stack::ST_TYPE_GLOBAL_AREA) {
            return $this->error(t('Stack not found.'), 404);
        }

        return $this->transform($stack, $this->createTransformer(), Resources::RESOURCE_STACKS);
    }

    private function createTransformer(): StackTransformer
    {
        return new StackTransformer($this->request->query->getBoolean('include_contents', false));
    }
}
