<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeCategory;

use Concrete\Core\Api\Model\AttributeCategory;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeCategoryExpress",
 *     type="object",
 *     title="A set of keys belonging to an Express entity",
 *     allOf={@OA\Schema(ref="#/components/schemas/AttributeCategory")}
 * )
 */
class Express extends AttributeCategory
{
    /**
     * @OA\Property(
     *     title="ID of the Express entity holding these attributes",
     *     description="What the Express entities endpoint names an entity by"
     * )
     *
     * @var string
     */
    public $express_entity_id;
}
