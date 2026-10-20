<?php

namespace Concrete\Core\Api\Model;

/**
 * @OA\Schema(
 *     title="Calendar",
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
     *     type="integer",
     *     format="int64",
     *     nullable=true,
     *     title="Site ID",
     *     description="ID of the site the calendar belongs to, NULL for a calendar that belongs to every site of this installation"
     * )
     *
     * @var int|null
     */
    private $site_id;




}