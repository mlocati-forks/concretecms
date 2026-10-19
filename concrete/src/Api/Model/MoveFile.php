<?php

namespace Concrete\Core\Api\Model;

/**
 * @OA\Schema(
 *     title="MoveFile",
 *     required={"folder"}
 * )
 */
class MoveFile
{
    
    /**
     * @OA\Property(type="integer", format="int64", title="Folder")
     */
    private $folder;
    
}
