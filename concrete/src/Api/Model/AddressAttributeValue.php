<?php

namespace Concrete\Core\Api\Model;

/**
 * @deprecated
 *
 * @see \Concrete\Core\Api\Model\AttributeValue\Address
 *
 * @OA\Schema(
 *     schema="AddressAttributeValue",
 *     deprecated=true,
 *     title="AddressAttributeValue",
 *     description="The address a value of an address attribute carries. Deprecated: read AttributeValueAddress instead",
 *     allOf={@OA\Schema(ref="#/components/schemas/AttributeValueAddress")}
 * )
 */
class AddressAttributeValue
{
}
