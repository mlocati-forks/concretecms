<?php

namespace Concrete\Core\Api\Attribute;

/**
 * @deprecated an attribute type reads a value received by the API through its API handler
 *
 * @see \Concrete\Core\Api\Attribute\AttributeApiHandler
 */
interface SupportsAttributeValueFromJsonInterface
{

    /**
     * Could be a string, could be an array representation of a more complex request body object
     * @param mixed $json
     * @return mixed
     */
    public function createAttributeValueFromNormalizedJson($json);


}