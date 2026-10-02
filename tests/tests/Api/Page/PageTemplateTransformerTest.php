<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Page;

use Concrete\Core\Api\Fractal\Transformer\PageTemplateTransformer;
use Concrete\Core\Entity\Page\Template;
use Concrete\TestHelpers\Api\SchemaFieldsTrait;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \Concrete\Core\Api\Fractal\Transformer\PageTemplateTransformer
 */
class PageTemplateTransformerTest extends TestCase
{
    use SchemaFieldsTrait;

    public function testAPageTemplateIsDescribedWithItsHandleAndItsName(): void
    {
        $template = $this->createMock(Template::class);
        $template->method('getPageTemplateHandle')->willReturn('right_sidebar');
        $template->method('getPageTemplateName')->willReturn('Right Sidebar');
        // the method returns FALSE when the page template belongs to no package
        $template->method('getPackageHandle')->willReturn(false);

        $transformed = (new PageTemplateTransformer())->transform($template);

        static::assertSame(['handle' => 'right_sidebar', 'name' => 'Right Sidebar', 'package' => ''], $transformed);
        $this->assertFieldsAre('PageTemplate', $transformed);
    }

    public function testThePackageOfAPageTemplateIsHandedOverByItsHandle(): void
    {
        $template = $this->createMock(Template::class);
        $template->method('getPageTemplateHandle')->willReturn('whatever');
        $template->method('getPageTemplateName')->willReturn('Whatever');
        $template->method('getPackageHandle')->willReturn('my_package');

        static::assertSame('my_package', (new PageTemplateTransformer())->transform($template)['package']);
    }
}
