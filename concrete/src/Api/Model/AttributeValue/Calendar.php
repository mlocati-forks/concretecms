<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeValue;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeValueCalendar",
 *     type="object",
 *     title="AttributeValueCalendar",
 *     description="The calendar a value of a calendar attribute names. A read hands it over wrapped in a data property, the way an include is, while a write takes what the specification declares for the key, or any object that carries the same id"
 * )
 */
class Calendar
{
    /**
     * @OA\Property(ref="#/components/schemas/Calendar")
     */
    public $data;
}
