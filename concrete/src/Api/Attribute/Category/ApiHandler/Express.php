<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Attribute\Category\ApiHandler;

use Concrete\Core\Api\Attribute\Category;
use Concrete\Core\Api\Attribute\Category\ApiHandler;
use Concrete\Core\Api\Express\EntityAccess;
use Concrete\Core\Api\Model\AttributeCategory as AttributeCategoryModel;
use Concrete\Core\Attribute\Category\ExpressCategory;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The keys of the Express category belong to one entity each, so a client asks for them by entity.
 */
class Express extends ApiHandler
{
    /**
     * @var \Concrete\Core\Api\Express\EntityAccess
     */
    private $entityAccess;

    public function __construct(ExpressCategory $category, ?EntityAccess $entityAccess = null)
    {
        parent::__construct($category);
        $this->entityAccess = $entityAccess ?? app(EntityAccess::class);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Attribute\Category\ApiHandler::getApiCategories()
     */
    public function getApiCategories(): array
    {
        $categories = [];
        foreach ($this->entityAccess->getEntities() as $entity) {
            $categories[] = new Category\Express($this, $entity);
        }

        return $categories;
    }

    /**
     * {@inheritdoc}
     *
     * @param \Concrete\Core\Api\Attribute\Category\Express $category
     *
     * @see \Concrete\Core\Api\Attribute\Category\ApiHandler::createApiCategoryModel()
     */
    public function createApiCategoryModel(Category $category): AttributeCategoryModel
    {
        $model = new AttributeCategoryModel\Express();
        $model->express_entity_id = (string) $category->getEntity()->getId();

        return $model;
    }
}
