<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Attribute\Category;
use Concrete\Core\Api\Model\AttributeCategory as AttributeCategoryModel;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class AttributeCategoryTransformer extends TransformerAbstract
{
    /**
     * @return array<string,mixed>
     */
    public function transform(Category $category): array
    {
        $model = $category->getApiHandler()->createApiCategoryModel($category);
        $model->handle = $category->getHandle();
        $model->description = $category->getDescription();
        $model->package = $category->getPackageHandle();

        return $model->jsonSerialize();
    }
}
