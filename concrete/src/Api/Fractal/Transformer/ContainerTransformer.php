<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Container\ContainerTemplates;
use Concrete\Core\Entity\Page\Container;
use League\Fractal\TransformerAbstract;

defined('C5_EXECUTE') or die('Access Denied.');

class ContainerTransformer extends TransformerAbstract
{
    /**
     * @var \Concrete\Core\Api\Container\ContainerTemplates
     */
    protected $containerTemplates;

    public function __construct(ContainerTemplates $containerTemplates)
    {
        $this->containerTemplates = $containerTemplates;
    }

    /**
     * Get what the API hands to its clients for a container.
     *
     * @return array<string,mixed>
     */
    public function transform(Container $container): array
    {
        return [
            'handle' => $container->getContainerHandle(),
            'name' => $container->getContainerName(),
            'package' => (string) $container->getPackageHandle(),
            'page_themes' => $this->containerTemplates->getThemeHandlesShowing($container),
            'application' => $this->containerTemplates->isShownByApplication($container),
        ];
    }
}
