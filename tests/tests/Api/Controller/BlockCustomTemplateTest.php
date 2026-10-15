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
 * A block takes the custom template it is shown with beside its value. No theme is installed here, so the
 * templates these tests ask for are the ones the core gives to page_title.
 *
 * @see \Concrete\Core\Api\Controller\Areas::addBlock()
 * @see \Concrete\Core\Api\Controller\Areas::updateBlock()
 */
class BlockCustomTemplateTest extends BlockApiTestCase
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

    public function testABlockIsAddedWithTheTemplateItIsGiven(): void
    {
        $page = self::createPage('Page with a block shown its own way');

        $answer = $this->add($page, ['type' => 'page_title', 'value' => [], 'custom_template' => 'byline.php']);

        static::assertNotInstanceOf(JsonResponse::class, $answer, $answer instanceof JsonResponse ? (string) $answer->getContent() : '');
        static::assertSame('byline.php', (string) $this->getBlock($page)->getBlockFilename());
    }

    public function testABlockThatNamesNoTemplateIsAddedWithNone(): void
    {
        $page = self::createPage('Page with a block shown the usual way');

        $this->add($page, ['type' => 'page_title', 'value' => []]);

        static::assertSame('', (string) $this->getBlock($page)->getBlockFilename());
    }

    public function testATemplateTheBlockTypeHasNotIsRefusedWhenAdding(): void
    {
        $page = self::createPage('Page refusing a template');

        $answer = $this->add($page, ['type' => 'page_title', 'value' => [], 'custom_template' => 'there_is_no_such_template.php']);

        static::assertInstanceOf(JsonResponse::class, $answer);
        static::assertSame(400, $answer->getStatusCode());
        static::assertSame([], Page::getByID($page->getCollectionID(), 'RECENT')->getBlocks('Main'));
    }

    public function testTheTemplateOfABlockIsChanged(): void
    {
        $page = self::createPage('Page with a block to show another way');
        $block = $this->addBlock('page_title', [], $page);

        $answer = $this->update($page, $block, ['value' => [], 'custom_template' => 'archive.php']);

        static::assertNotInstanceOf(JsonResponse::class, $answer);
        static::assertSame('archive.php', (string) $this->getBlock($page)->getBlockFilename());
    }

    public function testTheTemplateOfABlockIsDropped(): void
    {
        $page = self::createPage('Page with a block back to the usual way');
        $block = $this->addBlock('page_title', [], $page);
        $block->setCustomTemplate('byline.php');

        $this->update($page, $block, ['value' => [], 'custom_template' => '']);

        static::assertSame('', (string) $this->getBlock($page)->getBlockFilename());
    }

    public function testATemplateTheBlockTypeHasNotIsRefusedWhenUpdating(): void
    {
        $page = self::createPage('Page refusing a template on a block of its own');
        $block = $this->addBlock('page_title', [], $page);

        $answer = $this->update($page, $block, ['value' => [], 'custom_template' => 'there_is_no_such_template.php']);

        static::assertInstanceOf(JsonResponse::class, $answer);
        static::assertSame(400, $answer->getStatusCode());
    }

    /**
     * A client sends back what it read, and what it read may have stopped being one of the templates of
     * the block type: the page may have changed theme since.
     */
    public function testARequestLeavingTheTemplateAsItFoundItIsAskedNothing(): void
    {
        $page = self::createPage('Page with a block shown a way that is gone');
        $block = $this->addBlock('page_title', [], $page);
        $block->setCustomTemplate('the_theme_that_gave_this_is_gone.php');

        $answer = $this->update($page, $this->getBlock($page), ['value' => [], 'custom_template' => 'the_theme_that_gave_this_is_gone.php']);

        static::assertNotInstanceOf(JsonResponse::class, $answer);
        static::assertSame('the_theme_that_gave_this_is_gone.php', (string) $this->getBlock($page)->getBlockFilename());
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
