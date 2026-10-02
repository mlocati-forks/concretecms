<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Page;

use Concrete\Core\Api\Fractal\Transformer\PageTypeTransformer;
use Concrete\Core\Entity\Page\Template;
use Concrete\Core\Page\Type\Type as PageType;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \Concrete\Core\Api\Fractal\Transformer\PageTypeTransformer
 */
class PageTypeTransformerTest extends TestCase
{
    use SchemaFieldsTrait;

    public function testAPageTypeIsDescribedWithTheTemplatesItAllows(): void
    {
        $pageType = $this->createPageType('right_sidebar', 'Right Sidebar', ['right_sidebar', 'full']);

        $transformed = (new PageTypeTransformer())->transform($pageType);

        static::assertSame('right_sidebar', $transformed['handle']);
        static::assertSame('Right Sidebar', $transformed['name']);
        static::assertSame('right_sidebar', $transformed['default_template']);
        static::assertSame(['right_sidebar', 'full'], $transformed['templates']);
        // the page types of the core belong to no package
        static::assertSame('', $transformed['package']);
        $this->assertFieldsAre('PageType', $transformed);
    }

    public function testAPageTypeWithoutADefaultTemplateSaysSo(): void
    {
        $pageType = $this->createPageType('empty', 'Empty', []);

        $transformed = (new PageTypeTransformer())->transform($pageType);

        static::assertSame('', $transformed['default_template']);
        static::assertSame([], $transformed['templates']);
    }

    /**
     * @param string[] $templateHandles the handles of the templates the page type allows, the first
     *                                  of which is its default one
     */
    private function createPageType(string $handle, string $name, array $templateHandles): PageType
    {
        $templates = [];
        foreach ($templateHandles as $templateHandle) {
            $template = $this->createMock(Template::class);
            $template->method('getPageTemplateHandle')->willReturn($templateHandle);
            $templates[] = $template;
        }
        $pageType = $this->createMock(PageType::class);
        $pageType->method('getPageTypeHandle')->willReturn($handle);
        $pageType->method('getPageTypeName')->willReturn($name);
        $pageType->method('getPackageHandle')->willReturn(false);
        $pageType->method('getPageTypeDefaultPageTemplateObject')->willReturn($templates === [] ? null : $templates[0]);
        $pageType->method('getPageTypePageTemplateObjects')->willReturn($templates);

        return $pageType;
    }
}
