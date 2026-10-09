<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Attribute;

use Concrete\Attribute\Select\Controller as SelectController;
use Concrete\Core\Api\Fractal\Transformer\AttributeTypeTransformer;
use Concrete\Core\Attribute\Controller as AttributeTypeController;
use Concrete\Core\Entity\Attribute\Category;
use Concrete\Core\Entity\Attribute\Type;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\Tests\TestCase;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The types an attribute key can be of, which a client reads to know what the keys of each of them
 * carry.
 *
 * @see \Concrete\Core\Api\Fractal\Transformer\AttributeTypeTransformer
 */
class AttributeTypeTransformerTest extends TestCase
{
    use SchemaFieldsTrait;

    public function testATypeSaysHowItIsNamedAndWhereItIsAvailable(): void
    {
        $transformed = $this->transform('boolean', 'Checkbox', '', ['collection', 'user']);

        static::assertAnswerIs([
            'handle' => 'boolean',
            'name' => 'Checkbox',
            'package' => '',
            'categories' => ['collection', 'user'],
            'key_schema' => 'AttributeKey',
        ], $transformed);
    }

    /**
     * The keys of a type that carries more than the others are described by a schema of its own, and
     * this is where a client learns which one.
     */
    public function testATypeNamesTheSchemaOfItsKeys(): void
    {
        $transformed = $this->transform('select', 'Select', '', ['collection'], new SelectController($this->createMock(EntityManager::class)));

        static::assertSame('AttributeKeySelect', $transformed['key_schema']);
    }

    public function testATypeOfAPackageNamesIt(): void
    {
        $transformed = $this->transform('stars', 'Stars', 'my_package', []);

        static::assertSame('my_package', $transformed['package']);
        static::assertSame([], $transformed['categories']);
    }

    public function testTheCategoriesAreHandedOverInOrder(): void
    {
        $transformed = $this->transform('boolean', 'Checkbox', '', ['user', 'collection', 'file']);

        static::assertSame(['collection', 'file', 'user'], $transformed['categories']);
    }

    public function testTheFieldsAreTheOnesTheSpecificationDescribes(): void
    {
        $this->assertFieldsAre('AttributeType', $this->transform('boolean', 'Checkbox', '', ['collection']));
    }

    /**
     * @param string[] $categoryHandles the categories whose keys may be of this type
     * @param \Concrete\Core\Attribute\Controller|null $controller NULL for a type that needs nothing of its own
     *
     * @return array<string,mixed> what a client reads of the type
     */
    private function transform(string $handle, string $name, string $package, array $categoryHandles, ?AttributeTypeController $controller = null): array
    {
        $categories = [];
        foreach ($categoryHandles as $categoryHandle) {
            $category = $this->createMock(Category::class);
            $category->method('getAttributeKeyCategoryHandle')->willReturn($categoryHandle);
            $categories[] = $category;
        }
        $type = $this->createMock(Type::class);
        $type->method('getAttributeTypeHandle')->willReturn($handle);
        $type->method('getAttributeTypeDisplayName')->willReturn($name);
        $type->method('getPackageHandle')->willReturn($package);
        $type->method('getAttributeCategories')->willReturn(new ArrayCollection($categories));
        $type->method('getController')->willReturn($controller ?? new AttributeTypeController($this->createMock(EntityManager::class)));

        return (new AttributeTypeTransformer())->transform($type);
    }
}
