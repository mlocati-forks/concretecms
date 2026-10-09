<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model\AttributeValue;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     schema="AttributeValueAddress",
 *     type="object",
 *     title="The address a value of an address attribute carries"
 * )
 */
class Address
{
    /**
     * @OA\Property(type="string", title="Address 1")
     *
     * @var string
     */
    public $address1;

    /**
     * @OA\Property(type="string", title="Address 2")
     *
     * @var string
     */
    public $address2;

    /**
     * @OA\Property(type="string", title="Address 3")
     *
     * @var string
     */
    public $address3;

    /**
     * @OA\Property(type="string", title="City")
     *
     * @var string
     */
    public $city;

    /**
     * @OA\Property(type="string", title="State/Province Code")
     *
     * @var string
     */
    public $state_province;

    /**
     * @OA\Property(type="string", title="Country Code", description="An ISO 3166-1 alpha-2 code", pattern="^([A-Z]{2})?$")
     *
     * @var string
     */
    public $country;

    /**
     * @OA\Property(type="string", title="Postal Code")
     *
     * @var string
     */
    public $postal_code;
}
