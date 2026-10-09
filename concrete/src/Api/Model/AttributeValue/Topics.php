<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeValue;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeValueTopics",
 *     type="object",
 *     title="The topics a value of a topics attribute names",
 *     description="A read hands it over wrapped in a data property, the way an include is, while a write takes what the specification declares for the key"
 * )
 */
class Topics
{
    /**
     * @OA\Property(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/TopicTreeNode")
     * )
     */
    public $data;
}
