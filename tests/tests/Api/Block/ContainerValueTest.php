<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Block;

use Concrete\Core\Block\Block;
use Concrete\Core\Entity\Package;
use Concrete\Core\Entity\Page\Container;
use Concrete\Core\Entity\Page\Container\Instance;
use Concrete\Core\Entity\Page\Container\InstanceArea;
use Concrete\Core\Page\Theme\Theme as PageTheme;
use Concrete\Core\User\User;
use Concrete\TestHelpers\Block\BlockApiTestCase;
use Doctrine\ORM\EntityManagerInterface;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * A container brings areas of its own, which only the template showing it creates.
 *
 * @see \Concrete\Block\CoreContainer\Api::afterApiWrite()
 */
class ContainerValueTest extends BlockApiTestCase
{
    /**
     * The container the tests put in a page: the atomik theme carries its template, which makes two
     * areas.
     *
     * @var string
     */
    private const CONTAINER_HANDLE = 'two_column_light';

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

    public function testTheContainerIsAnsweredWithTheAreasOfItsTemplate(): void
    {
        $value = $this->getApiValue($this->addContainerToAPage());

        static::assertSame(self::CONTAINER_HANDLE, $value['container']);
        static::assertSame(['Column 1', 'Column 2'], array_column($value['areas'], 'name'));
    }

    /**
     * The handle of an area is what the areas endpoints take to place a block in it.
     */
    public function testEveryAreaCarriesTheHandleThatNamesIt(): void
    {
        $value = $this->getApiValue($this->addContainerToAPage());

        static::assertNotSame([], $value['areas']);
        foreach ($value['areas'] as $area) {
            static::assertStringStartsWith('Main', $area['area']);
            static::assertStringEndsWith($area['name'], $area['area']);
        }
    }

    /**
     * Another container gives the block a new instance, with the areas that the template of that one
     * makes.
     */
    public function testTheBlockTakesAnotherContainer(): void
    {
        $block = $this->addContainerToAPage();
        $this->registerContainerNamed('light_stripe', 'Light Stripe');

        $this->updateBlock($block, ['container' => 'light_stripe']);
        $value = $this->getApiValue($block);

        static::assertSame('light_stripe', $value['container']);
        static::assertSame(['Title', 'Body'], array_column($value['areas'], 'name'));
    }

    public function testTheAreasAreCreatedOnlyOnce(): void
    {
        $block = $this->addContainerToAPage();
        $areas = $this->getApiValue($block)['areas'];

        $this->updateBlock($block, ['container' => self::CONTAINER_HANDLE]);

        static::assertSame($areas, $this->getApiValue($block)['areas']);
    }

    private function addContainerToAPage(): Block
    {
        $page = self::createPage('Page with a container');
        $page->setTheme(PageTheme::getByHandle('atomik') ?? PageTheme::add('atomik'));

        return $this->addBlockFromApiValue('core_container', ['container' => $this->registerContainer()], $page);
    }

    /**
     * @return string the handle of the container registered in this installation
     */
    private function registerContainer(): string
    {
        return $this->registerContainerNamed(self::CONTAINER_HANDLE, 'Two Column Light');
    }

    private function registerContainerNamed(string $handle, string $name): string
    {
        $entityManager = app(EntityManagerInterface::class);
        $container = new Container();
        $container->setContainerHandle($handle);
        $container->setContainerName($name);
        $entityManager->persist($container);
        $entityManager->flush();

        return $handle;
    }
}
