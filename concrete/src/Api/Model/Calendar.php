<?php

namespace Concrete\Core\Api\Model;

/**
 * @OA\Schema(
 *     title="Calendar model",
 * )
 */
class Calendar
{

    /**
     * @OA\Property(type="integer", title="ID")
     *
     * @var string
     */
    private $id;

    /**
     * @OA\Property(type="string", format="string", title="Calendar Name")
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(
     *     type="object",
     *     title="Site the calendar belongs to, where the includes parameter asks for it",
     *     @OA\Property(
     *         property="data",
     *         ref="#/components/schemas/Site"
     *     )
     * )
     *
     * @var array
     */
    private $site;




}