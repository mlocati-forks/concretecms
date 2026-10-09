<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Attribute\Category;

use Concrete\Core\Api\Attribute\Category;
use Concrete\Core\Attribute\Category\CategoryService;

defined('C5_EXECUTE') or die('Access Denied.');

class Service
{
    /**
     * @var \Concrete\Core\Attribute\Category\CategoryService
     */
    private $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * @return \Concrete\Core\Api\Attribute\Category[] the sets of keys a client may ask about
     */
    public function getCategories(): array
    {
        $categories = [];
        foreach ($this->categoryService->getList() as $category) {
            $handler = ApiHandler::forCategory($category->getController());
            $categories = array_merge($categories, $handler->getApiCategories());
        }

        return $categories;
    }

    public function getCategory(string $handle): ?Category
    {
        foreach ($this->getCategories() as $category) {
            if ($category->getHandle() === $handle) {
                return $category;
            }
        }

        return null;
    }
}
