<?php

namespace Concrete\Core\Api\Model;

/**
 * @OA\Schema(
 *     title="NewPage model",
 *     description="A Concrete Page being added",
 *     required={"parent", "name", "type", "template"},
 *     allOf={@OA\Schema(ref="#/components/schemas/UpdatedPage")}
 * )
 */
class NewPage
{


    /**
     * @OA\Property(type="integer", title="ID")
     *
     * @var string
     */
    private $parent;




}
