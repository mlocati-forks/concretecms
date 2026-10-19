<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Controller;

use Concrete\Core\Api\Controller\Areas;
use Concrete\Core\Area\Area;
use Concrete\Core\Area\GlobalArea;
use Concrete\Core\Entity\Package;
use Concrete\Core\Http\Request;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Single as SinglePage;
use Concrete\Core\Page\Stack\Stack;
use Concrete\Core\Page\Type\Type as PageType;
use Concrete\Core\Permission\Category as PermissionCategory;
use Concrete\Core\Permission\Key\Key as PermissionKey;
use Concrete\Core\StyleCustomizer\Inline\StyleSet as StyleSetService;
use Concrete\Core\User\User;
use Concrete\TestHelpers\Block\BlockApiTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * An area takes a custom style of its own, and what a request says is held to the rules the design dialog
 * holds a form to.
 *
 * @see \Concrete\Core\Api\Controller\Areas::updateArea()
 */
class AreaCustomStyleTest extends BlockApiTestCase
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Database\ConcreteDatabaseTestCase::getEntityClassNames()
     */
    protected function getEntityClassNames(): array
    {
        return array_merge(parent::getEntityClassNames(), [Package::class]);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Database\ConcreteDatabaseTestCase::getTables()
     */
    protected function getTables()
    {
        // the stack behind a global area is a page of its own, named in a table of its own
        return array_merge(parent::getTables(), ['Stacks']);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Block\BlockApiTestCase::setUp()
     */
    public function setUp(): void
    {
        parent::setUp();
        // with no permission key registered, only the superuser is allowed anything
        $superUser = $this->createMock(User::class);
        $superUser->method('isSuperUser')->willReturn(true);
        app()->instance(User::class, $superUser);
        // asking an area about a key it builds itself wants the key to be there
        if (PermissionCategory::getByHandle('area') === null) {
            PermissionCategory::add('area');
            PermissionKey::add('area', 'add_block_to_area', 'Add Block to Area', '', 0, 0);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Block\BlockApiTestCase::tearDown()
     */
    public function tearDown(): void
    {
        app()->forgetInstance(User::class);
        parent::tearDown();
    }

    public function testAnAreaIsGivenACustomStyle(): void
    {
        $page = self::createPage('Page with an area of its own custom style');

        $answer = $this->update($page, 'Main', ['custom_style' => ['background_color' => 'rgba(255,0,0,1)', 'padding_top' => '20px']]);

        $style = $this->customStyleOf($page, 'Main');
        static::assertSame('rgba(255,0,0,1)', $style->getBackgroundColor());
        static::assertSame('20px', $style->getPaddingTop());
        static::assertSame('rgba(255,0,0,1)', $this->answered($answer)['custom_style']->background_color);
    }

    public function testTheCustomStyleOfAnAreaIsChanged(): void
    {
        $page = self::createPage('Page with an area to style again');
        $this->update($page, 'Main', ['custom_style' => ['background_color' => 'rgba(255,0,0,1)']]);

        $this->update($page, 'Main', ['custom_style' => ['text_color' => '#ff0000']]);

        $style = $this->customStyleOf($page, 'Main');
        static::assertSame('#ff0000', $style->getTextColor());
        static::assertSame('', (string) $style->getBackgroundColor());
    }

    public function testTheCustomStyleOfAnAreaIsDropped(): void
    {
        $page = self::createPage('Page with an area back to its theme');
        $this->update($page, 'Main', ['custom_style' => ['text_color' => '#ff0000']]);

        $answer = $this->update($page, 'Main', ['custom_style' => null]);

        static::assertNull($this->areaCustomStyle($page, 'Main'));
        static::assertNull($this->answered($answer)['custom_style']);
    }

    /**
     * A request that says nothing of the custom style leaves the one the area has.
     */
    public function testAnAreaKeepsTheCustomStyleARequestSaysNothingOf(): void
    {
        $page = self::createPage('Page with an area nobody asks about');
        $this->update($page, 'Main', ['custom_style' => ['text_color' => '#ff0000']]);

        $this->update($page, 'Main', []);

        static::assertSame('#ff0000', $this->customStyleOf($page, 'Main')->getTextColor());
    }

    /**
     * A client sends back what it read, and reading a custom style gives the fields the dialog saved: a
     * request that asks for the same custom style asks for nothing, and the style set stays the one it was.
     */
    public function testARequestLeavingTheCustomStyleAsItFoundItKeepsIt(): void
    {
        $page = self::createPage('Page with an area whose custom style stays');
        $this->update($page, 'Main', ['custom_style' => ['background_color' => 'rgba(1,2,3,1)']]);
        $wasSet = $this->customStyleOf($page, 'Main');

        $this->update($page, 'Main', ['custom_style' => [
            'background_color' => 'rgba(1,2,3,1)',
            'background_repeat' => 'no-repeat',
            'background_size' => 'auto',
            'background_position' => 'left top',
        ]]);

        static::assertSame($wasSet->getID(), $this->customStyleOf($page, 'Main')->getID());
    }

    /**
     * What a client writes is what the design dialog would have written, and nothing else gets stored.
     */
    public function testWhatIsNotACssValueIsNotStored(): void
    {
        $page = self::createPage('Page with an area asked for the impossible');

        $this->update($page, 'Main', ['custom_style' => [
            'text_color' => 'rgba(0,0,0,1)',
            'margin_top' => '10 miles',
            'alignment' => 'sideways',
        ]]);

        $style = $this->customStyleOf($page, 'Main');
        static::assertSame('rgba(0,0,0,1)', $style->getTextColor());
        static::assertSame('', (string) $style->getMarginTop());
        static::assertSame('', (string) $style->getAlignment());
    }

    public function testAnAttributeWhoseQuotesAreNotClosedIsRefused(): void
    {
        $page = self::createPage('Page with an area asked for an unclosed quote');

        $answer = $this->update($page, 'Main', ['custom_style' => ['custom_element_attribute' => 'data-x="1']]);

        static::assertInstanceOf(JsonResponse::class, $answer);
        static::assertSame(400, $answer->getStatusCode());
        static::assertNull($this->areaCustomStyle($page, 'Main'));
    }

    /**
     * An area nothing has drawn yet has no record of its own, and writing its custom style creates it, the
     * way adding a block to it does.
     */
    public function testAnAreaThePageHasNeverUsedTakesACustomStyle(): void
    {
        $page = self::createPage('Page with an area nothing has drawn');

        $this->update($page, 'Page Footer', ['custom_style' => ['text_color' => '#ff0000']]);

        static::assertSame('#ff0000', $this->customStyleOf($page, 'Page Footer')->getTextColor());
    }

    /**
     * The custom style of a global area belongs to the stack behind it: that is where it is written, in a
     * version of the stack tied to the one of the page, so that approving the page publishes it as well.
     */
    public function testTheCustomStyleOfAGlobalAreaIsWrittenOnItsStack(): void
    {
        $page = self::createPage('Page with a global area');
        // a stack is a page of the single page holding them, of a page type of its own
        PageType::add(['handle' => STACKS_PAGE_TYPE, 'name' => 'Stack', 'internal' => 1]);
        SinglePage::addGlobal(STACKS_PAGE_PATH);
        (new GlobalArea('Site Footer'))->create($page, 'Site Footer');

        $answer = $this->update($page, 'Site Footer', ['custom_style' => ['text_color' => '#ff0000']]);

        $stack = Stack::getByName('Site Footer');
        $styles = $stack->getVersionObject()->getCustomAreaStyles();
        static::assertArrayHasKey(STACKS_AREA_NAME, $styles, 'the stack holds the custom style of its global area');
        static::assertSame('#ff0000', StyleSetService::getByID($styles[STACKS_AREA_NAME])->getTextColor());
        // whoever may read the versions of a page reads the stack as it stands, drafts and all
        static::assertSame('#ff0000', $this->answered($answer)['custom_style']->text_color);
    }

    public function testAPageThatIsNotThereIsNotFound(): void
    {
        $answer = $this->areas('PUT', '/ccm/api/1.0/pages/999999/Main', [])->updateArea(999999, 'Main');

        static::assertInstanceOf(JsonResponse::class, $answer);
        static::assertSame(404, $answer->getStatusCode());
    }

    private function customStyleOf(Page $page, string $areaHandle): \Concrete\Core\Entity\StyleCustomizer\Inline\StyleSet
    {
        $style = $this->areaCustomStyle($page, $areaHandle);
        static::assertNotNull($style, 'the area was given no custom style at all');
        $styleSet = $style->getStyleSet();
        static::assertNotNull($styleSet);

        return $styleSet;
    }

    /**
     * @return \Concrete\Core\Area\CustomStyle|null
     */
    private function areaCustomStyle(Page $page, string $areaHandle)
    {
        // the custom styles of a version are read once per process, and an area is read again as another
        // request would read it
        app()->forgetInstance(\Concrete\Core\Area\CustomStyleRepository::class);
        $page = Page::getByID($page->getCollectionID(), 'RECENT');
        $area = Area::get($page, $areaHandle);

        return $area instanceof Area ? $page->getAreaCustomStyle($area) : null;
    }

    /**
     * @param \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse $answer
     *
     * @return array<string,mixed>
     */
    private function answered($answer): array
    {
        static::assertNotInstanceOf(JsonResponse::class, $answer);

        return $answer->getTransformer()->transform($answer->getData());
    }

    /**
     * @param array<string,mixed> $body
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    private function update(Page $page, string $areaHandle, array $body)
    {
        $url = '/ccm/api/1.0/pages/' . $page->getCollectionID() . '/' . rawurlencode($areaHandle);

        return $this->areas('PUT', $url, $body)->updateArea($page->getCollectionID(), $areaHandle);
    }

    /**
     * @param array<string,mixed> $body
     */
    private function areas(string $method, string $url, array $body): Areas
    {
        $request = Request::create($url, $method, [], [], [], [], (string) json_encode($body));

        return app()->make(Areas::class, ['request' => $request]);
    }
}
