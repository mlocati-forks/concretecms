<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="PageTheme model",
 * )
 */
class PageTheme
{
    /**
     * @OA\Property(type="string", title="Page Theme Handle")
     *
     * @var string
     */
    private $handle;

    /**
     * @OA\Property(type="string", title="Page Theme Name")
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(type="string", title="Page Theme Description")
     *
     * @var string
     */
    private $description;

    /**
     * @OA\Property(type="string", title="Handle of the package providing the page theme, empty when it belongs to none")
     *
     * @var string
     */
    private $package;

    /**
     * @OA\Property(type="array", title="Ready-made layouts the theme offers to the pages shown with it", @OA\Items(ref="#/components/schemas/LayoutPreset"))
     *
     * @var \Concrete\Core\Api\Model\LayoutPreset[]
     */
    private $presets;

    /**
     * @OA\Property(ref="#/components/schemas/PageThemeGrid", nullable=true, title="Grid framework of the theme, NULL when it declares none")
     *
     * @var \Concrete\Core\Api\Model\PageThemeGrid|null
     */
    private $grid;
}
