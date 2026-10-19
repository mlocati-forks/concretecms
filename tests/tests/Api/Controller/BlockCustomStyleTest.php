<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Controller;

use Concrete\Core\Api\Controller\Areas;
use Concrete\Core\Block\Block;
use Concrete\Core\Entity\Package;
use Concrete\Core\Http\Request;
use Concrete\Core\Page\Page;
use Concrete\Core\Permission\Category as PermissionCategory;
use Concrete\Core\Permission\Key\Key as PermissionKey;
use Concrete\Core\User\User;
use Concrete\TestHelpers\Block\BlockApiTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A block takes the custom style it is given beside its value, and what a request says is held to the
 * rules the design dialog holds a form to.
 *
 * @see \Concrete\Core\Api\Controller\Areas::addBlock()
 * @see \Concrete\Core\Api\Controller\Areas::updateBlock()
 */
class BlockCustomStyleTest extends BlockApiTestCase
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Database\ConcreteDatabaseTestCase::getEntityClassNames()
     */
    protected function getEntityClassNames(): array
    {
        // the templates of a block type are looked for among the files of the installed packages too
        return array_merge(parent::getEntityClassNames(), [Package::class]);
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
        // writing a block asks how many blocks stand for it, which is a table of this block type
        $this->getBlockType(BLOCK_HANDLE_SCRAPBOOK_PROXY);
        // adding one asks the area about a key it builds itself, so the key has to be there
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

    public function testABlockIsAddedWithTheCustomStyleItIsGiven(): void
    {
        $page = self::createPage('Page with a block of its own custom style');

        $this->add($page, ['type' => 'page_title', 'value' => [], 'custom_style' => ['background_color' => 'rgba(255,0,0,1)', 'padding_top' => '20px']]);

        $style = $this->customStyleOf($this->getBlock($page));
        static::assertSame('rgba(255,0,0,1)', $style->getBackgroundColor());
        static::assertSame('20px', $style->getPaddingTop());
    }

    public function testABlockThatNamesNoCustomStyleIsAddedWithNone(): void
    {
        $page = self::createPage('Page with a block with no custom style');

        $this->add($page, ['type' => 'page_title', 'value' => []]);

        static::assertNull($this->getBlock($page)->getCustomStyle());
    }

    /**
     * A custom style in which nothing holds is none, as it is for the dialog.
     */
    public function testACustomStyleWithNothingThatHoldsIsNone(): void
    {
        $page = self::createPage('Page with a block whose custom style says nothing');

        $this->add($page, ['type' => 'page_title', 'value' => [], 'custom_style' => ['background_color' => 'not a color', 'margin_top' => '0px']]);

        static::assertNull($this->getBlock($page)->getCustomStyle());
    }

    /**
     * A shadow of no size at all is no shadow, whatever the units its sizes are written with.
     */
    public function testAShadowOfNoSizeAtAllIsNoCustomStyle(): void
    {
        $page = self::createPage('Page with a block whose shadow has no size');

        $this->add($page, ['type' => 'page_title', 'value' => [], 'custom_style' => [
            'box_shadow_horizontal' => '0rem',
            'box_shadow_vertical' => '0px',
            'box_shadow_blur' => '0%',
            'box_shadow_spread' => '0em',
            'box_shadow_color' => 'rgba(1,2,3,1)',
        ]]);

        static::assertNull($this->getBlock($page)->getCustomStyle());
    }

    public function testTheCustomStyleOfABlockIsChanged(): void
    {
        $page = self::createPage('Page with a block to style again');
        $block = $this->addBlock('page_title', [], $page);

        $this->update($page, $block, ['value' => [], 'custom_style' => ['text_color' => '#ff0000']]);

        static::assertSame('#ff0000', $this->customStyleOf($this->getBlock($page))->getTextColor());
    }

    public function testTheCustomStyleOfABlockIsDropped(): void
    {
        $page = self::createPage('Page with a block back to its theme');
        $block = $this->addBlock('page_title', [], $page);
        $this->update($page, $block, ['value' => [], 'custom_style' => ['text_color' => '#ff0000']]);

        $this->update($page, $this->getBlock($page), ['value' => [], 'custom_style' => null]);

        static::assertNull($this->getBlock($page)->getCustomStyle());
    }

    /**
     * What a client writes is what the design dialog would have written, and nothing else gets stored.
     */
    public function testWhatIsNotACssValueIsNotStored(): void
    {
        $page = self::createPage('Page with a block asked for the impossible');
        $block = $this->addBlock('page_title', [], $page);

        $this->update($page, $block, ['value' => [], 'custom_style' => [
            'text_color' => 'rgba(0,0,0,1)',
            'margin_top' => '10 miles',
            'alignment' => 'sideways',
            'custom_element_attribute' => 'class="sneaky" data-x="1"',
        ]]);

        $style = $this->customStyleOf($this->getBlock($page));
        static::assertSame('rgba(0,0,0,1)', $style->getTextColor());
        static::assertNull($style->getMarginTop());
        static::assertNull($style->getAlignment());
        static::assertSame('data-x="1"', $style->getCustomElementAttribute());
    }

    public function testAnAttributeWithAnUnclosedQuoteIsRefused(): void
    {
        $page = self::createPage('Page with a block asked for an unclosed quote');
        $block = $this->addBlock('page_title', [], $page);

        $answer = $this->update($page, $block, ['value' => [], 'custom_style' => ['custom_element_attribute' => 'data-x="1']]);

        static::assertInstanceOf(JsonResponse::class, $answer);
        static::assertSame(400, $answer->getStatusCode());
        static::assertNull($this->getBlock($page)->getCustomStyle());
    }

    /**
     * A client sends back what it read, and reading a custom style gives the fields the dialog saved: a
     * request that asks for the same custom style asks for nothing.
     */
    public function testARequestLeavingTheCustomStyleAsItFoundItKeepsIt(): void
    {
        $page = self::createPage('Page with a block whose custom style stays');
        $block = $this->addBlock('page_title', [], $page);
        $this->update($page, $block, ['value' => [], 'custom_style' => ['background_color' => 'rgba(1,2,3,1)']]);
        $wasSet = $this->customStyleOf($this->getBlock($page));

        $this->update($page, $this->getBlock($page), ['value' => [], 'custom_style' => [
            'background_color' => 'rgba(1,2,3,1)',
            'background_repeat' => 'no-repeat',
            'background_size' => 'auto',
            'background_position' => 'left top',
        ]]);

        $isSet = $this->customStyleOf($this->getBlock($page));
        static::assertSame($wasSet->getID(), $isSet->getID());
        static::assertSame('rgba(1,2,3,1)', $isSet->getBackgroundColor());
    }

    private function customStyleOf(Block $block): \Concrete\Core\Entity\StyleCustomizer\Inline\StyleSet
    {
        $style = $block->getCustomStyle();
        static::assertNotNull($style, 'the block was given no custom style at all');
        $styleSet = $style->getStyleSet();
        static::assertNotNull($styleSet);

        return $styleSet;
    }

    /**
     * @param array<string,mixed> $body
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    private function add(Page $page, array $body)
    {
        $this->getBlockType((string) $body['type']);
        $url = '/ccm/api/1.0/pages/' . $page->getCollectionID() . '/Main';

        return $this->areas('POST', $url, $body)->addBlock($page->getCollectionID(), 'Main');
    }

    /**
     * @param array<string,mixed> $body
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    private function update(Page $page, Block $block, array $body)
    {
        $url = '/ccm/api/1.0/pages/' . $page->getCollectionID() . '/Main/' . $block->getBlockID();

        return $this->areas('PUT', $url, $body)->updateBlock($page->getCollectionID(), 'Main', $block->getBlockID());
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
