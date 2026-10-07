<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Controller;

use Concrete\Core\Api\Controller\Areas;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\Entity\Package;
use Concrete\Core\Entity\Page\Container;
use Concrete\Core\Entity\Page\Container\Instance;
use Concrete\Core\Entity\Page\Container\InstanceArea;
use Concrete\Core\Http\Request;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Theme\Theme as PageTheme;
use Concrete\Core\User\User;
use Concrete\TestHelpers\Block\BlockApiTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A page that nothing has drawn has no areas to hand over: this endpoint draws it.
 *
 * @see \Concrete\Core\Api\Controller\Areas::refreshAreas()
 */
class RefreshAreasTest extends BlockApiTestCase
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Database\ConcreteDatabaseTestCase::getTables()
     */
    protected function getTables()
    {
        // drawing a page reads the configuration of the site, the styles of its theme and its stacks
        return array_merge(parent::getTables(), ['Config', 'PageThemeCustomStyles', 'Stacks']);
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\TestHelpers\Database\ConcreteDatabaseTestCase::getEntityClassNames()
     */
    protected function getEntityClassNames(): array
    {
        return array_merge(parent::getEntityClassNames(), [
            Container::class,
            Instance::class,
            InstanceArea::class,
            // the template of a container is looked for among the files of the installed packages too
            Package::class,
        ]);
    }

    public function setUp(): void
    {
        parent::setUp();
        // with no permission key registered, only the superuser is allowed anything
        $superUser = $this->createMock(User::class);
        $superUser->method('isSuperUser')->willReturn(true);
        app()->instance(User::class, $superUser);
    }

    public function tearDown(): void
    {
        app()->forgetInstance(User::class);
        parent::tearDown();
    }

    public function testThePageIsDrawnAndTheAreasOfItsTemplateAreThere(): void
    {
        $page = $this->createThemedPage();

        static::assertSame([], $this->getStoredAreas($page));

        $response = $this->call($page->getCollectionID());

        static::assertSame(204, $response->getStatusCode());
        static::assertSame('', $response->getContent());
        static::assertSame(['Main', 'Page Footer', 'Page Header'], $this->getStoredAreas($page));
    }

    /**
     * A container brings areas of its own, which the template showing it creates while the page is drawn.
     */
    public function testTheAreasOfAContainerInThePageComeToo(): void
    {
        $page = $this->createThemedPage();
        $this->addContainer($page);

        static::assertSame(204, $this->call($page->getCollectionID())->getStatusCode());

        $areas = $this->getStoredAreas($page);
        static::assertContains('Main', $areas);
        foreach (['Column 1', 'Column 2'] as $column) {
            $named = array_filter($areas, static function ($area) use ($column) {
                return substr($area, -strlen($column)) === $column;
            });
            static::assertNotSame([], $named, "No area of the container is named {$column}: " . implode(', ', $areas));
        }
    }

    public function testAPageThatIsNotThereIsNotDrawn(): void
    {
        $response = $this->call(PHP_INT_MAX);

        static::assertSame(404, $response->getStatusCode());
    }

    /**
     * @return string[] the areas of the page, as the database keeps them
     */
    private function getStoredAreas(Page $page): array
    {
        return app(Connection::class)->fetchFirstColumn(
            'SELECT arHandle FROM Areas WHERE cID = ? ORDER BY arHandle',
            [$page->getCollectionID()]
        );
    }

    private function call(int $pageID): Response
    {
        return app()->make(Areas::class, ['request' => Request::createFromGlobals()])->refreshAreas($pageID);
    }

    /**
     * Put a container in a page, the way a client does.
     */
    private function addContainer(Page $page): void
    {
        $entityManager = app(EntityManagerInterface::class);
        $container = new Container();
        $container->setContainerHandle('two_column_light');
        $container->setContainerName('Two Column Light');
        $entityManager->persist($container);
        $entityManager->flush();
        $this->addBlockFromApiValue('core_container', ['container' => 'two_column_light'], $page);
    }

    private function createThemedPage(): Page
    {
        // the blank template declares three areas and brings no global area, whose stack this would create
        \PageTemplate::add('blank', 'Blank');
        $page = self::createPage('Page to draw', false, false, 'blank');
        $page->setTheme(PageTheme::getByHandle('atomik') ?? PageTheme::add('atomik'));

        return Page::getByID($page->getCollectionID(), 'RECENT');
    }
}
