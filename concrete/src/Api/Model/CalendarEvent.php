<?php

namespace Concrete\Core\Api\Model;

/**
 * @OA\Schema(
 *     title="CalendarEvent model",
 * )
 */
class CalendarEvent
{

    /**
     * @OA\Property(type="integer", title="ID")
     *
     * @var string
     */
    private $id;

    /**
     * @OA\Property(type="string", format="string", title="Event Name")
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(
     *     type="object",
     *     title="Version of the event this answer is about, which every answer carries",
     *     @OA\Property(
     *         property="data",
     *         ref="#/components/schemas/CalendarEventVersion"
     *     )
     * )
     *
     * @var array
     */
    private $version;

    /**
     * @OA\Property(
     *     type="object",
     *     title="Custom attributes of the event, where the includes parameter asks for them",
     *     @OA\Property(
     *         property="data",
     *         type="array",
     *         @OA\Items(ref="#/components/schemas/CustomAttribute")
     *     )
     * )
     *
     * @var array
     */
    private $custom_attributes;


}