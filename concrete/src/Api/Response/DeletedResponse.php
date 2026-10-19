<?php

namespace Concrete\Core\Api\Response;

/**
 * @OA\Schema(
 *     title="DeletedResponse",
 *     description="What a delete answers: which thing was deleted, and that it is gone",
 * )
 */
class DeletedResponse
{

    /**
     * @OA\Property(title="ID")
     *
     * @var string
     */
    private $id;

    /**
     * @OA\Property(
     *     type="string",
     *     title="Object Type",
     * )
     *
     * @var string
     */
    private $object;

    /**
     * @OA\Property(
     *     type="boolean",
     *     title="Deleted?",
     * )
     *
     * @var string
     */
    private $deleted;


}