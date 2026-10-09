<?php

namespace Concrete\Core\Api\Attribute;

use Concrete\Core\Attribute\Category\CategoryInterface;

class AttributeValueMapFactory
{

    public function createFromRequestData(CategoryInterface $category, array $body)
    {
        $attributeValueMap = new AttributeValueMap();
        foreach ($body as $key => $data) {
            $attributeKey = $category->getAttributeKeyByHandle($key);
            if ($attributeKey) {
                $handler = AttributeApiHandler::forController($attributeKey->getController());
                $value = $handler->createApiValue($data);
                if ($value) {
                    $entry = new AttributeValueMapEntry($attributeKey, $value);
                    $attributeValueMap->addEntry($entry);
                }
            }
        }
        return $attributeValueMap;
    }


}
