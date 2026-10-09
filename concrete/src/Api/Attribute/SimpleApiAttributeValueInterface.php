<?php

namespace Concrete\Core\Api\Attribute;

use League\Fractal\TransformerAbstract;

/**
 * @deprecated an attribute type hands a value over through its API handler
 *
 * @see \Concrete\Core\Api\Attribute\AttributeApiHandler
 */
interface SimpleApiAttributeValueInterface
{

    /**
     * @return mixed
     */
    public function getApiAttributeValue();


}