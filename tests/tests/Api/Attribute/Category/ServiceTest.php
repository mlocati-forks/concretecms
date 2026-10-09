<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Attribute\Category;

use Concrete\Core\Api\Attribute\Category;
use Concrete\Core\Api\Attribute\Category\ApiHandler;
use Concrete\Core\Api\Attribute\Category\Service;
use Concrete\Core\Attribute\Category\CategoryInterface;
use Concrete\Core\Attribute\Category\CategoryService;
use Concrete\Core\Attribute\Category\PageCategory;
use Concrete\Core\Entity\Attribute\Category as CategoryEntity;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The sets of keys a client may ask about are the ones the categories of this installation serve.
 *
 * @see \Concrete\Core\Api\Attribute\Category\Service
 */
class ServiceTest extends TestCase
{
    public function testEveryCategoryServesTheSetsOfKeysItWants(): void
    {
        $service = $this->createService([
            'collection' => ['collection'],
            'express' => ['express@7b2', 'express@9f4'],
        ]);

        static::assertSame(['collection', 'express@7b2', 'express@9f4'], $this->getHandles($service));
    }

    public function testASetIsFoundByTheHandleItIsNamedBy(): void
    {
        $service = $this->createService(['collection' => ['collection'], 'express' => ['express@7b2']]);

        static::assertSame('express@7b2', $service->getCategory('express@7b2')->getHandle());
    }

    public function testNothingAnswersForASetThisInstallationDoesntHave(): void
    {
        $service = $this->createService(['collection' => ['collection'], 'express' => ['express@7b2']]);

        static::assertNull($service->getCategory('user'));
        static::assertNull($service->getCategory('express@9f4'));
        // the Express category alone names no keys: each entity has its own set
        static::assertNull($service->getCategory('express'));
    }

    /**
     * @param array<string,string[]> $sets the handles of the sets each category serves, by the handle
     *                                     of the category
     */
    private function createService(array $sets): Service
    {
        $rows = [];
        foreach ($sets as $handle => $handles) {
            $row = $this->createMock(CategoryEntity::class);
            $row->method('getController')->willReturn($this->createCategoryServing($handles));
            $rows[] = $row;
        }
        $categoryService = $this->createMock(CategoryService::class);
        $categoryService->method('getList')->willReturn($rows);

        return new Service($categoryService);
    }

    /**
     * @param string[] $handles the handles of the sets of keys the category serves
     */
    private function createCategoryServing(array $handles): CategoryInterface
    {
        $handler = $this->createMock(ApiHandler::class);
        $sets = [];
        foreach ($handles as $handle) {
            $sets[] = new Category($handler, $handle);
        }
        $handler->method('getApiCategories')->willReturn($sets);
        $category = $this->createMock(PageCategory::class);
        $category->method('getApiHandler')->willReturn($handler);

        return $category;
    }

    /**
     * @return string[]
     */
    private function getHandles(Service $service): array
    {
        return array_map(static function (Category $category): string {
            return $category->getHandle();
        }, $service->getCategories());
    }
}
