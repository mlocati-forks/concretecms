<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeValue;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeValueSocialLinks",
 *     type="object",
 *     title="The links a value of a social_links attribute carries",
 *     description="A read hands it over wrapped in a data property, the way an include is, while a write takes what the specification declares for the key, or this shape as it came"
 * )
 */
class SocialLinks
{
    /**
     * @OA\Property(
     *     type="array",
     *     @OA\Items(ref="#/components/schemas/SocialLink")
     * )
     */
    public $data;
}
