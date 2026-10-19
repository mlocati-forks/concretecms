<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="UpdatedArea",
 *     type="object",
 *     title="UpdatedArea",
 *     description="An area of a page, as a client writes it."
 * )
 */
class UpdatedArea
{
    /**
     * @OA\Property(ref="#/components/schemas/CustomStyle", nullable=true, title="Custom style to give it beyond what the theme of the page does", description="NULL, or a custom style with nothing in it that holds, leaves it to the theme alone. Leave it out to keep the one the area has.")
     *
     * @var \Concrete\Core\Api\Model\CustomStyle|null
     */
    private $custom_style;
}
