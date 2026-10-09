<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Attribute;

use Concrete\Core\Api\Fractal\Transformer\AttributeKeyTransformer;
use Concrete\Core\Attribute\Controller as AttributeTypeController;
use Concrete\Core\Entity\Attribute\Key\Key;
use Concrete\Core\Entity\Attribute\Type;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\Tests\TestCase;
use Doctrine\ORM\EntityManager;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A client writes an attribute among the custom_attributes of an object, by handle, and a value
 * picked out of a fixed set by the ID of an option: this is what it reads to know both.
 *
 * @see \Concrete\Core\Api\Fractal\Transformer\AttributeKeyTransformer
 */
class AttributeKeyTransformerTest extends TestCase
{
    use SchemaFieldsTrait;

    public function testAKeySaysHowItIsNamedAndWhatItTakes(): void
    {
        $transformed = $this->transform($this->createKey(67, 'boxed', 'Boxed', 'boolean'));

        static::assertAnswerIs([
            'id' => 67,
            'handle' => 'boxed',
            'name' => 'Boxed',
            'type' => 'boolean',
        ], $transformed);
    }

    /**
     * A key whose type left with the package that brought it has no controller to ask.
     */
    public function testAKeyOfNoTypeNamesNone(): void
    {
        $transformed = $this->transform($this->createKey(1, 'orphan', 'Orphan', null));

        static::assertSame('', $transformed['type']);
        static::assertArrayNotHasKey('options', $transformed);
    }

    public function testTheFieldsAreTheOnesTheSpecificationDescribes(): void
    {
        $this->assertFieldsAre('AttributeKey', $this->transform($this->createKey(67, 'boxed', 'Boxed', 'boolean')));
    }

    /**
     * @return array<string,mixed> what a client reads of the key
     */
    private function transform(Key $key): array
    {
        return (new AttributeKeyTransformer())->transform($key);
    }

    /**
     * @param string|null $typeHandle NULL where the type of the key is gone
     * @param \Concrete\Core\Attribute\Controller|null $controller NULL for a type that needs none of its own
     *
     * @return \Concrete\Core\Entity\Attribute\Key\Key&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createKey(int $id, string $handle, string $name, ?string $typeHandle, ?AttributeTypeController $controller = null): Key
    {
        $type = null;
        if ($typeHandle !== null) {
            $type = $this->createMock(Type::class);
            $type->method('getAttributeTypeHandle')->willReturn($typeHandle);
        }
        $key = $this->createMock(Key::class);
        $key->method('getAttributeKeyID')->willReturn($id);
        $key->method('getAttributeKeyHandle')->willReturn($handle);
        $key->method('getAttributeKeyDisplayName')->willReturn($name);
        $key->method('getAttributeType')->willReturn($type);
        $key->method('getController')->willReturn($controller ?? new AttributeTypeController($this->createMock(EntityManager::class)));

        return $key;
    }
}
