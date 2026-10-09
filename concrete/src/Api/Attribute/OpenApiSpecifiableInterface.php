<?php

namespace Concrete\Core\Api\Attribute;

use Concrete\Core\Api\OpenApi\SpecProperty;
use Concrete\Core\Entity\Attribute\Key\Key;

/**
 * @deprecated an attribute type says what a value of it looks like in the specification through
 *             its API handler
 *
 * @see \Concrete\Core\Api\Attribute\AttributeApiHandler
 */
interface OpenApiSpecifiableInterface
{

    public function getOpenApiSpecProperty(Key $key): SpecProperty;


}