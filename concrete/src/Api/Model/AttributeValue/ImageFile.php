<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeValue;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeValueImageFile",
 *     type="object",
 *     title="AttributeValueImageFile",
 *     description="The file a value of an image_file attribute names. A read hands it over wrapped in a data property, the way an include is. A write takes the UUID of the file, or its ID when the file was added before UUIDs existed, and takes as well any object carrying one of the two: so the file a read handed over goes back as it came"
 * )
 */
class ImageFile
{
    /**
     * @OA\Property(ref="#/components/schemas/File")
     */
    public $data;
}
