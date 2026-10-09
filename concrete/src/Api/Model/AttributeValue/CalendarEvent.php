<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeValue;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeValueCalendarEvent",
 *     type="object",
 *     title="The event a value of a calendar_event attribute names",
 *     description="A read hands it over wrapped in a data property, the way an include is, while a write takes what the specification declares for the key, or any object that carries the same id"
 * )
 */
class CalendarEvent
{
    /**
     * @OA\Property(ref="#/components/schemas/CalendarEvent")
     */
    public $data;
}
