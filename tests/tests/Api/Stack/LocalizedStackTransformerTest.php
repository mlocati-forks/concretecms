<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Stack;

use Concrete\Core\Api\Fractal\Transformer\LocalizedStackTransformer;
use Concrete\Core\Entity\Site\Site;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Page\Stack\Stack;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \Concrete\Core\Api\Fractal\Transformer\LocalizedStackTransformer
 */
class LocalizedStackTransformerTest extends TestCase
{
    public function testALocalizedStackNamesTheLanguageItSpeaksAndTheSiteOfThatLanguage(): void
    {
        $site = $this->createMock(Site::class);
        $site->method('getSiteID')->willReturn(2);
        $section = $this->createMock(Section::class);
        $section->method('getLocale')->willReturn('it_IT');
        $section->method('getSite')->willReturn($site);
        $stack = $this->createMock(Stack::class);
        $stack->method('getMultilingualSection')->willReturn($section);
        $stack->method('getCollectionID')->willReturn(212);

        static::assertSame([
            'locale' => 'it_IT',
            'site_id' => 2,
            'id' => 212,
        ], (new LocalizedStackTransformer(false))->transform($stack));
    }

    public function testAStackBelongingToNoSectionHasNoLocaleAndNoSite(): void
    {
        $stack = $this->createMock(Stack::class);
        $stack->method('getMultilingualSection')->willReturn(null);
        $stack->method('getCollectionID')->willReturn(211);

        $transformed = (new LocalizedStackTransformer(false))->transform($stack);

        static::assertSame('', $transformed['locale']);
        static::assertNull($transformed['site_id']);
    }
}
