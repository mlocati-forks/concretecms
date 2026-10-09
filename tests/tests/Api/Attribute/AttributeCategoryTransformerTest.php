<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Attribute;

use Concrete\Core\Api\Attribute\Category\ApiHandler;
use Concrete\Core\Api\Attribute\Category;
use Concrete\Core\Api\Fractal\Transformer\AttributeCategoryTransformer;
use Concrete\Core\Attribute\Category\CategoryInterface;
use Concrete\Core\Attribute\Category\ExpressCategory;
use Concrete\Core\Entity\Express\Entity;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \Concrete\Core\Api\Fractal\Transformer\AttributeCategoryTransformer
 */
class AttributeCategoryTransformerTest extends TestCase
{
    use SchemaFieldsTrait;

    public function testASetOfKeysSaysWhatItHoldsAndWhoBroughtIt(): void
    {
        $transformed = $this->transform($this->createSet('collection', '', 'Attributes of pages'));

        static::assertAnswerIs([
            'handle' => 'collection',
            'description' => 'Attributes of pages',
            'package' => '',
        ], $transformed);
    }

    public function testASetOfKeysOfAPackageNamesIt(): void
    {
        $transformed = $this->transform($this->createSet('invoices', 'my_package'));

        static::assertSame('my_package', $transformed['package']);
        static::assertSame('', $transformed['description']);
    }

    /**
     * Only the categories that hand over more than the fields every set has build a model of their own.
     */
    public function testACategorySayingNothingMoreIsDescribedByTheCommonModel(): void
    {
        $transformed = $this->transform($this->createSet('file'));

        static::assertArrayNotHasKey('express_entity_id', $transformed);
        $this->assertFieldsAre('AttributeCategory', $transformed);
    }

    public function testASetOfKeysOfAnExpressEntityNamesIt(): void
    {
        $transformed = $this->transform($this->createExpressSet('7b2'));

        static::assertAnswerIs([
            'handle' => 'express@7b2',
            'description' => 'Attributes of the entries of the Project entity',
            'package' => '',
            'express_entity_id' => '7b2',
        ], $transformed);
    }

    public function testTheFieldsOfASetOfAnEntityAreDescribedToo(): void
    {
        $this->assertFieldsAre('AttributeCategoryExpress', $this->transform($this->createExpressSet('7b2')));
    }

    /**
     * Get a set of keys of a category that hands over nothing more than the common fields.
     */
    private function createSet(string $handle, string $package = '', string $description = ''): Category
    {
        $handler = new ApiHandler($this->createMock(CategoryInterface::class));

        return new Category($handler, $handle, $package, $description);
    }

    /**
     * @return array<string,mixed> what a client reads of the set of keys
     */
    private function transform(Category $category): array
    {
        return (new AttributeCategoryTransformer())->transform($category);
    }

    /**
     * Get a set of keys as the Express category serves it, which is the one that names its entity.
     */
    private function createExpressSet(string $entityID): Category
    {
        $entity = $this->createMock(Entity::class);
        $entity->method('getId')->willReturn($entityID);
        $entity->method('getName')->willReturn('Project');
        $handler = new ApiHandler\Express($this->createMock(ExpressCategory::class));

        return new Category\Express($handler, $entity);
    }
}
