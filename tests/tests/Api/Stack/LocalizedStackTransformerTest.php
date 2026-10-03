<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Stack;

use Concrete\Core\Api\Fractal\Transformer\LocalizedStackTransformer;
use Concrete\Core\Multilingual\Page\Section\Section;
use Concrete\Core\Page\Stack\Stack;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Tests what the stacks endpoint hands to its clients for the versions of a stack that speak the
 * language of a section of the site.
 *
 * @see \Concrete\Core\Api\Fractal\Transformer\LocalizedStackTransformer
 */
class LocalizedStackTransformerTest extends TestCase
{
    public function testALocalizedStackNamesTheLocaleItSpeaks(): void
    {
        $section = $this->createMock(Section::class);
        $section->method('getLocale')->willReturn('it_IT');
        $stack = $this->createMock(Stack::class);
        $stack->method('getMultilingualSection')->willReturn($section);
        $stack->method('getCollectionID')->willReturn(212);

        static::assertSame([
            'locale' => 'it_IT',
            'id' => 212,
        ], (new LocalizedStackTransformer(false))->transform($stack));
    }

    public function testAStackBelongingToNoSectionHasNoLocale(): void
    {
        $stack = $this->createMock(Stack::class);
        $stack->method('getMultilingualSection')->willReturn(null);
        $stack->method('getCollectionID')->willReturn(211);

        static::assertSame('', (new LocalizedStackTransformer(false))->transform($stack)['locale']);
    }
}
