<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Attribute\Category;

use Concrete\Core\Api\Attribute\Category;
use Concrete\Core\Api\Model\AttributeCategory as AttributeCategoryModel;
use Concrete\Core\Attribute\Category\CategoryInterface;
use Concrete\Core\Entity\Attribute\Category as CategoryEntity;
use Concrete\Core\Entity\Attribute\Key\Key;
use Doctrine\ORM\EntityManagerInterface;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * What the API does with a category of attribute keys: a category that needs more than the defaults
 * comes with a class of its own extending this one, and hands it over through getApiHandler().
 *
 * @see \Concrete\Core\Attribute\Category\CategoryInterface
 */
class ApiHandler
{
    /**
     * @var \Concrete\Core\Attribute\Category\CategoryInterface
     */
    protected $category;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(CategoryInterface $category, ?EntityManagerInterface $entityManager = null)
    {
        $this->category = $category;
        $this->entityManager = $entityManager ?? app(EntityManagerInterface::class);
    }

    /**
     * Get the handler of a category, whatever that category knows about the API.
     */
    public static function forCategory(CategoryInterface $category): self
    {
        // every category of the core hands one over, a category of a package out there may not
        return method_exists($category, 'getApiHandler') ? $category->getApiHandler() : new self($category);
    }

    /**
     * Get what the keys of this category belong to, which tells a client where it writes them.
     *
     * @return string an empty string for a category that says nothing about itself
     */
    public function getCategoryDescription(): string
    {
        return '';
    }

    /**
     * Get the sets of keys a client asks this category about, which is the category itself unless
     * its keys belong to something else.
     *
     * @return \Concrete\Core\Api\Attribute\Category[]
     */
    public function getApiCategories(): array
    {
        $row = $this->getCategoryEntity();
        if ($row === null) {
            return [];
        }

        return [
            new Category(
                $this,
                (string) $row->getAttributeKeyCategoryHandle(),
                (string) $row->getPackageHandle(),
                $this->getCategoryDescription()
            ),
        ];
    }

    /**
     * Get the keys of one of these sets, leaving out the ones the core keeps for itself.
     *
     * @return \Concrete\Core\Entity\Attribute\Key\Key[]
     */
    public function getApiKeys(Category $category): array
    {
        $row = $this->getCategoryEntity();
        if ($row === null) {
            return [];
        }

        return $this->entityManager->getRepository(Key::class)->findBy(
            ['category' => $row, 'akIsInternal' => false],
            ['akHandle' => 'ASC']
        );
    }

    /**
     * Create the model a client reads of one of these sets of keys.
     */
    public function createApiCategoryModel(Category $category): AttributeCategoryModel
    {
        return new AttributeCategoryModel();
    }

    /**
     * Get the row that defines the category, which the handle, the package and the keys come from.
     *
     * @return \Concrete\Core\Entity\Attribute\Category|null NULL for a category that answers for no
     *                                                       row: its interface doesn't promise one, and a category built without it has none. The
     *                                                       API then serves no set of keys for it.
     */
    protected function getCategoryEntity(): ?CategoryEntity
    {
        return method_exists($this->category, 'getCategoryEntity') ? $this->category->getCategoryEntity() : null;
    }
}
