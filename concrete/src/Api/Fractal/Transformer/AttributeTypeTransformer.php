<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Api\Model\AttributeType as AttributeTypeModel;
use Concrete\Core\Entity\Attribute\Category;
use Concrete\Core\Entity\Attribute\Type;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class AttributeTypeTransformer extends TransformerAbstract
{
    /**
     * @return array<string,mixed>
     */
    public function transform(Type $type): array
    {
        $model = new AttributeTypeModel();
        $model->handle = (string) $type->getAttributeTypeHandle();
        $model->name = (string) $type->getAttributeTypeDisplayName('text');
        $model->package = (string) $type->getPackageHandle();
        $model->categories = $this->getCategories($type);
        $model->key_schema = AttributeApiHandler::forController($type->getController())->getApiKeySchema();

        return $model->jsonSerialize();
    }

    /**
     * @return string[]
     */
    protected function getCategories(Type $type): array
    {
        $handles = [];
        foreach ($type->getAttributeCategories() as $category) {
            if ($category instanceof Category) {
                $handles[] = (string) $category->getAttributeKeyCategoryHandle();
            }
        }
        sort($handles);

        return $handles;
    }
}
