<?php
namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Entity\Attribute\Value\AbstractValue;
use League\Fractal\TransformerAbstract;

class AttributeValueTransformer extends TransformerAbstract
{

    public function transform(AbstractValue $value)
    {
        $key = $value->getAttributeKey();
        $type = $value->getAttributeTypeObject();
        if ($key && $type) {
            return [
                'id' => $value->getAttributeValueID(),
                'type' => $type->getAttributeTypeHandle(),
                'handle' => $key->getAttributeKeyHandle(),
                'value' => AttributeApiHandler::forController($value->getController())->getApiValue(),
            ];
        }
    }

}
