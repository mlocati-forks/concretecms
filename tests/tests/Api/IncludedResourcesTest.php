<?php

declare(strict_types=1);

namespace Concrete\Tests\Api;

use Concrete\Core\Api\Fractal\Transformer\AreaTransformer;
use Concrete\Core\Api\Fractal\Transformer\BaseBlockTransformer;
use Concrete\Core\Api\Fractal\Transformer\BlockTransformer;
use Concrete\Core\Api\Fractal\Transformer\CalendarEventTransformer;
use Concrete\Core\Api\Fractal\Transformer\CalendarTransformer;
use Concrete\Core\Api\Fractal\Transformer\FileTransformer;
use Concrete\Core\Api\Fractal\Transformer\PageTransformer;
use Concrete\Core\Api\Fractal\Transformer\SiteTransformer;
use Concrete\Core\Api\Fractal\Transformer\UserTransformer;
use Concrete\Core\Area\ApiArea;
use Concrete\Core\Page\Page;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\Tests\TestCase;
use League\Fractal\Manager;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\DataArraySerializer;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \Concrete\Core\Http\Middleware\FractalNegotiatorMiddleware
 */
class IncludedResourcesTest extends TestCase
{
    use SchemaFieldsTrait;

    /**
     * The transformers that hand over a resource of their own, and the schema describing what they
     * answer with.
     *
     * @return array<int,string[]>
     */
    public static function provideTransformersAndTheirSchema(): array
    {
        return [
            [AreaTransformer::class, 'Area'],
            [BaseBlockTransformer::class, 'Block'],
            [BlockTransformer::class, 'Block'],
            [CalendarEventTransformer::class, 'CalendarEvent'],
            [CalendarTransformer::class, 'Calendar'],
            [FileTransformer::class, 'File'],
            [PageTransformer::class, 'Page'],
            [SiteTransformer::class, 'Site'],
            [UserTransformer::class, 'User'],
        ];
    }

    /**
     * @dataProvider provideTransformersAndTheirSchema
     */
    public function testEveryIncludeIsDescribedWithTheDataItComesIn(string $transformer, string $schema): void
    {
        $includes = $this->getIncludesOf($transformer);

        static::assertNotSame([], $includes);
        foreach ($includes as $include) {
            static::assertSame(
                ['data'],
                array_keys($this->getSchemaField($schema, $include)['properties'] ?? []),
                "The {$include} of the {$schema} schema is a resource of its own, which the serializer hands over wrapped in a data property: describe it that way."
            );
        }
    }

    /**
     * The serializer wraps an included resource as it wraps the one the answer is about, so a client
     * reads it one property deeper.
     */
    public function testAnIncludedResourceComesWrappedInItsOwnData(): void
    {
        $page = $this->createMock(Page::class);
        $page->method('getBlocks')->willReturn([]);
        $area = $this->createMock(ApiArea::class);
        $area->method('getAreaHandle')->willReturn('Main');
        $area->method('getPage')->willReturn($page);

        $manager = new Manager();
        $manager->setSerializer(new DataArraySerializer());
        $answer = $manager->createData(new Item($area, new AreaTransformer()))->toArray();

        static::assertSame(['data'], array_keys($answer));
        // the blocks of an area travel with it, and come wrapped
        static::assertSame(['name', 'blocks'], array_keys($answer['data']));
        static::assertSame(['data'], array_keys($answer['data']['blocks']));
        static::assertSame([], $answer['data']['blocks']['data']);
    }

    /**
     * @return string[] the includes a transformer hands over, the ones it always does first
     */
    private function getIncludesOf(string $transformer): array
    {
        $reflection = new \ReflectionClass($transformer);
        $instance = $reflection->newInstanceWithoutConstructor();
        $includes = [];
        foreach (['defaultIncludes', 'availableIncludes'] as $name) {
            if (!$reflection->hasProperty($name)) {
                continue;
            }
            $property = $reflection->getProperty($name);
            if (PHP_VERSION_ID < 80100) {
                $property->setAccessible(true);
            }
            $includes = array_merge($includes, (array) $property->getValue($instance));
        }

        return array_values(array_unique($includes));
    }
}
