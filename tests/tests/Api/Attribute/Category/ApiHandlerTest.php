<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Attribute\Category;

use Concrete\Core\Api\Attribute\Category\ApiHandler;
use Concrete\Core\Attribute\Category\CategoryInterface;
use Concrete\Core\Attribute\Category\PageCategory;
use Concrete\Core\Entity\Attribute\Category;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Tests\TestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * What the API does with the keys of a category that asks for nothing of its own.
 *
 * @see \Concrete\Core\Api\Attribute\Category\ApiHandler
 */
class ApiHandlerTest extends TestCase
{
    public function testACategoryHandsOverItsOwnHandler(): void
    {
        $own = new ApiHandler\Page($this->createMock(CategoryInterface::class));
        $category = $this->createMock(PageCategory::class);
        $category->method('getApiHandler')->willReturn($own);

        static::assertSame($own, ApiHandler::forCategory($category));
    }

    /**
     * A category out there may know nothing about the API, and it gets the defaults.
     */
    public function testACategoryThatHandsOverNoneGetsTheDefaults(): void
    {
        $handler = ApiHandler::forCategory($this->createMock(CategoryInterface::class));

        static::assertSame(ApiHandler::class, get_class($handler));
    }

    public function testTheOnlySetOfKeysIsTheCategoryItself(): void
    {
        $handler = new ApiHandler($this->createCategory('invoices', 'my_package'));

        $sets = $handler->getApiCategories();

        static::assertCount(1, $sets);
        static::assertSame('invoices', $sets[0]->getHandle());
        static::assertSame('my_package', $sets[0]->getPackageHandle());
        static::assertSame($handler, $sets[0]->getApiHandler());
    }

    public function testWhatTheHandlerSaysOfTheCategoryTravelsWithTheSet(): void
    {
        $handler = new ApiHandler\Page($this->createCategory('collection'));

        static::assertSame('Attributes of pages', $handler->getApiCategories()[0]->getDescription());
        static::assertSame('Attributes of pages', $handler->getCategoryDescription());
    }

    public function testACategoryThatSaysNothingIsDescribedByNobody(): void
    {
        static::assertSame('', (new ApiHandler($this->createCategory('invoices')))->getCategoryDescription());
    }

    /**
     * A category whose row is gone has no keys to serve either.
     */
    public function testACategoryWithoutARowServesNoSet(): void
    {
        $handler = new ApiHandler($this->createMock(CategoryInterface::class));

        static::assertSame([], $handler->getApiCategories());
    }

    public function testTheSetsOfACategoryAreDescribedByTheCommonModel(): void
    {
        $handler = new ApiHandler($this->createCategory('invoices'));
        $set = $handler->getApiCategories()[0];

        static::assertSame(['handle', 'description', 'package'], array_keys($handler->createApiCategoryModel($set)->jsonSerialize()));
    }

    /**
     * The keys of the only set of a category are its own, the ones the core keeps for itself left out.
     */
    public function testTheKeysOfTheSetAreTheOnesOfTheCategory(): void
    {
        $row = $this->createMock(Category::class);
        $keys = [$this->createMock(Key::class)];
        $repository = $this->createMock(EntityRepository::class);
        $repository
            ->expects(static::once())
            ->method('findBy')
            ->with(['category' => $row, 'akIsInternal' => false], ['akHandle' => 'ASC'])
            ->willReturn($keys)
        ;
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);
        $category = $this->createMock(PageCategory::class);
        $category->method('getCategoryEntity')->willReturn($row);
        $handler = new ApiHandler($category, $entityManager);

        static::assertSame($keys, $handler->getApiKeys($handler->getApiCategories()[0]));
    }

    public function testACategoryAddsNothingToWhatAClientReadsOfItsKeys(): void
    {
        $handler = new ApiHandler($this->createCategory('invoices'));

        static::assertSame([], $handler->getApiKeyFields($this->createMock(Key::class)));
    }

    /**
     * @return \Concrete\Core\Attribute\Category\CategoryInterface a category defined by that row
     */
    private function createCategory(string $handle, string $package = ''): CategoryInterface
    {
        $row = $this->createMock(Category::class);
        $row->method('getAttributeKeyCategoryHandle')->willReturn($handle);
        $row->method('getPackageHandle')->willReturn($package);
        $category = $this->createMock(PageCategory::class);
        $category->method('getCategoryEntity')->willReturn($row);

        return $category;
    }
}
