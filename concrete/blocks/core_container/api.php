<?php

declare(strict_types=1);

namespace Concrete\Block\CoreContainer;

use Concrete\Core\Api\Block\BlockApiHandler;
use Concrete\Core\Area\Area;
use Concrete\Core\Block\Block;
use Concrete\Core\Entity\Page\Container;
use Doctrine\ORM\EntityManagerInterface;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * The core_container block type displays a container of the theme, and keeps in its table just the
 * ID of the instance of that container: the value is the handle of the container, along with the
 * areas its template creates.
 *
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
                    'description' => 'The handle of the container of the theme. Sending another one empties the block, since the areas belong to the container itself.',
                ],
                'areas' => [
                    'type' => 'array',
                    'readOnly' => true,
                    'description' => 'The areas of the container, which its template creates: they are there once the page has been displayed at least once. The blocks placed in them are worked with through the areas endpoints.',
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
