<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeValue;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeValueUserSelector",
 *     type="object",
 *     title="The user a value of a user_selector attribute names",
 *     description="A read hands it over wrapped in a data property, the way an include is, while a write takes what the specification declares for the key, or any object that carries the same id"
 * )
 */
class UserSelector
{
    /**
     * @OA\Property(ref="#/components/schemas/User")
     */
    public $data;
}
