<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeKey;

use Concrete\Core\Api\Model\AttributeKey;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeKeyExpress",
 *     type="object",
 *     title="A key whose value names an Express entry",
 *     allOf={@OA\Schema(ref="#/components/schemas/AttributeKey")}
 * )
 */
class Express extends AttributeKey
{
    /**
     * @OA\Property(
     *     title="ID of the Express entity whose entries a value of this key names",
     *     description="Empty where the entity is gone"
     * )
     *
     * @var string
     */
    public $express_entity_id;
}
