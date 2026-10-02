<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Controller;

use Concrete\Core\Api\Controller\Pages;
use Concrete\Core\Entity\Block\BlockType\BlockType as BlockTypeEntity;
use Concrete\Core\Http\Request;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Theme\Theme as PageTheme;
use Concrete\Core\Permission\Response\PageResponse;
use Concrete\TestHelpers\Page\PageTestCase;
use League\Fractal\Manager;
use Symfony\Component\HttpFoundation\JsonResponse;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A client adding a page may name the theme it is shown with, which is a thing of its own to be
 * allowed: the page is not there yet to be asked about, so its parent answers for it.
 *
 * @see \Concrete\Core\Api\Controller\Pages::add()
 */
class PagesAddTest extends PageTestCase
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Database\ConcreteDatabaseTestCase::getTables()
     */
    protected function getTables()
    {
        return array_merge(parent::getTables(), [
            'PageTypePageTemplateDefaultPages',
            'AreaPermissionAssignments',
            'Blocks',
            'BlockTypeSets',
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Database\ConcreteDatabaseTestCase::getEntityClassNames()
     */
    protected function getEntityClassNames(): array
    {
        return array_merge(parent::getEntityClassNames(), [
            BlockTypeEntity::class,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Database\ConcreteDatabaseTestCase::setUpBeforeClass()
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        PageTheme::add('atomik');
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Tests\TestCase::tearDown()
     */
    public function tearDown(): void
    {
        app()->forgetInstance($this->getHomePage()->getPermissionResponseClassName());
        parent::tearDown();
    }

    public function testAClientAllowedTheThemeAddsAPageWithIt(): void
    {
        $this->answerAboutThePermissions(true);

        $page = $this->theAddedPage($this->add(['name' => 'With a theme', 'theme' => 'atomik']));

        static::assertSame('atomik', (string) $page->getCollectionThemeObject()->getThemeHandle());
    }

    public function testAClientDeniedTheThemeIsTurnedAway(): void
    {
        $this->answerAboutThePermissions(false);
        $children = $this->getHomePage()->getNumChildren();

        $response = $this->add(['name' => 'Denied the theme', 'theme' => 'atomik']);

        static::assertInstanceOf(JsonResponse::class, $response);
        static::assertSame(403, $response->getStatusCode());
        // the request is turned away before the page is there
        static::assertSame($children, $this->getHomePage()->getNumChildren());
    }

    public function testAPageNamingNoThemeIsAddedWithoutThatPermission(): void
    {
        $this->answerAboutThePermissions(false);

        $page = $this->theAddedPage($this->add(['name' => 'No theme at all']));

        static::assertEmpty($page->isError());
    }

    /**
     * @param \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse $answer
     */
    private function theAddedPage($answer): Page
    {
        static::assertNotInstanceOf(JsonResponse::class, $answer);

        return Page::getByID((new Manager())->createData($answer)->toArray()['data']['id']);
    }

    /**
     * Have the pages of this installation answer about their permissions without the permission keys
     * of a real one: adding a page beneath the home page is allowed, the theme is what the test says.
     */
    private function answerAboutThePermissions(bool $mayEditTheTheme): void
    {
        $response = $this->createMock(PageResponse::class);
        $response->method('canAddSubCollection')->willReturn(true);
        // the theme is not asked about by a method of its own: the response answers whatever it is asked
        $response->method('__call')->willReturnCallback(static function (string $method) use ($mayEditTheTheme) {
            return $method === 'canEditPageTheme' ? $mayEditTheTheme : false;
        });
        app()->instance($this->getHomePage()->getPermissionResponseClassName(), $response);
    }

    /**
     * @param array<string,mixed> $body what the client sends, beyond the parent, the type and the template
     *
     * @return \League\Fractal\Resource\Item|\Symfony\Component\HttpFoundation\JsonResponse
     */
    private function add(array $body)
    {
        $body += [
            'parent' => $this->getHomePage()->getCollectionID(),
            'type' => 'basic',
            'template' => 'full',
        ];
        $controller = new Pages(Request::create('/ccm/api/1.0/pages', 'POST', [], [], [], [], json_encode($body)));
        $controller->setApplication(app());

        return $controller->add();
    }

    private function getHomePage(): Page
    {
        return Page::getByID(Page::getHomePageID());
    }
}
