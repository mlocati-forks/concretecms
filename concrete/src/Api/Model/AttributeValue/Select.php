<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeValue;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeValueSelect",
 *     type="object",
 *     title="The options a value of a select attribute picks",
 *     description="A read hands it over wrapped in a data property, the way an include is, while a write takes what the specification declares for the key, or any object that carries the same id"
 * )
 */
class Select
{
    /**
     * @OA\Property(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/AttributeKeySelectOption")
     * )
     */
    public $data;
}
