<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeKey;

use Concrete\Core\Api\Model\AttributeKey;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeKeyAddress",
 *     type="object",
 *     title="AttributeKeyAddress",
 *     description="A key whose value is an address",
 *     allOf={@OA\Schema(ref="#/components/schemas/AttributeKey")}
 * )
 */
class Address extends AttributeKey
{
    /**
     * @OA\Property(
     *     title="Default country",
     *     description="Country a value of this key starts with, as an ISO 3166-1 alpha-2 code. Empty where the key names none",
     *     pattern="^([A-Z]{2})?$"
     * )
     *
     * @var string
     */
    public $default_country;

    /**
     * @OA\Property(
     *     type="array",
     *     title="Countries",
     *     description="Countries a value of this key is meant to name, as ISO 3166-1 alpha-2 codes. Empty where the key is meant for any country this installation knows, which is every country of CLDR but the ones a package of the site takes out",
     *     @OA\Items(type="string", pattern="^[A-Z]{2}$")
     * )
     *
     * @var string[]
     */
    public $countries;
}
