<?php

declare(strict_types=1);

namespace Concrete\Core\Api\Model;

defined('C5_EXECUTE') or die('Access Denied.');

/**
 * @OA\Schema(
 *     title="SiteDefaults model",
 * )
 */
class SiteDefaults
{
    /**
     * @OA\Property(type="string", title="Handle of the page theme the pages are shown with, unless a page carries one of its own")
     *
     * @var string
     */
    private $page_theme;

    /**
     * @OA\Property(type="string", title="Identifier of the skin that theme is shown with, empty for the skin named default")
     *
     * @var string
     */
    private $page_theme_skin;

    /**
     * @OA\Property(type="string", title="Identifier of the skin that theme is shown with in dark mode, empty for the skin named default")
     *
     * @var string
     */
    private $page_theme_skin_dark;
}
