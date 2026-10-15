<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Block;

use Concrete\Core\Api\Fractal\Transformer\BlockTypeTransformer;
use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Entity\Block\BlockType\BlockType as BlockTypeEntity;
use Concrete\Core\Page\Theme\Theme;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\TestHelpers\Database\ConcreteDatabaseTestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \Concrete\Core\Api\Fractal\Transformer\BlockTypeTransformer
 */
class BlockTypeTransformerTest extends ConcreteDatabaseTestCase
{
    use SchemaFieldsTrait;

    protected $tables = [
        'BlockTypeSets',
        'PageThemes',
    ];

    protected $entityClassNames = [
        BlockTypeEntity::class,
        \Concrete\Core\Entity\Package::class,
    ];

    public function testABlockTypeIsDescribedWithTheSchemaOfItsValue(): void
    {
        $transformed = $this->transform('image');

        static::assertSame('image', $transformed['handle']);
        static::assertSame('Image', $transformed['name']);
        // the block types of the core belong to no package
        static::assertSame('', $transformed['package']);
        static::assertSame('object', $transformed['value_schema']['type']);
        static::assertArrayHasKey('altText', $transformed['value_schema']['properties']);
        $this->assertFieldsAre('BlockType', $transformed);
    }

    public function testABlockTypeIsDescribedAsItDescribesItself(): void
    {
        $blockType = $this->getBlockType('image');

        $transformed = (new BlockTypeTransformer())->transform($blockType);

        static::assertSame((string) $blockType->getBlockTypeDescription(), $transformed['description']);
    }

    public function testTheHandlerOfABlockTypeCanDescribeItToTheClientsInstead(): void
    {
        // the description that the layout block type gives of itself says nothing to an API client
        $blockType = $this->getBlockType('core_area_layout');

        $transformed = (new BlockTypeTransformer())->transform($blockType);

        $handler = $blockType->getController()->getApiHandler();
        static::assertSame($handler->getCustomApiDescription(), $transformed['description']);
        static::assertNotSame((string) $blockType->getBlockTypeDescription(), $transformed['description']);
    }

    public function testTheSchemaIsTheOneOfTheHandlerOfTheBlockType(): void
    {
        $blockType = $this->getBlockType('image');

        $transformed = (new BlockTypeTransformer())->transform($blockType);

        static::assertSame($blockType->getController()->getApiHandler()->getApiValueSchema(), $transformed['value_schema']);
    }

    public function testTheCustomTemplatesOfABlockTypeAreHandedOverWithIt(): void
    {
        // no theme is installed in these tests, so page_list has the one of the core and nothing else
        $templates = $this->customTemplatesOf('page_list');

        static::assertSame([['handle' => 'basic_list.php', 'name' => 'Basic List', 'only_for_themes' => []]], $templates);
    }

    public function testATemplateOfATypeThatNoThemeOwnsIsForEveryPage(): void
    {
        $templates = $this->customTemplatesOf('autonav');

        static::assertNotSame([], $templates);
        foreach ($templates as $template) {
            static::assertSame([], $template['only_for_themes']);
        }
    }

    public function testABlockTypeWithNoTemplateHandsOverNone(): void
    {
        static::assertSame([], $this->customTemplatesOf('next_previous'));
    }

    public function testATemplateNamesTheThemesThatGiveIt(): void
    {
        $templates = $this->transformWithTheThemes('page_list', ['atomik', 'elemental']);

        $byHandle = array_column($templates, 'only_for_themes', 'handle');
        // what the core gives is there whatever the theme, what a theme gives names it
        static::assertSame([], $byHandle['basic_list.php']);
        static::assertSame(['atomik'], $byHandle['resource_list.php']);
        static::assertSame(['elemental'], $byHandle['thumbnail_grid']);
    }

    public function testATemplateThatEveryPageHasNamesNoTheme(): void
    {
        // atomik gives a byline.php of its own, and the core gives one to every page
        $templates = $this->transformWithTheThemes('page_title', ['atomik']);

        $byHandle = array_column($templates, 'only_for_themes', 'handle');
        static::assertSame([], $byHandle['byline.php']);
    }

    /**
     * @return array<string,mixed>
     */
    private function transform(string $handle): array
    {
        return (new BlockTypeTransformer())->transform($this->getBlockType($handle));
    }

    /**
     * The custom templates of a block type, read the way a client reads them: the transformer hands the
     * models over, and it is the serialisation that turns them into what travels.
     *
     * @return array<int,array<string,mixed>>
     */
    private function customTemplatesOf(string $handle): array
    {
        return self::asRead($this->transform($handle))['custom_templates'];
    }

    /**
     * Describe a block type as if the installation had these themes, which these tests do not install.
     *
     * @param string[] $themeHandles
     *
     * @return array<int,array<string,mixed>>
     */
    private function transformWithTheThemes(string $handle, array $themeHandles): array
    {
        $themes = [];
        foreach ($themeHandles as $themeHandle) {
            $theme = $this->createMock(Theme::class);
            $theme->method('getThemeHandle')->willReturn($themeHandle);
            $theme->method('getPackageHandle')->willReturn(null);
            $themes[] = $theme;
        }
        $transformer = new class ($themes) extends BlockTypeTransformer {
            /**
             * @var \Concrete\Core\Page\Theme\Theme[]
             */
            private $installed;

            /**
             * @param \Concrete\Core\Page\Theme\Theme[] $installed
             */
            public function __construct(array $installed)
            {
                $this->installed = $installed;
            }

            /**
             * {@inheritdoc}
             *
             * @see \Concrete\Core\Api\Fractal\Transformer\BlockTypeTransformer::getThemes()
             */
            protected function getThemes(): array
            {
                return $this->installed;
            }
        };

        return self::asRead($transformer->transform($this->getBlockType($handle)))['custom_templates'];
    }

    private function getBlockType(string $handle): BlockTypeEntity
    {
        $blockType = BlockType::getByHandle($handle);
        if ($blockType === null) {
            BlockType::installBlockType($handle);
            $blockType = BlockType::getByHandle($handle);
        }

        return $blockType;
    }
}
