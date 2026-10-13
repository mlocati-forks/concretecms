<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Fractal\Transformer\BoardInstanceTransformer;
use Concrete\Core\Api\Fractal\Transformer\BoardTransformer;
use Concrete\Core\Api\Resources;
use Concrete\Core\Entity\Board\Board;
use Concrete\Core\Entity\Board\Instance;
use Concrete\Core\Permission\Checker;
use Doctrine\ORM\EntityManagerInterface;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

defined('C5_EXECUTE') or die('Access Denied.');

class Boards extends ApiController
{
    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/boards",
     *     tags={"boards"},
     *     operationId="getBoards",
     *     summary="List the boards of this installation",
     *     security={
     *         {"authorization": {"boards:read"}}
     *     },
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/Board")
     *             )
     *         ),
     *     ),
     * )
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function listBoards()
    {
        $boards = [];
        foreach ($this->getRepository()->findBy([], ['boardName' => 'asc']) as $board) {
            if ($board instanceof Board && $this->canViewBoard($board)) {
                $boards[] = $board;
            }
        }

        return new Collection($boards, new BoardTransformer(), Resources::RESOURCE_BOARDS);
    }

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/boards/{boardID}",
     *     tags={"boards"},
     *     operationId="getBoardById",
     *     summary="Find a board by its ID",
     *     security={
     *         {"authorization": {"boards:read"}}
     *     },
     *     @OA\Parameter(
     *         name="boardID",
     *         in="path",
     *         description="ID of the board to return",
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
     *             @OA\Property(property="data", ref="#/components/schemas/Board")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="This installation has no board of this ID",
     *     ),
     * )
     *
     * @param int|string $boardID
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function read($boardID)
    {
        $board = $this->getBoard($boardID);
        if ($board === null) {
            return $this->error(t('Board not found.'), 404);
        }

        return new Item($board, new BoardTransformer(), Resources::RESOURCE_BOARDS);
    }

    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/boards/{boardID}/instances",
     *     tags={"boards"},
     *     operationId="getBoardInstances",
     *     summary="List the instances of a board, the ones a board block shows",
     *     description="A board is laid out anew every time it is generated, and each of those layouts is an instance: the newest comes first.",
     *     security={
     *         {"authorization": {"boards:read"}}
     *     },
     *     @OA\Parameter(
     *         name="boardID",
     *         in="path",
     *         description="ID of the board holding the instances",
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
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/BoardInstance")
     *             )
     *         ),
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="This installation has no board of this ID",
     *     ),
     * )
     *
     * @param int|string $boardID
     *
     * @return \League\Fractal\Resource\Collection|\Symfony\Component\HttpFoundation\JsonResponse
     */
    public function listInstances($boardID)
    {
        $board = $this->getBoard($boardID);
        if ($board === null) {
            return $this->error(t('Board not found.'), 404);
        }
        $instances = [];
        foreach ($board->getInstances() as $instance) {
            if ($instance instanceof Instance) {
                $instances[] = $instance;
            }
        }

        return new Collection($instances, new BoardInstanceTransformer(), Resources::RESOURCE_BOARD_INSTANCES);
    }

    /**
     * Get the board of an ID, where the request may view it.
     *
     * @param int|string $boardID
     */
    private function getBoard($boardID): ?Board
    {
        $board = $this->getRepository()->findOneBy(['boardID' => (int) $boardID]);
        if (!$board instanceof Board || !$this->canViewBoard($board)) {
            return null;
        }

        return $board;
    }

    private function canViewBoard(Board $board): bool
    {
        $checker = new Checker($board);

        return (bool) $checker->canViewBoard();
    }

    /**
     * @return \Doctrine\ORM\EntityRepository
     */
    private function getRepository()
    {
        return $this->app->make(EntityManagerInterface::class)->getRepository(Board::class);
    }
}
