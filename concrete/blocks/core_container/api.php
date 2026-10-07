<?php

declare(strict_types=1);

namespace Concrete\Block\CoreContainer;

use Concrete\Core\Api\Block\BlockApiHandler;
use Concrete\Core\Area\Area;
use Concrete\Core\Block\Block;
use Concrete\Core\Entity\Page\Container;
use Concrete\Core\Entity\Page\Container\Instance;
use Concrete\Core\Filesystem\TemplateService;
use Concrete\Core\Logging\Channels;
use Concrete\Core\Logging\LoggerFactory;
use Concrete\Core\Page\Container\ContainerBlockInstance;
use Concrete\Core\Page\Container\TemplateLocator;
use Concrete\Core\Page\Page;
use Doctrine\ORM\EntityManagerInterface;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @property \Concrete\Block\CoreContainer\Controller $controller
 */
class Api extends BlockApiHandler
{
    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\BlockApiHandler::getApiValueSchema()
     */
    public function getApiValueSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'container' => [
                    'type' => 'string',
                    'description' => 'The handle of the container, as GET /containers lists them. Sending another one gives the block a new instance of the container: the areas of the old instance keep their blocks in the page version, and the page stops showing them.',
                ],
                'areas' => [
                    'type' => 'array',
                    'readOnly' => true,
                    'description' => 'The areas of the container. The blocks placed in them are worked with through the areas endpoints.',
                    'items' => [
                        'type' => 'object',
                        'properties' => [
                            'name' => [
                                'type' => 'string',
                                'description' => 'The name the template of the container gives to the area.',
                            ],
                            'area' => [
                                'type' => 'string',
                                'description' => 'The handle of the area.',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\BlockApiHandler::getApiValue()
     */
    public function getApiValue(Block $block): array
    {
        $instance = $this->controller->getContainerInstanceObject();
        $container = $instance === null ? null : $instance->getContainer();
        if ($container === null) {
            return ['container' => '', 'areas' => []];
        }
        $areas = [];
        foreach ($instance->getInstanceAreas() as $instanceArea) {
            $areaHandle = (string) Area::getAreaHandleFromID($instanceArea->getAreaID());
            if ($areaHandle !== '') {
                $areas[] = [
                    'name' => (string) $instanceArea->getContainerAreaName(),
                    'area' => $areaHandle,
                ];
            }
        }

        return [
            'container' => $container->getContainerHandle(),
            'areas' => $areas,
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\BlockApiHandler::afterApiWrite()
     */
    public function afterApiWrite(Block $block): void
    {
        $instance = $this->controller->getContainerInstanceObject();
        if ($instance !== null) {
            $this->ensureInstanceAreas($block, $instance);
        }
    }

    /**
     * Give the instance of the container the areas it has none of, by running the template that shows
     * it and throwing away what it draws. A template that raises anything leaves it without them.
     */
    protected function ensureInstanceAreas(Block $block, Instance $instance): void
    {
        if (count($instance->getInstanceAreas()) !== 0) {
            return;
        }
        $page = $this->controller->getCollectionObject();
        if (!$page instanceof Page) {
            return;
        }
        $container = $instance->getContainer();
        $fileToRender = app(TemplateLocator::class)->getFileToRender($page, $container, true);
        if (!is_string($fileToRender) || $fileToRender === '') {
            return;
        }
        $templateService = app(TemplateService::class);
        $bufferLevel = ob_get_level();
        try {
            $templateService->renderTemplate($fileToRender, [
                'container' => app(ContainerBlockInstance::class, ['block' => $block, 'instance' => $instance]),
                'c' => $page,
                'templateService' => $templateService,
            ]);
        } catch (\Throwable $x) {
            app(LoggerFactory::class)->createLogger(Channels::CHANNEL_PAGES)->error(
                t('Failed to create the areas of the %s container of the block %s: %s', $container->getContainerHandle(), $block->getBlockID(), $x->getMessage())
            );
        } finally {
            // what a half-drawn template left buffered would otherwise leak into the answer
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
        }
    }

    /**
     * {@inheritdoc}
     *
     * @see \Concrete\Core\Api\Block\BlockApiHandler::getSaveArgumentsFromApiValue()
     */
    public function getSaveArgumentsFromApiValue(array $value, ?Block $block): array
    {
        $current = $block === null ? [] : $this->getApiValue($block);
        $handle = (string) ($value['container'] ?? $current['container'] ?? '');
        if ($handle !== '' && $handle === ($current['container'] ?? null)) {
            // the very same container: let's keep its instance, and the blocks placed in its areas
            return [];
        }
        $container = app(EntityManagerInterface::class)->getRepository(Container::class)->findOneBy(['containerHandle' => $handle]);

        return $container === null ? [] : ['containerID' => $container->getContainerID()];
    }
}
