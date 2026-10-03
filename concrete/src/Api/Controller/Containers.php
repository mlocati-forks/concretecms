<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Container\ContainerTemplates;
use Concrete\Core\Api\Fractal\Transformer\ContainerTransformer;
use Concrete\Core\Api\Resources;
use Concrete\Core\Entity\Page\Container;
use Concrete\Core\Page\Theme\Theme as PageTheme;
use League\Fractal\Resource\Collection;

defined('C5_EXECUTE') or die('Access Denied.');

class Containers extends ApiController
{
    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/containers",
     *     tags={"containers"},
     *     operationId="getContainers",
     *     summary="List the containers that a core_container block can show",
     *     security={
     *         {"clientCredentials": {"definitions:read"}},
     *         {"authorization": {"definitions:read"}}
     *     },
     *     @OA\Parameter(
     *         name="include_application_provided",
     *         in="query",
     *         description="Whether the containers whose template lives in the application directory of this installation are listed (default: true)",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="page_theme",
     *         in="query",
     *         description="Lists only the containers that a page shown with the page theme of this handle can display",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Container")
     *         ),
     *     ),
     * )
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function listContainers()
    {
        $containerTemplates = $this->app->make(ContainerTemplates::class);
        $wantedTheme = (string) $this->request->query->get('page_theme', '');
        if ($wantedTheme === '') {
            $containers = $containerTemplates->getContainers();
        } else {
            $theme = PageTheme::getByHandle($wantedTheme);
            $containers = $theme === null ? [] : $containerTemplates->getContainersOfTheme($theme);
        }
        if (!$this->request->query->getBoolean('include_application_provided', true)) {
            $containers = array_values(array_filter($containers, static function (Container $container) use ($containerTemplates): bool {
                return !$containerTemplates->isShownByApplication($container);
            }));
        }

        return new Collection($containers, new ContainerTransformer($containerTemplates), Resources::RESOURCE_CONTAINERS);
    }
}
