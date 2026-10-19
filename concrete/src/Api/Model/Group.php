<?php

namespace Concrete\Core\Api\Model;

/**
 * @OA\Schema(
 *     schema="Group",
 *     title="Group",
 *     description="A group of users of this site, with the ID it is named by",
 *     type="object",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/NewGroup"),
 *     }
 * )
 */

class Group
{

    /**
     * @OA\Property(type="integer", title="ID")
     *
     * @var string
     */
    private $id;


}
