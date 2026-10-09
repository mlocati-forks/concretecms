<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Attribute;

use Concrete\Core\Api\Attribute\AttributeApiHandler;
use Concrete\Core\Api\Fractal\Transformer\AttributeKeyTransformer;
use Concrete\Core\Api\Model\AttributeKey as AttributeKeyModel;
use Concrete\Core\Api\Attribute\Category\ApiHandler;
use Concrete\Core\Attribute\Category\PageCategory;
use Concrete\Core\Attribute\Controller as AttributeTypeController;
use Concrete\Core\Entity\Attribute\Category;
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
     * Only the types whose value is picked out of a fixed set have options to hand over, and the
     * handler of the type is the one that knows them.
     */
    public function testTheKeyIsDescribedByTheHandlerOfItsType(): void
    {
        $described = new AttributeKeyModel\Select();
        $described->id = 30;
        $described->handle = 'header_color';
        $described->name = 'Header Color';
        $described->type = 'select';
        $described->options = [['id' => 18, 'value' => 'Red', 'display_value' => 'Rosso']];
        $key = $this->createKey(30, 'header_color', 'Header Color', 'select', $this->createControllerDescribing($described));

        static::assertAnswerIs($described->jsonSerialize(), $this->transform($key));
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

    /**
     * Where the site asks for an attribute belongs to the category of the key, so the category adds
     * it to what the type of the key describes.
     */
    public function testTheCategoryOfTheKeyAddsWhatItKnows(): void
    {
        $key = $this->createKey(67, 'boxed', 'Boxed', 'boolean');
        $key->method('getAttributeCategoryEntity')->willReturn($this->createCategoryAdding(['user' => ['required_on_register' => true]]));

        static::assertSame(['required_on_register' => true], $this->transform($key)['user']);
    }

    public function testAKeyOfACategoryThatAddsNothingHandsOverNoSuchField(): void
    {
        $transformed = $this->transform($this->createKey(67, 'boxed', 'Boxed', 'boolean'));

        static::assertArrayNotHasKey('user', $transformed);
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

    /**
     * @param array<string,mixed> $added what the category adds to the model of one of its keys
     *
     * @return \Concrete\Core\Entity\Attribute\Category&\PHPUnit\Framework\MockObject\MockObject
     */
    private function createCategoryAdding(array $added): Category
    {
        $handler = $this->createMock(ApiHandler::class);
        $handler->method('getApiKeyFields')->willReturn($added);
        $controller = $this->createMock(PageCategory::class);
        $controller->method('getApiHandler')->willReturn($handler);
        $category = $this->createMock(Category::class);
        $category->method('getController')->willReturn($controller);

        return $category;
    }

    /**
     * @return \Concrete\Core\Attribute\Controller a controller whose handler describes a key that way
     */
    private function createControllerDescribing(AttributeKeyModel $described): AttributeTypeController
    {
        $handler = $this->createMock(AttributeApiHandler::class);
        $handler->method('describeApiKey')->willReturn($described);

        // getApiHandler() is final, and what it answers with comes from createApiHandler()
        $controller = new class ($this->createMock(EntityManager::class)) extends AttributeTypeController {
            /**
             * @var \Concrete\Core\Api\Attribute\AttributeApiHandler|null
             */
            public $handler;

            /**
             * {@inheritdoc}
             *
             * @see \Concrete\Core\Attribute\Controller::createApiHandler()
             */
            protected function createApiHandler(): AttributeApiHandler
            {
                return $this->handler;
            }
        };
        $controller->handler = $handler;

        return $controller;
    }
}
