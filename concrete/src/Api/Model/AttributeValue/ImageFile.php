<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeValue;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeValueImageFile",
 *     type="object",
 *     title="The file a value of an image_file attribute names",
 *     description="A read hands it over wrapped in a data property, the way an include is, while a write takes what the specification declares for the key, or any object that carries the same id"
 * )
 */
class ImageFile
{
    /**
     * @OA\Property(ref="#/components/schemas/File")
     */
    public $data;
}
