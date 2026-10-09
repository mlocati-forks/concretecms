<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeValue;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeValueSite",
 *     type="object",
 *     title="The site a value of a site attribute names",
 *     description="A read hands it over wrapped in a data property, the way an include is, while a write takes what the specification declares for the key, or any object that carries the same id"
 * )
 */
class Site
{
    /**
     * @OA\Property(ref="#/components/schemas/Site")
     */
    public $data;
}
