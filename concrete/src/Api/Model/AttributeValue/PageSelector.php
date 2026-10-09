<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeValue;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeValuePageSelector",
 *     type="object",
 *     title="The page a value of a page_selector attribute names",
 *     description="A read hands it over wrapped in a data property, the way an include is, while a write takes what the specification declares for the key"
 * )
 */
class PageSelector
{
    /**
     * @OA\Property(ref="#/components/schemas/Page")
     */
    public $data;
}
