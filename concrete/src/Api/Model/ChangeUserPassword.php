<?php

namespace Concrete\Core\Api\Model;

/**
 * @OA\Schema(
 *     title="ChangeUserPassword",
 *     required={"password"}
 * )
 */
class ChangeUserPassword
{
    
    /**
     * @OA\Property(type="string", title="New password")
     */
    private $password;
    
}
