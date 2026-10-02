<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Site;

use Concrete\Core\Api\Fractal\Transformer\SiteTransformer;
use Concrete\Core\Entity\Site\Locale;
use Concrete\Core\Entity\Site\Site;
use Concrete\Core\Page\Theme\Theme as PageTheme;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Tests what the sites endpoints hand to their clients.
 *
 * @see \Concrete\Core\Api\Fractal\Transformer\SiteTransformer
 */
class SiteTransformerTest extends TestCase
{
    public function testASiteSaysWhatItsPagesAreGivenWhenTheyAskForNothing(): void
    {
        $theme = $this->createMock(PageTheme::class);
        $theme->method('getThemeHandle')->willReturn('pixel');
        $site = $this->createSite('midnight', 'midnight-dark');

        $defaults = $this->transform($site, $theme)['defaults'];

        static::assertSame([
            'page_theme' => 'pixel',
            'page_theme_skin' => 'midnight',
            'page_theme_skin_dark' => 'midnight-dark',
        ], $defaults);
    }

    public function testTheSkinsAreEmptyWhereTheSiteNamesNone(): void
    {
        $theme = $this->createMock(PageTheme::class);
        $theme->method('getThemeHandle')->willReturn('pixel');
        $site = $this->createSite(null, null);

        $defaults = $this->transform($site, $theme)['defaults'];

        static::assertSame('', $defaults['page_theme_skin']);
        static::assertSame('', $defaults['page_theme_skin_dark']);
    }

    public function testTheLocaleOfASiteIsStillHandedOverTheWayItUsedToBe(): void
    {
        $transformed = $this->transform($this->createSite(null, null), null);

        static::assertSame('en_US', $transformed['default_locale']);
    }

    public function testASiteNamingAThemeThatIsGoneSaysNothing(): void
    {
        $defaults = $this->transform($this->createSite(null, null), null)['defaults'];

        static::assertSame('', $defaults['page_theme']);
    }

    /**
     * @return array<string,mixed>
     */
    private function transform(Site $site, ?PageTheme $theme): array
    {
        return $this->createTransformer($theme)->transform($site);
    }

    /**
     * Build a transformer that finds the page theme of a site without asking the database.
     */
    private function createTransformer(?PageTheme $theme): SiteTransformer
    {
        return new class ($theme) extends SiteTransformer {
            /**
             * @var \Concrete\Core\Page\Theme\Theme|null
             */
            private $theme;

            public function __construct(?PageTheme $theme)
            {
                $this->theme = $theme;
            }

            /**
             * {@inheritdoc}
             *
             * @see \Concrete\Core\Api\Fractal\Transformer\SiteTransformer::getPageTheme()
             */
            protected function getPageTheme(Site $site): ?PageTheme
            {
                return $this->theme;
            }
        };
    }

    /**
     * @param string|null $skin the skin the site names, NULL when it names none
     * @param string|null $skinDark the skin the site names for dark mode, NULL when it names none
     */
    private function createSite(?string $skin, ?string $skinDark): Site
    {
        $site = $this->createMock(Site::class);
        $site->method('getSiteID')->willReturn(1);
        $site->method('getSiteHandle')->willReturn('default');
        $site->method('getSiteName')->willReturn('Example');
        $site->method('getSiteHomePageID')->willReturn(1);
        $site->method('getThemeID')->willReturn(3);
        $locale = $this->createMock(Locale::class);
        $locale->method('getLocale')->willReturn('en_US');
        $site->method('getDefaultLocale')->willReturn($locale);
        $site->method('getThemeSkinIdentifier')->willReturn($skin);
        $site->method('getThemeSkinIdentifierDark')->willReturn($skinDark);

        return $site;
    }
}
