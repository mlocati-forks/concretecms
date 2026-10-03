<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Controller;

use Concrete\Core\Api\ApiController;
use Concrete\Core\Api\Fractal\Transformer\LayoutPresetTransformer;
use Concrete\Core\Api\Resources;
use Concrete\Core\Area\Layout\Preset\Provider\ThemeProvider;
use Concrete\Core\Area\Layout\Preset\Provider\ThemeProviderInterface;
use Concrete\Core\Area\Layout\Preset\Provider\UserProvider;
use Concrete\Core\Page\Theme\Theme as PageTheme;
use League\Fractal\Resource\Collection;

defined('C5_EXECUTE') or die('Access Denied.');

class LayoutPresets extends ApiController
{
    /**
     * @OA\Get(
     *     path="/ccm/api/1.0/layout_presets",
     *     tags={"layout_presets"},
     *     operationId="getLayoutPresets",
     *     summary="List the ready-made layouts that a core_area_layout block can be given, both the ones defined here and the ones the themes offer",
     *     security={
     *         {"clientCredentials": {"definitions:read"}},
     *         {"authorization": {"definitions:read"}}
     *     },
     *     @OA\Parameter(
     *         name="include_user_defined",
     *         in="query",
     *         description="Whether the layouts defined in this installation are listed (default: true)",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="include_theme_provided",
     *         in="query",
     *         description="Whether the layouts offered by the page themes are listed (default: true)",
     *         @OA\Schema(type="boolean")
     *     ),
     *     @OA\Parameter(
     *         name="page_theme",
     *         in="query",
     *         description="The handle of the page theme whose layouts are listed, leaving out the ones of the other page themes",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(ref="#/components/schemas/LayoutPreset")
     *             )
     *         ),
     *     ),
     * )
     *
     * @return \League\Fractal\Resource\Collection
     */
    public function listLayoutPresets()
    {
        $rows = [];
        if ($this->request->query->getBoolean('include_user_defined', true)) {
            $transformer = new LayoutPresetTransformer();
            foreach ($this->app->make(UserProvider::class)->getPresets() as $preset) {
                $rows[] = $transformer->transform($preset);
            }
        }
        if ($this->request->query->getBoolean('include_theme_provided', true)) {
            $wantedTheme = (string) $this->request->query->get('page_theme', '');
            foreach (PageTheme::getList() as $theme) {
                $themeHandle = (string) $theme->getThemeHandle();
                if (!$theme instanceof ThemeProviderInterface || ($wantedTheme !== '' && $themeHandle !== $wantedTheme)) {
                    continue;
                }
                $transformer = new LayoutPresetTransformer($themeHandle);
                foreach ((new ThemeProvider($theme))->getPresets() as $preset) {
                    $rows[] = $transformer->transform($preset);
                }
            }
        }

        return new Collection($rows, static function (array $row): array {
            return $row;
        }, Resources::RESOURCE_LAYOUT_PRESETS);
    }
}
