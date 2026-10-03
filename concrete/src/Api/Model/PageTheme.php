<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="PageTheme model",
 * )
 */
class PageTheme implements \JsonSerializable
{
    /**
     * @OA\Property(title="Page Theme Handle")
     *
     * @var string
     */
    public $handle;

    /**
     * @OA\Property(title="Page Theme Name")
     *
     * @var string
     */
    public $name;

    /**
     * @OA\Property(title="Page Theme Description")
     *
     * @var string
     */
    public $description;

    /**
     * @OA\Property(title="Handle of the package providing the page theme, empty when it belongs to none")
     *
     * @var string
     */
    public $package;

    /**
     * @OA\Property(ref="#/components/schemas/PageThemeGrid", nullable=true, title="Grid framework of the theme, NULL when it declares none")
     *
     * @var \Concrete\Core\Api\Model\PageThemeGrid|null
     */
    public $grid;

    /**
     * @OA\Property(type="array", title="Widths the theme shows its responsive images at", description="What the thumbnails of an image block are keyed by", @OA\Items(ref="#/components/schemas/PageThemeImageBreakpoint"))
     *
     * @var \Concrete\Core\Api\Model\PageThemeImageBreakpoint[]
     */
    public $image_breakpoints;

    /**
     * @OA\Property(type="array", title="Ready-made layouts the theme offers to the pages shown with it", @OA\Items(ref="#/components/schemas/LayoutPreset"))
     *
     * @var \Concrete\Core\Api\Model\LayoutPreset[]
     */
    public $presets;

    /**
     * @OA\Property(type="array", title="Containers the theme carries the template of", @OA\Items(ref="#/components/schemas/Container"))
     *
     * @var \Concrete\Core\Api\Model\Container[]
     */
    public $containers;

    /**
     * {@inheritdoc}
     *
     * @see \JsonSerializable::jsonSerialize()
     *
     * @return array<string,mixed>
     */
    public function jsonSerialize(): array
    {
        return (array) $this;
    }
}
