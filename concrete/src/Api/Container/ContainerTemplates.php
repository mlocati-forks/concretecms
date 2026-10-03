<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Container;

use Concrete\Core\Application\Application;
use Concrete\Core\Entity\Page\Container;
use Concrete\Core\Filesystem\FileLocator;
use Concrete\Core\Filesystem\FileLocator\LocationInterface;
use Concrete\Core\Page\Theme\Theme as PageTheme;
use Doctrine\ORM\EntityManagerInterface;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The containers registered in this installation, each shown by the elements/containers template that
 * a theme, the application directory or the package of the container carries.
 */
class ContainerTemplates
{
    /**
     * @var \Concrete\Core\Application\Application
     */
    protected $app;

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    protected $entityManager;

    public function __construct(Application $app, EntityManagerInterface $entityManager)
    {
        $this->app = $app;
        $this->entityManager = $entityManager;
    }

    /**
     * Get every container registered in this installation, by name.
     *
     * @return \Concrete\Core\Entity\Page\Container[]
     */
    public function getContainers(): array
    {
        return $this->entityManager->getRepository(Container::class)->findBy([], ['containerName' => 'asc']);
    }

    /**
     * Get the containers that a theme carries the template of.
     *
     * @return \Concrete\Core\Entity\Page\Container[]
     */
    public function getContainersOfTheme(PageTheme $theme): array
    {
        $containers = [];
        foreach ($this->getContainers() as $container) {
            if ($this->isShownByTheme($container, $theme)) {
                $containers[] = $container;
            }
        }

        return $containers;
    }

    /**
     * Get the handles of the themes a container can be shown with.
     *
     * @return string[]
     */
    public function getThemeHandlesShowing(Container $container): array
    {
        $handles = [];
        foreach (PageTheme::getList() as $theme) {
            if ($this->isShownByTheme($container, $theme)) {
                $handles[] = (string) $theme->getThemeHandle();
            }
        }

        return $handles;
    }

    /**
     * Is the template that shows a container among the files a theme uses?
     */
    public function isShownByTheme(Container $container, PageTheme $theme): bool
    {
        $themeLocation = $this->app->make(FileLocator\ThemeLocation::class);
        $themeLocation->setTheme($theme);

        return $this->hasTemplate($container, $themeLocation);
    }

    /**
     * Is the template that shows a container in the application directory, which shows it whatever theme
     * a page is given?
     */
    public function isShownByApplication(Container $container): bool
    {
        return $this->hasTemplate($container, null);
    }

    /**
     * Look the template of a container up the way the core does when it renders it, which searches the
     * application directory, the location given, the package of the container and the core.
     */
    protected function hasTemplate(Container $container, ?LocationInterface $location): bool
    {
        $handle = $container->getContainerHandle();
        if ($handle === '') {
            return false;
        }
        $locator = $this->app->make(FileLocator::class);
        if ($location !== null) {
            $locator->addLocation($location);
        }
        $packageHandle = (string) $container->getPackageHandle();
        if ($packageHandle !== '') {
            $locator->addPackageLocation($packageHandle);
        }
        $record = $locator->getRecord(DIRNAME_ELEMENTS . '/' . DIRNAME_CONTAINERS . '/' . $handle . '.php');

        return $record !== null && $record->exists();
    }
}
