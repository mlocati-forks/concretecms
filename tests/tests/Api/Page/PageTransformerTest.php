<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Page;

use Concrete\Core\Api\Fractal\Transformer\PageTransformer;
use Concrete\Core\Entity\Site\Site;
use Concrete\Core\Page\Page;
use Concrete\Core\Permission\Response\PageResponse;
use Concrete\Core\Page\Theme\Theme as PageTheme;
use Concrete\Core\Site\Config\Liaison;
use Concrete\Core\StyleCustomizer\Skin\SkinInterface;
use Concrete\Core\User\User;
use Concrete\TestHelpers\Api\TokenScopesTrait;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @see \Concrete\Core\Api\Fractal\Transformer\PageTransformer
 */
class PageTransformerTest extends TestCase
{
    use TokenScopesTrait;


     /**
     * {@inheritdoc}
     *
     * @see TestCase::setUp()
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->giveTheTokenScopes(['pages:read']);
        // with no permission key registered, only the superuser is allowed anything
        $superUser = $this->createMock(User::class);
        $superUser->method('isSuperUser')->willReturn(true);
        app()->instance(User::class, $superUser);
    }

    /**
     * {@inheritdoc}
     *
     * @see TestCase::tearDown()
     */
    public function tearDown(): void
    {
        $this->takeTheTokenBack();
        app()->forgetInstance(User::class);
        parent::tearDown();
    }

   public function testAPageSaysWhereItHangsAndHowItIsShown(): void
    {
        $theme = $this->createTheme('elemental', 'midnight');

        $transformed = (new PageTransformer())->transform($this->createPage('about-us', $theme, 'midnight'));

        static::assertSame('/about/about-us', $transformed['path']);
        static::assertSame('about-us', $transformed['url_slug']);
        static::assertSame('elemental', $transformed['theme']);
        static::assertSame('midnight', $transformed['theme_skin']);
    }

    public function testThePageThatTheSiteHangsFromHasNoSlugOfItsOwn(): void
    {
        $transformed = (new PageTransformer())->transform($this->createPage('', $this->createTheme('elemental', 'default'), 'default'));

        static::assertSame('', $transformed['url_slug']);
    }

    public function testAPageSaysWhichSiteItBelongsTo(): void
    {
        $page = $this->createPage('about-us', $this->createTheme('elemental', 'default'), 'default', $this->createSite(2));

        static::assertSame(2, (new PageTransformer())->transform($page)['site_id']);
    }

    /**
     * The pages holding the stacks are kept outside of the sites of the installation.
     */
    public function testAPageOfNoSiteSaysSo(): void
    {
        $page = $this->createPage('about-us', $this->createTheme('elemental', 'default'), 'default');

        static::assertNull((new PageTransformer())->transform($page)['site_id']);
    }

    public function testAThemeOfferingNoSkinsLeavesTheSkinOfItsPagesEmpty(): void
    {
        // the core falls back to the skin named default even where the theme has none
        $theme = $this->createTheme('elemental', '');

        $transformed = (new PageTransformer())->transform($this->createPage('about-us', $theme, 'default'));

        static::assertSame('', $transformed['theme_skin']);
    }

    /**
     * @param string $skinIdentifier the only skin the theme offers, empty when it offers none
     */
    private function createSite(int $siteID): Site
    {
        $config = $this->createMock(Liaison::class);
        $config->method('get')->willReturn('en_US');
        $site = $this->createMock(Site::class);
        $site->method('getSiteID')->willReturn($siteID);
        $site->method('getConfigRepository')->willReturn($config);

        return $site;
    }

    private function createTheme(string $handle, string $skinIdentifier): PageTheme
    {
        $theme = $this->createMock(PageTheme::class);
        $theme->method('getThemeHandle')->willReturn($handle);
        $skin = $this->createMock(SkinInterface::class);
        $theme->method('getSkinByIdentifier')->willReturnCallback(static function (string $identifier) use ($skinIdentifier, $skin) {
            return $identifier !== '' && $identifier === $skinIdentifier ? $skin : null;
        });

        return $theme;
    }

    /**
     * @param string $urlSlug the handle of the page
     * @param \Concrete\Core\Page\Theme\Theme|null $theme the theme the page is shown with
     * @param string $skinIdentifier the skin the page is shown with
     */
    private function createPage(string $urlSlug, ?PageTheme $theme, string $skinIdentifier, ?Site $site = null): Page
    {
        $page = $this->createMock(Page::class);
        $page->method('getSite')->willReturn($site);
        $page->method('getCollectionID')->willReturn(123);
        $page->method('getCollectionPath')->willReturn('/about/about-us');
        $page->method('getCollectionHandle')->willReturn($urlSlug);
        $page->method('getCollectionName')->willReturn('About us');
        $page->method('getCollectionDescription')->willReturn('');
        $page->method('getCollectionDateAdded')->willReturn('2026-10-02 10:00:00');
        $page->method('getCollectionDateLastModified')->willReturn('2026-10-02 11:00:00');
        $page->method('getCollectionDatePublic')->willReturn('2026-10-02 12:00:00');
        $page->method('getPageTypeHandle')->willReturn('page');
        $page->method('getPageTemplateHandle')->willReturn('full');
        $page->method('getCollectionThemeObject')->willReturn($theme);
        $page->method('getPageSkinIdentifier')->willReturn($skinIdentifier);
        $page->method('isExternalLink')->willReturn(false);
        // a client reads the whole of a page it may view, and this one answers about its permissions
        $page->method('getPermissionResponseClassName')->willReturn(PageResponse::class);
        $page->method('getPermissionObjectIdentifier')->willReturn('123');

        return $page;
    }
}
