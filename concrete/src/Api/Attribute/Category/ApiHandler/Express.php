<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Attribute\Category\ApiHandler;

use Concrete\Core\Api\Attribute\Category;
use Concrete\Core\Api\Attribute\Category\ApiHandler;
use Concrete\Core\Api\Express\EntityAccess;
use Concrete\Core\Api\Model\AttributeCategory as AttributeCategoryModel;
use Concrete\Core\Attribute\Category\ExpressCategory;
use Concrete\Core\Entity\Attribute\Key\ExpressKey;
use Doctrine\ORM\EntityManagerInterface;

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

    public function __construct(ExpressCategory $category, ?EntityAccess $entityAccess = null, ?EntityManagerInterface $entityManager = null)
    {
        parent::__construct($category, $entityManager);
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
     * The keys of a set belong to its entity, so they are looked for by entity and not by category.
     *
     * @param \Concrete\Core\Api\Attribute\Category\Express $category
     *
     * @see \Concrete\Core\Api\Attribute\Category\ApiHandler::getApiKeys()
     */
    public function getApiKeys(Category $category): array
    {
        return $this->entityManager->getRepository(ExpressKey::class)->findBy(
            ['entity' => $category->getEntity(), 'akIsInternal' => false],
            ['akHandle' => 'ASC']
        );
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
