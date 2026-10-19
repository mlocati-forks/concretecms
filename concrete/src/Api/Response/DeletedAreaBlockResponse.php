<?php

namespace Concrete\Core\Api\Response;

/**
 * @OA\Schema(
 *     title="DeletedAreaBlockResponse",
 *     description="What deleting a block of an area answers: the block is gone, and the version of the page the request worked on",
 * )
 */
class DeletedAreaBlockResponse extends DeletedResponse
{

    /**
     * @OA\Property(title="Version", ref="#/components/schemas/PageVersion")
     *
     * @var string
     */
    private $version;


}