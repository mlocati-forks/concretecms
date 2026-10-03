<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Fractal\Transformer;

use Concrete\Core\Api\Container\ContainerTemplates;
use Concrete\Core\Api\Model\Container as ContainerModel;
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
     * @return array<string,mixed>
     */
    public function transform(Container $container): array
    {
        $model = new ContainerModel();
        $model->handle = $container->getContainerHandle();
        $model->name = $container->getContainerName();
        $model->package = (string) $container->getPackageHandle();
        $model->page_themes = $this->containerTemplates->getThemeHandlesShowing($container);
        $model->application = $this->containerTemplates->isShownByApplication($container);

        return $model->jsonSerialize();
    }
}
