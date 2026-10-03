<?php

declare(strict_types=1);

namespace Concrete\Tests\Api\Container;

use Concrete\Core\Api\Container\ContainerTemplates;
use Concrete\Core\Api\Fractal\Transformer\ContainerTransformer;
use Concrete\Core\Entity\Package;
use Concrete\Core\Entity\Page\Container;
use Concrete\Tests\TestCase;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * Tests what the containers endpoint hands to its clients.
 *
 * @see \Concrete\Core\Api\Fractal\Transformer\ContainerTransformer
 */
class ContainerTransformerTest extends TestCase
{
    public function testAContainerNamesTheThemesShowingIt(): void
    {
        $container = (new Container())
            ->setContainerHandle('two_column_light')
            ->setContainerName('Two Column Highlight')
        ;

        static::assertSame([
            'handle' => 'two_column_light',
            'name' => 'Two Column Highlight',
            'package' => '',
            'page_themes' => ['atomik'],
            'application' => false,
        ], $this->createTransformer(['atomik'], false)->transform($container));
    }

    public function testAContainerOfTheApplicationDirectorySaysSo(): void
    {
        $container = (new Container())->setContainerHandle('hero')->setContainerName('Hero');

        $transformed = $this->createTransformer(['atomik', 'elemental'], true)->transform($container);

        static::assertSame(['atomik', 'elemental'], $transformed['page_themes']);
        static::assertTrue($transformed['application']);
    }

    public function testAContainerOfAPackageNamesIt(): void
    {
        $package = new Package();
        $package->setPackageHandle('my_package');
        $container = (new Container())->setContainerHandle('hero')->setContainerName('Hero');
        $container->setPackage($package);

        static::assertSame('my_package', $this->createTransformer()->transform($container)['package']);
    }

    /**
     * @param string[] $themeHandles the handles of the themes that can show the container
     */
    private function createTransformer(array $themeHandles = [], bool $shownByApplication = false): ContainerTransformer
    {
        $containerTemplates = $this->createMock(ContainerTemplates::class);
        $containerTemplates->method('getThemeHandlesShowing')->willReturn($themeHandles);
        $containerTemplates->method('isShownByApplication')->willReturn($shownByApplication);

        return new ContainerTransformer($containerTemplates);
    }
}
